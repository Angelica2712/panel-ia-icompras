<?php

namespace App\Http\Controllers;

use App\Models\AiMensajeLog;
use Illuminate\Support\Facades\DB;

class ConversacionesController extends Controller
{
    public function index()
    {
        // Paginación de todos los mensajes
        $conversaciones = AiMensajeLog::orderByDesc('created_at')->paginate(25);

        // Stats rápidas
        $total          = AiMensajeLog::count();
        $hoy            = AiMensajeLog::whereDate('created_at', today())->count();
        $promedioLatencia = AiMensajeLog::avg('latencia_ms');

        // Longitud promedio de preguntas/respuestas
        $avgPregunta    = AiMensajeLog::selectRaw('AVG(CHAR_LENGTH(pregunta)) as avg')->value('avg');
        $avgRespuesta   = AiMensajeLog::selectRaw('AVG(CHAR_LENGTH(respuesta)) as avg')->value('avg');

        return view('dashboard.conversaciones', compact(
            'conversaciones', 'total', 'hoy', 'promedioLatencia',
            'avgPregunta', 'avgRespuesta'
        ));
    }
}
