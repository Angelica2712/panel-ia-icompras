<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
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

        // Top usuarios por volumen
        $topUsuarios = $aplicarFiltro(AiMensajeLog::select(
                'id_usuario',
                DB::raw('COUNT(*) as total_mensajes'),
                DB::raw('COUNT(DISTINCT session_id) as sesiones'),
                DB::raw('AVG(latencia_ms) as latencia_promedio'),
                DB::raw('MAX(created_at) as ultima_actividad'),
                DB::raw('MIN(created_at) as primera_actividad'),
                DB::raw('COUNT(DISTINCT DATE(created_at)) as dias_activo')
            ))
            ->groupBy('id_usuario')
            ->orderByDesc('total_mensajes')
            ->paginate(20)
            ->appends($request->only(['fecha_desde', 'fecha_hasta']));

        // KPIs
        $totalUsuarios    = $aplicarFiltro(AiMensajeLog::query())->distinct('id_usuario')->count('id_usuario');
        $usuariosHoy      = $aplicarFiltro(AiMensajeLog::whereDate('created_at', today()))
                            ->distinct('id_usuario')->count('id_usuario');
        $usuariosSemana   = $aplicarFiltro(AiMensajeLog::where('created_at', '>=', now()->startOfWeek()))
                            ->distinct('id_usuario')->count('id_usuario');
        $totalMsgs        = $aplicarFiltro(AiMensajeLog::query())->count();
        $avgMsgPorUsuario = $totalUsuarios > 0
                            ? round($totalMsgs / $totalUsuarios, 1)
                            : 0;

        // Usuarios activos por día
        $queryDia = AiMensajeLog::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(DISTINCT id_usuario) as nuevos')
            );
        if ($filtroActivo) {
            $queryDia->whereDate('created_at', '>=', $fechaDesde)
                     ->whereDate('created_at', '<=', $fechaHasta);
        } else {
            $queryDia->where('created_at', '>=', now()->subDays(30));
        }
        $nuevosPorDia = $queryDia->groupBy('fecha')->orderBy('fecha')->get();

        // Usuarios más activos (en el rango o esta semana)
        $queryActivos = AiMensajeLog::select(
                'id_usuario',
                DB::raw('COUNT(*) as total')
            );
        if ($filtroActivo) {
            $queryActivos->whereDate('created_at', '>=', $fechaDesde)
                         ->whereDate('created_at', '<=', $fechaHasta);
        } else {
            $queryActivos->where('created_at', '>=', now()->startOfWeek());
        }
        $activosEstaSemana = $queryActivos
            ->groupBy('id_usuario')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('dashboard.usuarios', compact(
            'topUsuarios', 'totalUsuarios', 'usuariosHoy',
            'usuariosSemana', 'avgMsgPorUsuario',
            'nuevosPorDia', 'activosEstaSemana'
        ));
    }
}
