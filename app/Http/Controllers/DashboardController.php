<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AiMensajeLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Tarjetas KPI ───────────────────────────────────────────────
        $totalMensajes    = AiMensajeLog::count();
        $mensajesHoy      = AiMensajeLog::whereDate('created_at', today())->count();
        $mensajesSemana   = AiMensajeLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $mensajesMes      = AiMensajeLog::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)->count();

        $latenciaPromedio = AiMensajeLog::avg('latencia_ms');
        $latenciaMax      = AiMensajeLog::max('latencia_ms');
        $latenciaMin      = AiMensajeLog::min('latencia_ms');

        $usuariosUnicos   = AiMensajeLog::distinct('id_usuario')->count('id_usuario');
        $sesionesUnicas   = AiMensajeLog::distinct('session_id')->count('session_id');
        $farmaciasActivas = AiMensajeLog::whereNotNull('nombre_farmacia')
                                         ->distinct('id_farmacia')->count('id_farmacia');

        // ── Mensajes por día (últimos 30 días) ─────────────────────────
        $mensajesPorDia = AiMensajeLog::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // ── Mensajes por hora del día ──────────────────────────────────
        $mensajesPorHora = AiMensajeLog::select(
                DB::raw('HOUR(created_at) as hora'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->keyBy('hora');

        $horasData = [];
        for ($h = 0; $h < 24; $h++) {
            $horasData[] = $mensajesPorHora->get($h)->total ?? 0;
        }

        // ── Top farmacias ──────────────────────────────────────────────
        $topFarmacias = AiMensajeLog::select('nombre_farmacia', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nombre_farmacia')
            ->groupBy('nombre_farmacia')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ── Por versión ────────────────────────────────────────────────
        $porVersion = AiMensajeLog::select('version_icompras', DB::raw('COUNT(*) as total'))
            ->whereNotNull('version_icompras')
            ->groupBy('version_icompras')
            ->orderByDesc('total')
            ->get();

        // ── Top páginas de origen ──────────────────────────────────────
        $topPaginas = AiMensajeLog::select('pagina_origen', DB::raw('COUNT(*) as total'))
            ->whereNotNull('pagina_origen')
            ->groupBy('pagina_origen')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── Mensajes recientes ─────────────────────────────────────────
        $recientes = AiMensajeLog::orderByDesc('created_at')->limit(10)->get();

        // ── Latencia por día (últimos 14 días) ─────────────────────────
        $latenciaPorDia = AiMensajeLog::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('AVG(latencia_ms) as promedio'),
                DB::raw('MAX(latencia_ms) as maximo')
            )
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return view('dashboard.index', compact(
            'totalMensajes', 'mensajesHoy', 'mensajesSemana', 'mensajesMes',
            'latenciaPromedio', 'latenciaMax', 'latenciaMin',
            'usuariosUnicos', 'sesionesUnicas', 'farmaciasActivas',
            'mensajesPorDia', 'horasData',
            'topFarmacias', 'porVersion', 'topPaginas',
            'recientes', 'latenciaPorDia'
        ));
    }
}
