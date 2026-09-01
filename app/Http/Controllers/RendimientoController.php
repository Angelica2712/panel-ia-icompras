<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RendimientoController extends Controller
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

        // Distribución de latencia por rangos
        $rangos = [
            'Rápida (<1s)'    => $aplicarFiltro(AiMensajeLog::where('latencia_ms', '<', 1000))->count(),
            'Normal (1-3s)'   => $aplicarFiltro(AiMensajeLog::whereBetween('latencia_ms', [1000, 3000]))->count(),
            'Lenta (3-6s)'    => $aplicarFiltro(AiMensajeLog::whereBetween('latencia_ms', [3001, 6000]))->count(),
            'Muy lenta (>6s)' => $aplicarFiltro(AiMensajeLog::where('latencia_ms', '>', 6000))->count(),
        ];

        // Latencia por día
        $queryDia = AiMensajeLog::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('AVG(latencia_ms) as promedio'),
                DB::raw('MIN(latencia_ms) as minimo'),
                DB::raw('MAX(latencia_ms) as maximo'),
                DB::raw('COUNT(*) as total')
            );
        if ($filtroActivo) {
            $queryDia->whereDate('created_at', '>=', $fechaDesde)
                     ->whereDate('created_at', '<=', $fechaHasta);
        } else {
            $queryDia->where('created_at', '>=', now()->subDays(30));
        }
        $latenciaDia = $queryDia->groupBy('fecha')->orderBy('fecha')->get();

        // Latencia por hora
        $latenciaHora = $aplicarFiltro(AiMensajeLog::select(
                DB::raw('HOUR(created_at) as hora'),
                DB::raw('AVG(latencia_ms) as promedio')
            ))
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->keyBy('hora');

        $horasLatencia = [];
        for ($h = 0; $h < 24; $h++) {
            $horasLatencia[] = round($latenciaHora->get($h)->promedio ?? 0);
        }

        // KPIs
        $latPromedio = $aplicarFiltro(AiMensajeLog::query())->avg('latencia_ms');
        $totalCount  = $aplicarFiltro(AiMensajeLog::query())->count();
        $latMediana  = $aplicarFiltro(AiMensajeLog::query())->orderBy('latencia_ms')
                        ->skip((int)($totalCount / 2))
                        ->value('latencia_ms') ?? 0;
        $latMax      = $aplicarFiltro(AiMensajeLog::query())->max('latencia_ms');
        $latMin      = $aplicarFiltro(AiMensajeLog::query())->min('latencia_ms');

        // Peores respuestas (más lentas)
        $masLentas = $aplicarFiltro(AiMensajeLog::query())->orderByDesc('latencia_ms')->limit(10)->get();

        return view('dashboard.rendimiento', compact(
            'rangos', 'latenciaDia', 'horasLatencia',
            'latPromedio', 'latMediana', 'latMax', 'latMin',
            'masLentas'
        ));
    }
}
