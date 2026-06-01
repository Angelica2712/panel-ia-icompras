<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMensajeLog extends Model
{
    protected $table      = 'ai_mensajes_log';
    protected $primaryKey = 'id';
    public    $timestamps = false; // la tabla usa created_at pero no updated_at

    protected $fillable = [
        'id_usuario',
        'id_farmacia',
        'nombre_farmacia',
        'pregunta',
        'respuesta',
        'latencia_ms',
        'created_at',
        'version_icompras',
        'pagina_origen',
        'session_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'latencia_ms' => 'integer',
    ];
}
