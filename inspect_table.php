<?php
$cols = DB::select('DESCRIBE ai_mensajes_log');
foreach ($cols as $c) {
    echo $c->Field . ' | ' . $c->Type . PHP_EOL;
}
echo PHP_EOL . '--- MUESTRA (5 registros) ---' . PHP_EOL;
$rows = DB::select('SELECT * FROM ai_mensajes_log LIMIT 5');
foreach ($rows as $r) {
    print_r((array)$r);
}
