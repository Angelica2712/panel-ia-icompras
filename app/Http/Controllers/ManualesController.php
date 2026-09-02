<?php

namespace App\Http\Controllers;

use App\Services\QdrantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * ManualesController
 * -----------------------------------------------------------------------------
 * Módulo de "Carga de manuales" del panel administrativo.
 *
 * Qué hace, en orden:
 *   1. Muestra un formulario para subir un archivo .md (Markdown).
 *   2. Valida el archivo, la versión (light/full/ambas) y el módulo.
 *   3. Lee el contenido del .md como texto plano.
 *   4. Envía ese texto + versión + módulo por POST (JSON) al webhook de n8n.
 *   5. n8n se encarga de fragmentar, vectorizar y guardar en Qdrant.
 *   6. Devuelve al usuario un mensaje de éxito o de error.
 *
 * Nota: este controlador NO guarda el archivo en el servidor. Solo lee su
 * contenido en memoria y lo reenvía a n8n. Si algún día quieres conservar una
 * copia, mira el comentario marcado como [OPCIONAL] dentro de store().
 */
class ManualesController extends Controller
{
    /**
     * Versiones válidas del asistente a las que puede aplicar un manual.
     * Está aquí (y no escrito a mano en la vista) para que el desplegable
     * y la validación usen SIEMPRE la misma lista y no se desincronicen.
     */
    private const VERSIONES = ['light', 'full', 'ambas'];

