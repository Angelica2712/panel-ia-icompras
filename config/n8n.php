<?php

/*
|--------------------------------------------------------------------------
| Configuración de integración con n8n
|--------------------------------------------------------------------------
|
| Aquí centralizamos todo lo relacionado con los flujos de n8n.
| IMPORTANTE: nunca escribas la URL del webhook directamente en el código.
| Siempre se lee desde el archivo .env con la función env().
|
| Si cambias algo en .env, recuerda ejecutar:  php artisan config:clear
|
*/

return [

    // Flujo de "carga de conocimiento" (manuales -> chunks -> embeddings -> Qdrant)
    'manuales' => [

        // URL completa del webhook de n8n (viene de .env -> N8N_MANUALES_WEBHOOK_URL)
        'webhook_url' => env('N8N_MANUALES_WEBHOOK_URL'),

        // Token opcional. Si tu webhook de n8n tiene "Header Auth" activado,
        // pon aquí el valor y se enviará en la cabecera Authorization.
        // Si lo dejas vacío, simplemente no se envía ninguna cabecera extra.
        'token' => env('N8N_MANUALES_TOKEN'),

        // Segundos máximos de espera. Vectorizar un manual puede tardar,
        // por eso el valor por defecto es alto (120s = 2 minutos).
        'timeout' => (int) env('N8N_MANUALES_TIMEOUT', 120),
    ],

];
