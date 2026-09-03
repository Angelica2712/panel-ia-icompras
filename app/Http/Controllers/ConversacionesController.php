<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class ConversacionesController extends Controller
{
    public function index(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $filtroActivo = $fechaDesde && $fechaHasta;

        $aplicarFiltro = function ($query) use ($filtroActivo, $fechaDesde, $fechaHasta) {
            if ($filtroActivo) {
                $query->whereDate('created_at', '>=', $fechaDesde)
                      ->whereDate('created_at', '<=', $fechaHasta);
            }
            return $query;
        };

        // Paginación de todos los mensajes
        $conversaciones = $aplicarFiltro(AiMensajeLog::query())
            ->orderByDesc('created_at')
            ->paginate(25)
            ->appends($request->only(['fecha_desde', 'fecha_hasta']));

        // Stats rápidas
        $total          = $aplicarFiltro(AiMensajeLog::query())->count();
        $hoy            = $aplicarFiltro(AiMensajeLog::whereDate('created_at', today()))->count();
        $promedioLatencia = $aplicarFiltro(AiMensajeLog::query())->avg('latencia_ms');

        // Longitud promedio de preguntas/respuestas
        $avgPregunta    = $aplicarFiltro(AiMensajeLog::selectRaw('AVG(CHAR_LENGTH(pregunta)) as avg'))->value('avg');
        $avgRespuesta   = $aplicarFiltro(AiMensajeLog::selectRaw('AVG(CHAR_LENGTH(respuesta)) as avg'))->value('avg');

        return view('dashboard.conversaciones', compact(
            'conversaciones', 'total', 'hoy', 'promedioLatencia',
            'avgPregunta', 'avgRespuesta'
        ));
    }

    /**
     * GET /conversaciones/descargar
     * Descarga todas las Q&A como archivo CSV compatible con Excel.
     * Acepta los mismos filtros de fecha que el listado.
     */
    public function descargar(Request $request): StreamedResponse
    {
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $query = AiMensajeLog::query()->orderByDesc('created_at');

        if ($fechaDesde && $fechaHasta) {
            $query->whereDate('created_at', '>=', $fechaDesde)
                  ->whereDate('created_at', '<=', $fechaHasta);
        }

        $nombreArchivo = 'preguntas_respuestas'
            . ($fechaDesde ? '_' . $fechaDesde : '')
            . ($fechaHasta ? '_al_' . $fechaHasta : '')
            . '_' . now()->format('Ymd_His')
            . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8: necesario para que Excel abra el CSV con tildes y ñ sin problemas.
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabecera de columnas
            fputcsv($handle, [
                'Farmacia',
                'Pregunta',
                'Respuesta',
            ], ';');

            // Recorremos en chunks para no cargar todo en memoria de golpe.
            $query->chunk(500, function ($registros) use ($handle) {
                foreach ($registros as $r) {
                    fputcsv($handle, [
                        $r->nombre_farmacia ?? '',
                        $r->pregunta        ?? '',
                        $r->respuesta       ?? '',
                    ], ';');
                }
            });

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }
}
