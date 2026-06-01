<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
    public function index()
    {
        // Top usuarios por volumen
        $topUsuarios = AiMensajeLog::select(
                'id_usuario',
                DB::raw('COUNT(*) as total_mensajes'),
                DB::raw('COUNT(DISTINCT session_id) as sesiones'),
                DB::raw('AVG(latencia_ms) as latencia_promedio'),
                DB::raw('MAX(created_at) as ultima_actividad'),
                DB::raw('MIN(created_at) as primera_actividad'),
                DB::raw('COUNT(DISTINCT DATE(created_at)) as dias_activo')
            )
            ->groupBy('id_usuario')
            ->orderByDesc('total_mensajes')
            ->paginate(20);

        // KPIs
        $totalUsuarios    = AiMensajeLog::distinct('id_usuario')->count('id_usuario');
        $usuariosHoy      = AiMensajeLog::whereDate('created_at', today())
                            ->distinct('id_usuario')->count('id_usuario');
        $usuariosSemana   = AiMensajeLog::where('created_at', '>=', now()->startOfWeek())
                            ->distinct('id_usuario')->count('id_usuario');
        $avgMsgPorUsuario = $totalUsuarios > 0
                            ? round(AiMensajeLog::count() / $totalUsuarios, 1)
                            : 0;

        // Nuevos usuarios por día (últimos 30 días)
        $nuevosPorDia = AiMensajeLog::select(
                DB::raw('DATE(MIN(created_at)) as fecha'),
                DB::raw('COUNT(DISTINCT id_usuario) as nuevos')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        // Usuarios más activos esta semana
        $activosEstaSemana = AiMensajeLog::select(
                'id_usuario',
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->startOfWeek())
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