    /**
     * Tamaño máximo del archivo, en kilobytes (1536 KB = 1.5 MB).
     *
     * Ojo: PHP tiene su propio límite en php.ini (upload_max_filesize, hoy 2M).
     * Este número debe quedar POR DEBAJO del de php.ini, no igual: si PHP
     * rechaza la subida antes que Laravel, el archivo ni siquiera llega y el
     * mensaje de error que ve la usuaria es confuso ("selecciona un archivo").
     * Dejando margen, el error siempre lo da Laravel y se entiende.
     *
     * 1.5 MB de texto son cerca de 1.5 millones de caracteres: de sobra para
     * cualquier manual. Si algún día necesitas más, sube primero
     * upload_max_filesize y post_max_size en php.ini, y luego este valor.
     */
    private const MAX_KB = 1536;

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
            'versiones'        => self::VERSIONES,
            'maxKb'            => self::MAX_KB,
            'manuales'         => $qdrantDisponible ? $qdrant->listarManuales() : [],
            'qdrantDisponible' => $qdrantDisponible,
        ]);
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

        return redirect()
            ->route('manuales.create')
            ->with('ok', 'Manual "' . $datos['modulo'] . '" (' . $datos['version'] . ') eliminado de la base de conocimiento.');
    }

    /**
     * POST /manuales
     * Recibe el formulario, valida, lee el .md y lo manda a n8n.
     */
    public function store(Request $request): RedirectResponse
    {
        // ---------------------------------------------------------------------
        // PASO 1: Validación
        // ---------------------------------------------------------------------
        // Si algo falla aquí, Laravel redirige solo hacia atrás con los errores
        // y con los datos anteriores (los recuperamos en la vista con old()).
        //
        // Sobre el archivo: NO usamos la regla "mimes:md" porque el tipo MIME
        // real de un .md suele llegar como text/plain o application/octet-stream
        // según el navegador y el sistema operativo, y la regla rechazaría
        // archivos perfectamente válidos. Por eso comprobamos la extensión
        // directamente con una regla de tipo closure (función anónima).
        $datos = $request->validate([
            'archivo' => [
                'required',
                'file',
                'max:' . self::MAX_KB,
                function (string $atributo, $valor, Closure $fallar) {
                    $extension = strtolower($valor->getClientOriginalExtension());

                    if ($extension !== 'md') {
                        $fallar('El archivo debe tener extensión .md (Markdown).');
                    }
                },
            ],

            // in:... obliga a que el valor sea exactamente uno de los de la lista.
            // implode convierte ['light','full','ambas'] en el texto "light,full,ambas".
            'version' => ['required', 'string', 'in:' . implode(',', self::VERSIONES)],

            // El módulo es de escritura libre, pero limitamos el formato para
            // evitar basura en los metadatos de Qdrant: solo letras, números,
            // espacios, guiones y guiones bajos.
            'modulo'  => ['required', 'string', 'min:2', 'max:60', 'regex:/^[\pL\pN _\-]+$/u'],
        ], [
            // Mensajes de error en español, para que se entienda qué pasó.
            'archivo.required' => 'Debes seleccionar un archivo Markdown.',
            'archivo.file'     => 'El archivo subido no es válido.',
            'archivo.max'      => 'El archivo no puede pesar más de ' . (self::MAX_KB / 1024) . ' MB.',
            'version.required' => 'Debes seleccionar una versión.',
            'version.in'       => 'La versión seleccionada no es válida.',
            'modulo.required'  => 'Debes indicar el módulo al que pertenece el manual.',
            'modulo.min'       => 'El módulo debe tener al menos 2 caracteres.',
            'modulo.max'       => 'El módulo no puede superar los 60 caracteres.',
            'modulo.regex'     => 'El módulo solo puede contener letras, números, espacios, guiones y guiones bajos.',
        ]);

        // ---------------------------------------------------------------------
        // PASO 2: Comprobar que el webhook esté configurado
        // ---------------------------------------------------------------------
        // La URL vive en .env y se lee a través de config/n8n.php.
        // Si está vacía avisamos antes de intentar la petición, así el error es
        // claro y no aparece como un fallo de red confuso.
        $webhookUrl = config('n8n.manuales.webhook_url');

        if (empty($webhookUrl)) {
            return back()
                ->withInput()
                ->with('error', 'El webhook de n8n no está configurado. Agrega N8N_MANUALES_WEBHOOK_URL en el archivo .env y ejecuta: php artisan config:clear');
        }

        // ---------------------------------------------------------------------
        // PASO 3: Leer el contenido del archivo como texto
        // ---------------------------------------------------------------------
        $archivo = $request->file('archivo');
        $texto   = $archivo->get(); // get() devuelve el contenido completo como string

        // Algunos editores (sobre todo en Windows) guardan un "BOM" invisible al
        // inicio del archivo. Si no lo quitamos, se cuela como basura dentro del
        // primer fragmento que se vectoriza.
        $texto = preg_replace('/^\x{FEFF}/u', '', $texto);
        $texto = trim($texto);

        // Un archivo vacío no aporta conocimiento: no tiene sentido enviarlo.
        if ($texto === '') {
            return back()
                ->withInput()
                ->with('error', 'El archivo está vacío. Sube un manual con contenido.');
        }

        // Si el texto no es UTF-8 válido, el json_encode de la petición fallaría.
        // Lo convertimos en lugar de rechazarlo, para no bloquear la carga.
        if (! mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
        }

        // [OPCIONAL] Si algún día quieres guardar una copia del .md en el
        // servidor, descomenta la línea siguiente. El archivo quedaría en
        // storage/app/manuales/ con un nombre único generado por Laravel:
        // $archivo->store('manuales');

        // ---------------------------------------------------------------------
        // PASO 4: Decidir cuántas cargas hay que hacer
        // ---------------------------------------------------------------------
        // El flujo de n8n guarda cada fragmento con una sola versión: "light" o
        // "full". No entiende "ambas". Así funciona también la carga masiva del
        // orquestador: para un manual que aplica a las dos versiones hay dos
        // filas separadas, una por versión.
        //
        // Replicamos exactamente ese comportamiento: si eligió "ambas",
        // mandamos DOS peticiones, una por cada versión.
        $versionesAEnviar = $datos['version'] === 'ambas'
            ? ['light', 'full']
            : [$datos['version']];

        // ---------------------------------------------------------------------
        // PASO 5: Enviar las peticiones POST al webhook de n8n
        // ---------------------------------------------------------------------
        $enviadas = [];

        foreach ($versionesAEnviar as $version) {
            // IMPORTANTE: los nombres de estas claves son los que se leen dentro
            // de n8n. Como llegan por un nodo Webhook, allá se acceden con el
            // prefijo "body": {{ $json.body.texto }}, {{ $json.body.modulo }}...
            $payload = [
                // --- Los tres datos que el flujo necesita sí o sí ---
                'texto'   => $texto,            // contenido completo del manual
                'version' => $version,          // siempre "light" o "full", nunca "ambas"
                'modulo'  => $datos['modulo'],  // catalogo, carrito, configuracion...

                // El nodo "Convert to File" usa este nombre para el archivo que
                // arma en memoria. Debe terminar en .md para que n8n lo trate
                // como Markdown y no como PDF.
                'nombre_archivo' => $archivo->getClientOriginalName(),

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
                    'modulo'  => $datos['modulo'],
                    'version' => $version,
                ]);

                return back()
                    ->withInput()
                    ->with('error', $this->mensajeParcial($enviadas)
                        . 'No se pudo conectar con n8n al cargar la versión "' . $version . '". '
                        . 'Revisa que el flujo esté activo y que la URL del webhook sea correcta.');
            }

            // -----------------------------------------------------------------
            // PASO 6: Interpretar la respuesta de n8n
            // -----------------------------------------------------------------
            // successful() es true cuando el código HTTP está entre 200 y 299.
            if (! $respuesta->successful()) {
                Log::warning('n8n respondió con error al cargar un manual', [
                    'status'  => $respuesta->status(),
                    'body'    => $respuesta->body(),
                    'modulo'  => $datos['modulo'],
                    'version' => $version,
                ]);

                return back()
                    ->withInput()
                    ->with('error', $this->mensajeParcial($enviadas)
                        . 'n8n rechazó la versión "' . $version . '" (código HTTP ' . $respuesta->status() . '). '
                        . 'Revisa la ejecución del flujo en n8n para ver el detalle.');
            }

            Log::info('Manual enviado a n8n correctamente', [
                'archivo' => $archivo->getClientOriginalName(),
                'modulo'  => $datos['modulo'],
                'version' => $version,
            ]);

            $enviadas[] = $version;
        }

        // Redirigimos al formulario limpio con el mensaje de éxito en la sesión.
        return redirect()
            ->route('manuales.create')
            ->with('ok', 'Manual cargado correctamente en Qdrant (' . implode(' y ', $enviadas) . ').')
            ->with('resumen', [
                'archivo' => $archivo->getClientOriginalName(),
                'modulo'  => $datos['modulo'],
                'version' => implode(' + ', $enviadas),
                'kb'      => round(strlen($texto) / 1024, 1),
            ]);
    }

    /**
     * Cuando se pidió "ambas" y la primera versión sí se cargó pero la segunda
     * falló, hay que avisarlo: parte del manual YA quedó en Qdrant. Si no se
     * dice, la usuaria reintenta y termina con la versión "light" duplicada.
     *
     * @param  array<int, string>  $enviadas  versiones que sí se cargaron
     */
    private function mensajeParcial(array $enviadas): string
    {
        if (empty($enviadas)) {
            return '';
        }

        return 'ATENCIÓN: la versión "' . implode('" y "', $enviadas)
            . '" SÍ se cargó en Qdrant. Si reintentas, quedará duplicada. ';
    }
}
