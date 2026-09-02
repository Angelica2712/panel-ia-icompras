<?php

/*
|--------------------------------------------------------------------------
| Conexión a Qdrant (base de datos vectorial)
|--------------------------------------------------------------------------
|
| El panel habla con Qdrant SOLO para consultar y borrar manuales.
| La escritura (fragmentar y vectorizar) la sigue haciendo n8n.
|
| Igual que con n8n, la dirección nunca va escrita en el código: se lee
| desde .env. Si cambias algo allí, ejecuta:  php artisan config:clear
|
*/

return [

    // Dirección de Qdrant vista DESDE Laravel.
    //
    // Ojo con este detalle: n8n usa "http://qdrant-local:6333" porque habla
    // por dentro de la red de Docker. Laravel corre fuera de Docker (en XAMPP),
    // así que tiene que usar el puerto publicado hacia Windows: 6335.
    // Son la misma base de datos, vista desde dos sitios distintos.
    'url' => env('QDRANT_URL', 'http://localhost:6335'),

    // API key. El Qdrant local no la pide; el de producción sí.
    // Si está vacía, no se envía ninguna cabecera de autenticación.
    'api_key' => env('QDRANT_API_KEY'),

    // Nombre de la colección donde n8n guarda los fragmentos.
    // Debe coincidir con la que usan los nodos "Qdrant Vector Store".
    'collection' => env('QDRANT_COLLECTION', 'icompras_modulos'),

    // Segundos máximos de espera.
    'timeout' => (int) env('QDRANT_TIMEOUT', 15),
];
