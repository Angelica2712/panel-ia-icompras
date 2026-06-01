<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Support\Facades\DB;

class FarmaciasController extends Controller
{
    public function index()
    {
        $farmacias = AiMensajeLog::select(
                'id_farmacia',
                'nombre_farmacia',
                DB::raw('COUNT(*) as total_mensajes'),
                DB::raw('AVG(latencia_ms) as latencia_promedio'),
                DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos'),
                DB::raw('COUNT(DISTINCT session_id) as sesiones'),
                DB::raw('MAX(created_at) as ultimo_mensaje'),
                DB::raw('MIN(created_at) as primer_mensaje')
            )
            ->whereNotNull('nombre_farmacia')
            ->groupBy('id_farmacia', 'nombre_farmacia')
            ->orderByDesc('total_mensajes')
            ->paginate(20);

        $totalFarmacias = AiMensajeLog::whereNotNull('nombre_farmacia')
                            ->distinct('id_farmacia')->count('id_farmacia');

        // Mensajes por farmacia en los últimos 7 días
        $actividadReciente = AiMensajeLog::select(
                'nombre_farmacia',
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('nombre_farmacia')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('nombre_farmacia')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return view('dashboard.farmacias', compact(
            'farmacias', 'totalFarmacias', 'actividadReciente'
        ));
    }
}
