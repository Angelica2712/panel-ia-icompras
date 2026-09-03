<?php

namespace App\Http\Controllers;

use App\Services\QdrantService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * ManualesController
 * -----------------------------------------------------------------------------
 * Módulo de "Carga de manuales" del panel administrativo.
 *
 * Qué hace, en orden:
 *   1. Muestra un formulario para subir uno o varios archivos .md (Markdown).
 *   2. Valida los archivos, la versión (light/full/ambas) y el módulo.
 *   3. Lee el contenido de cada .md como texto plano.
 *   4. Envía ese texto + versión + módulo por POST (JSON) al webhook de n8n.
 *   5. n8n se encarga de fragmentar, vectorizar y guardar en Qdrant.
 *   6. Devuelve un resumen de qué se cargó y qué falló.
 *
 * También muestra lo que ya está cargado en Qdrant y permite borrarlo.
 *
 * Nota: este controlador NO guarda los archivos en el servidor. Solo lee su
 * contenido en memoria y lo reenvía a n8n.
 */
class ManualesController extends Controller
{
    /**
     * Tamaño máximo por archivo, en kilobytes (1536 KB = 1.5 MB).
     *
     * Ojo: PHP tiene su propio límite en php.ini (upload_max_filesize, hoy 2M).
     * Este número debe quedar POR DEBAJO del de php.ini, no igual: si PHP
     * rechaza la subida antes que Laravel, el archivo ni siquiera llega y el
     * mensaje de error que ve la usuaria es confuso ("selecciona un archivo").
     * Dejando margen, el error siempre lo da Laravel y se entiende.
     */
    private const MAX_KB = 1536;

    /**
     * Cuántos archivos se aceptan en una sola carga masiva.
     *
     * El límite no es capricho: cada archivo puede disparar dos peticiones
     * (light y full), y cada una espera a que n8n termine de vectorizar. Con
     * 20 archivos en "ambas" son 40 esperas seguidas. Además, PHP tiene un
     * tope de tamaño para el POST completo (post_max_size), y ahí cuenta la
     * suma de todos los archivos, no cada uno por separado.
     */
    private const MAX_ARCHIVOS = 20;

    /**
     * Versiones válidas del asistente. Se leen desde config('n8n.versiones'),
     * que a su vez lee MANUALES_VERSIONES del .env. Si el .env no la define,
     * usa ['light', 'full', 'ambas'] como valores por defecto.
     *
     * ¿Por qué un método y no una constante? Porque las constantes de PHP
     * no pueden contener llamadas a funciones (como config()), y queremos
     * que la lista sea configurable desde el panel sin tocar el código.
     */
    private function versiones(): array
    {
        return config('n8n.versiones', ['light', 'full', 'ambas']);
    }

    /**
     * GET /manuales
     * Muestra el formulario de carga y, debajo, el listado de lo que ya está
     * cargado en Qdrant.
     *
     * Laravel inyecta solo el QdrantService: no hay que crearlo con "new",
     * basta con pedirlo como parámetro y él lo construye. Se llama inyección
     * de dependencias, y es lo que hace que en los tests podamos sustituirlo
     * por una versión falsa.
     */
    public function create(QdrantService $qdrant): View
    {
        // Si Qdrant está caído, mostramos un aviso en vez de una tabla vacía
        // (que haría pensar que no hay ningún manual cargado).
        $qdrantDisponible = $qdrant->disponible();

        return view('dashboard.manuales', [
            'versiones'        => $this->versiones(),
            'maxKb'            => self::MAX_KB,
            'maxArchivos'      => self::MAX_ARCHIVOS,
            'manuales'         => $qdrantDisponible ? $qdrant->listarManuales() : [],
            'qdrantDisponible' => $qdrantDisponible,
        ]);
    }

