<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Http\Request;
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
}
