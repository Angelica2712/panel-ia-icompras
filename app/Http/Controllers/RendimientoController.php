<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Support\Facades\DB;

class RendimientoController extends Controller
{
    public function index()
    {
        // Distribución de latencia por rangos
        $rangos = [
            'Rápida (<1s)'    => AiMensajeLog::where('latencia_ms', '<', 1000)->count(),
            'Normal (1-3s)'   => AiMensajeLog::whereBetween('latencia_ms', [1000, 3000])->count(),
            'Lenta (3-6s)'    => AiMensajeLog::whereBetween('latencia_ms', [3001, 6000])->count(),
            'Muy lenta (>6s)' => AiMensajeLog::where('latencia_ms', '>', 6000)->count(),
        ];

        // Latencia por día (últimos 30 días)
        $latenciaDia = AiMensajeLog::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('AVG(latencia_ms) as promedio'),
                DB::raw('MIN(latencia_ms) as minimo'),
                DB::raw('MAX(latencia_ms) as maximo'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Latencia por hora
        $latenciaHora = AiMensajeLog::select(
                DB::raw('HOUR(created_at) as hora'),
                DB::raw('AVG(latencia_ms) as promedio')
            )
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->keyBy('hora');

        $horasLatencia = [];
        for ($h = 0; $h < 24; $h++) {
            $horasLatencia[] = round($latenciaHora->get($h)->promedio ?? 0);
        }

        // KPIs
        $latPromedio = AiMensajeLog::avg('latencia_ms');
        $latMediana  = AiMensajeLog::orderBy('latencia_ms')
                        ->skip((int)(AiMensajeLog::count() / 2))
                        ->value('latencia_ms') ?? 0;
        $latMax      = AiMensajeLog::max('latencia_ms');
        $latMin      = AiMensajeLog::min('latencia_ms');

        // Peores respuestas (más lentas)
        $masLentas = AiMensajeLog::orderByDesc('latencia_ms')->limit(10)->get();

        return view('dashboard.rendimiento', compact(
            'rangos', 'latenciaDia', 'horasLatencia',
            'latPromedio', 'latMediana', 'latMax', 'latMin',
            'masLentas'
        ));
    }
}
