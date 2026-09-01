<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FarmaciasController extends Controller
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

        $farmacias = $aplicarFiltro(AiMensajeLog::select(
                'id_farmacia',
                'nombre_farmacia',
                DB::raw('COUNT(*) as total_mensajes'),
                DB::raw('AVG(latencia_ms) as latencia_promedio'),
                DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos'),
                DB::raw('COUNT(DISTINCT session_id) as sesiones'),
                DB::raw('MAX(created_at) as ultimo_mensaje'),
                DB::raw('MIN(created_at) as primer_mensaje')
            )
            ->whereNotNull('nombre_farmacia'))
            ->groupBy('id_farmacia', 'nombre_farmacia')
            ->orderByDesc('total_mensajes')
            ->paginate(20)
            ->appends($request->only(['fecha_desde', 'fecha_hasta']));

        $totalFarmacias = $aplicarFiltro(AiMensajeLog::whereNotNull('nombre_farmacia'))
                            ->distinct('id_farmacia')->count('id_farmacia');

        // Mensajes por farmacia (actividad reciente o dentro del rango)
        $queryActividad = AiMensajeLog::select(
                'nombre_farmacia',
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('nombre_farmacia');

        if ($filtroActivo) {
            $queryActividad->whereDate('created_at', '>=', $fechaDesde)
                           ->whereDate('created_at', '<=', $fechaHasta);
        } else {
            $queryActividad->where('created_at', '>=', now()->subDays(7));
        }

        $actividadReciente = $queryActividad
            ->groupBy('nombre_farmacia')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return view('dashboard.farmacias', compact(
            'farmacias', 'totalFarmacias', 'actividadReciente'
        ));
    }
}