    /**
     * POST /manuales
     * Recibe uno o varios .md, los lee y los manda a n8n.
     *
     * Con UN archivo, el módulo lo escribe la usuaria.
     * Con VARIOS, el módulo de cada uno sale de su nombre de archivo, porque
     * no habría forma de escribir veinte módulos en un solo formulario.
     */
    public function store(Request $request): RedirectResponse
    {
        // ---------------------------------------------------------------------
        // PASO 1: Validación
        // ---------------------------------------------------------------------
        // Sobre los archivos: NO usamos la regla "mimes:md" porque el tipo MIME
        // real de un .md suele llegar como text/plain o application/octet-stream
        // según el navegador y el sistema operativo, y la regla rechazaría
        // archivos perfectamente válidos. Comprobamos la extensión directamente.
        //
        // "archivos.*" aplica la regla a CADA elemento del array, no al array.
        $datos = $request->validate([
            'archivos'   => ['required', 'array', 'min:1', 'max:' . self::MAX_ARCHIVOS],
            'archivos.*' => [
                'file',
                'max:' . self::MAX_KB,
                function (string $atributo, $valor, Closure $fallar) {
                    if (strtolower($valor->getClientOriginalExtension()) !== 'md') {
                        $fallar('El archivo :attribute debe tener extensión .md (Markdown).');
                    }
                },
            ],

            // in:... obliga a que el valor sea exactamente uno de los de la lista.
            'version' => ['required', 'string', 'in:' . implode(',', $this->versiones())],

            // Opcional aquí porque en carga masiva se deriva del nombre.
            // Más abajo se exige cuando hay un solo archivo.
            'modulo' => ['nullable', 'string', 'min:2', 'max:60', 'regex:/^[\pL\pN _\-]+$/u'],
        ], [
            'archivos.required' => 'Debes seleccionar al menos un archivo Markdown.',
            'archivos.max'      => 'No puedes subir más de ' . self::MAX_ARCHIVOS . ' archivos a la vez.',
            'archivos.*.file'   => 'Uno de los archivos subidos no es válido.',
            'archivos.*.max'    => 'Cada archivo debe pesar menos de ' . (self::MAX_KB / 1024) . ' MB.',
            'version.required'  => 'Debes seleccionar una versión.',
            'version.in'        => 'La versión seleccionada no es válida.',
            'modulo.min'        => 'El módulo debe tener al menos 2 caracteres.',
            'modulo.max'        => 'El módulo no puede superar los 60 caracteres.',
            'modulo.regex'      => 'El módulo solo puede contener letras, números, espacios, guiones y guiones bajos.',
        ]);

        $archivos = $request->file('archivos');
        $esUnico  = count($archivos) === 1;

        // Con un solo archivo el módulo es obligatorio. Podríamos deducirlo del
        // nombre igual que en la carga masiva, pero es el caso de uso normal y
        // conviene que la usuaria lo escriba: un nombre de archivo descuidado
        // crearía un módulo nuevo en Qdrant sin que nadie se dé cuenta.
        if ($esUnico && blank($datos['modulo'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['modulo' => 'Debes indicar el módulo al que pertenece el manual.']);
        }

        // ---------------------------------------------------------------------
        // PASO 2: Comprobar que el webhook esté configurado
        // ---------------------------------------------------------------------
        $webhookUrl = config('n8n.manuales.webhook_url');

        if (empty($webhookUrl)) {
            return back()
                ->withInput()
                ->with('error', 'El webhook de n8n no está configurado. Agrega N8N_MANUALES_WEBHOOK_URL en el archivo .env y ejecuta: php artisan config:clear');
        }

        // Vectorizar tarda, y aquí pueden encadenarse decenas de esperas.
        // Sin quitar el límite, PHP corta a mitad del lote y quedan manuales
        // cargados a medias sin que nadie se entere.
        @set_time_limit(0);

        // El flujo de n8n guarda una sola versión por carga: "light" o "full".
        // No entiende "ambas". Así funciona también la carga masiva del
        // orquestador, que tiene una fila por versión. Lo replicamos.
        $versiones = $datos['version'] === 'ambas'
            ? ['light', 'full']
            : [$datos['version']];

        // ---------------------------------------------------------------------
        // PASO 3: Procesar los archivos uno por uno
        // ---------------------------------------------------------------------
        // Si uno falla NO abortamos el resto: se anota el fallo y se sigue.
        // En un lote de quince manuales, que uno falle no es razón para dejar
        // los otros catorce sin cargar.
        $resultados = [];

        foreach ($archivos as $archivo) {
            $nombre = $archivo->getClientOriginalName();
            $modulo = $esUnico
                ? $datos['modulo']
                : $this->moduloDesdeNombre($nombre);

            $texto = $this->leerTexto($archivo);

            if ($texto === '') {
                $resultados[] = [
                    'archivo'   => $nombre,
                    'modulo'    => $modulo,
                    'ok'        => false,
                    'versiones' => [],
                    'kb'        => 0,
                    'detalle'   => 'El archivo está vacío.',
                ];
                continue;
            }

            $cargadas = [];
            $fallo    = null;

            foreach ($versiones as $version) {
                [$ok, $detalle] = $this->enviarAN8n($webhookUrl, $texto, $modulo, $version, $nombre, $request);

                if (! $ok) {
                    $fallo = $detalle;
                    break; // no seguimos con la otra versión de este archivo
                }

                $cargadas[] = $version;
            }

            $resultados[] = [
                'archivo'   => $nombre,
                'modulo'    => $modulo,
                'ok'        => $fallo === null,
                'versiones' => $cargadas,
                'kb'        => round(strlen($texto) / 1024, 1),
                'detalle'   => $fallo,
            ];
        }

        return redirect()
            ->route('manuales.create')
            ->with('resultados', $resultados);
    }

    /**
     * DELETE /manuales
     * Borra de Qdrant todos los fragmentos de un manual (módulo + versión).
     *
     * Ojo: esto es IRREVERSIBLE. Qdrant no tiene papelera. Para recuperar
     * un manual borrado hay que volver a subir el archivo .md.
     */
    public function destroy(Request $request, QdrantService $qdrant): RedirectResponse
    {
        // Estos dos campos vienen de un <input hidden> del formulario, no los
        // escribe la usuaria. Aun así se validan: nunca hay que fiarse de lo
        // que llega en una petición, aunque el formulario sea nuestro.
        $datos = $request->validate([
            'modulo'  => ['required', 'string', 'max:60'],
            'version' => ['required', 'string', 'max:30'],
        ], [
            'modulo.required'  => 'Falta indicar qué módulo borrar.',
            'version.required' => 'Falta indicar qué versión borrar.',
        ]);

        $borrado = $qdrant->borrarManual($datos['modulo'], $datos['version']);

        if (! $borrado) {
            return back()->with('error',
                'No se pudo borrar el manual "' . $datos['modulo'] . '" (' . $datos['version'] . '). '
                . 'Revisa que Qdrant esté accesible.');
        }

        // Quién borró qué queda registrado: si un día falta un manual,
        // el log dice de dónde salió la orden.
        Log::info('Manual borrado desde el panel', [
            'modulo'  => $datos['modulo'],
            'version' => $datos['version'],
            'usuario' => optional($request->user())->email,
        ]);

        return redirect()
            ->route('manuales.create')
            ->with('ok', 'Manual "' . $datos['modulo'] . '" (' . $datos['version'] . ') eliminado de la base de conocimiento.');
    }

    /**
     * GET /manuales/fragmentos?modulo=X&version=Y&offset=Z
     * Endpoint AJAX: devuelve fragmentos de un módulo+versión paginados.
     * El campo de texto se auto-detecta en QdrantService.
     */
    public function fragmentos(Request $request, QdrantService $qdrant): JsonResponse
    {
        $datos = $request->validate([
            'modulo'  => ['required', 'string', 'max:60'],
            'version' => ['required', 'string', 'max:30'],
            'offset'  => ['nullable', 'string', 'max:100'],
            'limite'  => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if (! $qdrant->disponible()) {
            return response()->json(['error' => 'Qdrant no está disponible.'], 503);
        }

        $resultado = $qdrant->obtenerFragmentos(
            $datos['modulo'],
            $datos['version'],
            $datos['offset'] ?? null,
            (int) ($datos['limite'] ?? 10)
        );

        return response()->json($resultado);
    }

    /**
     * POST /manuales/versiones
     * Guarda la lista de versiones en el .env para que persista entre reinicios.
     *
     * Escribe directamente el archivo .env usando str_replace para no perder
     * las otras variables. Es la forma estándar en proyectos Laravel pequeños
     * sin un sistema de gestión de configuración más sofisticado.
     */
    public function guardarVersiones(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'versiones' => ['required', 'string', 'max:200',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*(,[a-zA-Z][a-zA-Z0-9_]*)*$/'],
        ], [
            'versiones.required' => 'Debes indicar al menos una versión.',
            'versiones.regex'    => 'Las versiones solo pueden contener letras, números y guiones bajos, separadas por coma.',
        ]);

        // Limpiar y normalizar: quitar espacios, duplicados, vacíos.
        $lista = array_values(array_unique(array_filter(
            array_map('trim', explode(',', $datos['versiones']))
        )));

        if (empty($lista)) {
            return back()->withErrors(['versiones' => 'La lista de versiones no puede estar vacía.']);
        }

        $valorNuevo  = implode(',', $lista);
        $envPath     = base_path('.env');
        $contenido   = file_get_contents($envPath);

        // Si la variable ya existe, la reemplazamos. Si no, la agregamos al final.
        if (preg_match('/^MANUALES_VERSIONES=.*/m', $contenido)) {
            $contenido = preg_replace(
                '/^MANUALES_VERSIONES=.*/m',
                'MANUALES_VERSIONES=' . $valorNuevo,
                $contenido
            );
        } else {
            $contenido .= PHP_EOL . 'MANUALES_VERSIONES=' . $valorNuevo . PHP_EOL;
        }

        file_put_contents($envPath, $contenido);

        // Limpiar la caché de configuración para que los cambios tengan efecto
        // en la misma petición siguiente sin necesidad de reiniciar el servidor.
        \Artisan::call('config:clear');

        Log::info('Versiones de manuales actualizadas desde el panel', [
            'versiones' => $lista,
            'usuario'   => optional($request->user())->email,
        ]);

        return redirect()
            ->route('manuales.create')
            ->with('ok', 'Versiones actualizadas: ' . implode(', ', $lista) . '.');
    }

    /**
     * POST /manuales/modulo-version
     * Cambia la versión de todos los fragmentos de un módulo en Qdrant.
     * Útil para corregir versiones sin tener que volver a subir el manual.
     */
    public function cambiarVersionModulo(Request $request, QdrantService $qdrant): RedirectResponse
    {
        $datos = $request->validate([
            'modulo'          => ['required', 'string', 'min:2', 'max:60'],
            'version_nueva'   => ['required', 'string', 'in:' . implode(',', $this->versiones())],
            'version_actual'  => ['nullable', 'string', 'max:30'],
        ], [
            'modulo.required'        => 'Debes indicar el módulo.',
            'version_nueva.required' => 'Debes seleccionar la versión nueva.',
            'version_nueva.in'       => 'La versión seleccionada no es válida.',
        ]);

        if (! $qdrant->disponible()) {
            return back()->with('error', 'No se pudo conectar con Qdrant. Verifica que el servicio esté corriendo.');
        }

        $ok = $qdrant->cambiarVersionModulo(
            $datos['modulo'],
            $datos['version_nueva'],
            ($datos['version_actual'] ?? null) ?: null
        );

        if (! $ok) {
            return back()->with('error',
                'No se pudo actualizar la versión del módulo "' . $datos['modulo'] . '". '
                . 'Revisa que Qdrant esté accesible y que el módulo exista.'
            );
        }

        $detalle = ($datos['version_actual'] ?? null)
            ? ' (solo los que tenían versión "' . $datos['version_actual'] . '")'
            : ' (todos los fragmentos del módulo)';

        Log::info('Versión de módulo cambiada desde el panel', [
            'modulo'         => $datos['modulo'],
            'version_nueva'  => $datos['version_nueva'],
            'version_actual' => $datos['version_actual'] ?? null,
            'usuario'        => optional($request->user())->email,
        ]);

        return redirect()
            ->route('manuales.create')
            ->with('ok', 'Módulo "' . $datos['modulo'] . '" actualizado a versión "' . $datos['version_nueva'] . '"' . $detalle . '.');
    }

    /**
     * Manda un manual a n8n en una versión concreta.
     *
     * @return array{0: bool, 1: string|null}  [salió bien, detalle del fallo]
     */
    private function enviarAN8n(
        string $webhookUrl,
        string $texto,
        string $modulo,
        string $version,
        string $nombreArchivo,
        Request $request
    ): array {
        // IMPORTANTE: los nombres de estas claves son los que se leen dentro
        // de n8n. Como llegan por un nodo Webhook, allá se acceden con el
        // prefijo "body": {{ $json.body.texto }}, {{ $json.body.modulo }}...
        $payload = [
            // --- Los tres datos que el flujo necesita sí o sí ---
            'texto'   => $texto,    // contenido completo del manual
            'version' => $version,  // siempre "light" o "full", nunca "ambas"
            'modulo'  => $modulo,   // catalogo, carrito, configuracion...

            // El nodo "Convert to File" usa este nombre para el archivo que
            // arma en memoria. Debe terminar en .md para que n8n lo trate
            // como Markdown y no como PDF.
            'nombre_archivo' => $nombreArchivo,

            // --- Metadatos extra: solo para trazabilidad ---
            'enviado_por' => optional($request->user())->email,
            'enviado_en'  => now()->toIso8601String(),
        ];

        // Envolvemos en try/catch porque la red puede fallar (n8n apagado,
        // sin internet, timeout...) y eso lanza una excepción, no respuesta.
        try {
            $peticion = Http::timeout(config('n8n.manuales.timeout'))
                ->acceptJson(); // pedimos que n8n responda en formato JSON

            // Si configuraste un token en .env, lo mandamos en la cabecera.
            $token = config('n8n.manuales.token');
            if (! empty($token)) {
                $peticion = $peticion->withHeaders(['Authorization' => $token]);
            }

            // ->post($url, $array) envía el array como JSON automáticamente.
            $respuesta = $peticion->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            Log::error('Fallo al conectar con el webhook de n8n (manuales)', [
                'error'   => $e->getMessage(),
                'archivo' => $nombreArchivo,
                'modulo'  => $modulo,
                'version' => $version,
            ]);

            return [false, 'No se pudo conectar con n8n al cargar la versión "' . $version . '".'];
        }

        // successful() es true cuando el código HTTP está entre 200 y 299.
        if (! $respuesta->successful()) {
            Log::warning('n8n respondió con error al cargar un manual', [
                'status'  => $respuesta->status(),
                'body'    => $respuesta->body(),
                'archivo' => $nombreArchivo,
                'modulo'  => $modulo,
                'version' => $version,
            ]);

            return [false, 'n8n rechazó la versión "' . $version . '" (código HTTP ' . $respuesta->status() . ').'];
        }

        Log::info('Manual enviado a n8n correctamente', [
            'archivo' => $nombreArchivo,
            'modulo'  => $modulo,
            'version' => $version,
            'usuario' => optional($request->user())->email,
        ]);

        return [true, null];
    }

    /**
     * Lee el contenido de un .md como texto limpio.
     * Devuelve cadena vacía si el archivo no tiene contenido útil.
     */
    private function leerTexto(UploadedFile $archivo): string
    {
        $texto = $archivo->get(); // get() devuelve el contenido completo

        // Algunos editores (sobre todo en Windows) guardan un "BOM" invisible
        // al inicio del archivo. Si no lo quitamos, se cuela como basura
        // dentro del primer fragmento que se vectoriza.
        $texto = preg_replace('/^\x{FEFF}/u', '', $texto);
        $texto = trim($texto);

        // Si el texto no es UTF-8 válido, el json_encode de la petición
        // fallaría. Lo convertimos en lugar de rechazarlo.
        if ($texto !== '' && ! mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
        }

        return $texto;
    }

    /**
     * Convierte un nombre de archivo en un nombre de módulo válido.
     *
     *   "Grafico panel principal.md"  ->  grafico_panel_principal
     *   "REPORTE DE SOBRESTOCK.md"    ->  reporte_de_sobrestock
     *   "configuración.md"            ->  configuracion
     *
     * Se normaliza a minúsculas sin acentos y con guiones bajos para que el
     * mismo manual subido dos veces con el nombre escrito distinto no acabe
     * creando dos módulos separados en Qdrant.
     */
    private function moduloDesdeNombre(string $nombreArchivo): string
    {
        // pathinfo con PATHINFO_FILENAME devuelve el nombre sin la extensión.
        $base = pathinfo($nombreArchivo, PATHINFO_FILENAME);

        // Str::ascii pasa "configuración" a "configuracion".
        $base = Str::ascii($base);
        $base = strtolower($base);

        // Cualquier cosa que no sea letra o número se vuelve guion bajo,
        // y los repetidos se colapsan en uno solo.
        $base = preg_replace('/[^a-z0-9]+/', '_', $base);
        $base = trim($base, '_');

        // Un nombre como "___.md" se quedaría vacío tras la limpieza.
        return $base !== '' ? substr($base, 0, 60) : 'sin_nombre';
    }
}
