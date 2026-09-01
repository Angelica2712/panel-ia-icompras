<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AiMensajeLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ── Rango de fechas opcional ───────────────────────────────────
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $filtroActivo = $fechaDesde && $fechaHasta;

        // Closure para aplicar el filtro de fechas a cualquier query
        $aplicarFiltro = function ($query) use ($filtroActivo, $fechaDesde, $fechaHasta) {
            if ($filtroActivo) {
                $query->whereDate('created_at', '>=', $fechaDesde)
                      ->whereDate('created_at', '<=', $fechaHasta);
            }
            return $query;
        };

        // ── Tarjetas KPI ───────────────────────────────────────────────
        $totalMensajes    = $aplicarFiltro(AiMensajeLog::query())->count();
        $mensajesHoy      = $aplicarFiltro(AiMensajeLog::whereDate('created_at', today()))->count();
        $mensajesSemana   = $aplicarFiltro(AiMensajeLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))->count();
        $mensajesMes      = $aplicarFiltro(AiMensajeLog::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year))->count();

        $latenciaPromedio = $aplicarFiltro(AiMensajeLog::query())->avg('latencia_ms');
        $latenciaMax      = $aplicarFiltro(AiMensajeLog::query())->max('latencia_ms');
        $latenciaMin      = $aplicarFiltro(AiMensajeLog::query())->min('latencia_ms');

        $usuariosUnicos   = $aplicarFiltro(AiMensajeLog::query())->distinct('id_usuario')->count('id_usuario');
        $sesionesUnicas   = $aplicarFiltro(AiMensajeLog::query())->distinct('session_id')->count('session_id');
        $farmaciasActivas = $aplicarFiltro(AiMensajeLog::whereNotNull('nombre_farmacia'))
                                         ->distinct('id_farmacia')->count('id_farmacia');

        // ── Mensajes por día ───────────────────────────────────────────
        $queryDia = AiMensajeLog::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total')
            );
        if ($filtroActivo) {
            $queryDia->whereDate('created_at', '>=', $fechaDesde)
                     ->whereDate('created_at', '<=', $fechaHasta);
        } else {
            $queryDia->where('created_at', '>=', now()->subDays(30));
        }
        $mensajesPorDia = $queryDia->groupBy('fecha')->orderBy('fecha')->get();

        // ── Mensajes por hora del día ──────────────────────────────────
        $mensajesPorHora = $aplicarFiltro(AiMensajeLog::select(
                DB::raw('HOUR(created_at) as hora'),
                DB::raw('COUNT(*) as total')
            ))
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->keyBy('hora');

        $horasData = [];
        for ($h = 0; $h < 24; $h++) {
            $horasData[] = $mensajesPorHora->get($h)->total ?? 0;
        }

        // ── Top farmacias ──────────────────────────────────────────────
        $topFarmacias = $aplicarFiltro(AiMensajeLog::select('nombre_farmacia', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nombre_farmacia'))
            ->groupBy('nombre_farmacia')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ── Por versión ────────────────────────────────────────────────
        $porVersion = $aplicarFiltro(AiMensajeLog::select('version_icompras', DB::raw('COUNT(*) as total'))
            ->whereNotNull('version_icompras'))
            ->groupBy('version_icompras')
            ->orderByDesc('total')
            ->get();

        // ── Top páginas de origen ──────────────────────────────────────
        $topPaginas = $aplicarFiltro(AiMensajeLog::select('pagina_origen', DB::raw('COUNT(*) as total'))
            ->whereNotNull('pagina_origen'))
            ->groupBy('pagina_origen')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── Mensajes recientes ─────────────────────────────────────────
        $recientes = $aplicarFiltro(AiMensajeLog::query())->orderByDesc('created_at')->limit(10)->get();

        // ── Latencia por día ───────────────────────────────────────────
        $queryLat = AiMensajeLog::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('AVG(latencia_ms) as promedio'),
                DB::raw('MAX(latencia_ms) as maximo')
            );
        if ($filtroActivo) {
            $queryLat->whereDate('created_at', '>=', $fechaDesde)
                     ->whereDate('created_at', '<=', $fechaHasta);
        } else {
            $queryLat->where('created_at', '>=', now()->subDays(14));
        }
        $latenciaPorDia = $queryLat->groupBy('fecha')->orderBy('fecha')->get();

        return view('dashboard.index', compact(
            'totalMensajes', 'mensajesHoy', 'mensajesSemana', 'mensajesMes',
            'latenciaPromedio', 'latenciaMax', 'latenciaMin',
            'usuariosUnicos', 'sesionesUnicas', 'farmaciasActivas',
            'mensajesPorDia', 'horasData',
            'topFarmacias', 'porVersion', 'topPaginas',
            'recientes', 'latenciaPorDia',
            'fechaDesde', 'fechaHasta', 'filtroActivo'
        ));
    }
}
