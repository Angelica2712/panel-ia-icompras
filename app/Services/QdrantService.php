<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * QdrantService
 * -----------------------------------------------------------------------------
 * Todo lo que el panel necesita hablar con Qdrant vive aquí.
 *
 * ¿Por qué una clase aparte y no dentro del controlador?
 * Porque el controlador ya tiene bastante trabajo (validar formularios,
 * mostrar mensajes). Si además metiéramos aquí las llamadas HTTP, el archivo
 * sería enorme y difícil de seguir. Separado, cada cosa hace lo suyo:
 *
 *   - El controlador  -> decide QUÉ hacer y qué mensaje mostrar
 *   - Este servicio   -> sabe CÓMO hablar con Qdrant
 *
 * Un manual no es una fila en Qdrant: son muchos "puntos" (fragmentos), todos
 * con los mismos metadatos de modulo y version. Por eso listar manuales es en
 * realidad agrupar fragmentos, y borrar un manual es borrar todos los que
 * compartan ese par modulo + version.
 */
class QdrantService
{
    /**
     * Cuántos puntos pedimos por página al recorrer la colección.
     * Qdrant no deja traerlo todo de golpe, hay que ir por tandas.
     */
    private const TAMANO_PAGINA = 1000;

    /**
     * Tope de seguridad: máximo de páginas a recorrer.
     * Sin esto, un error nuestro podría dejar el bucle girando para siempre.
     * 200 páginas x 1000 = 200.000 fragmentos, de sobra para este panel.
     */
    private const MAX_PAGINAS = 200;

    /**
     * Lista los manuales cargados, agrupados por módulo y versión.
     *
     * Recorre todos los puntos de la colección pidiendo SOLO los metadatos
     * (nunca el texto ni los vectores, que pesan muchísimo) y los va contando.
     *
     * @return array<int, array{modulo: string, version: string, fragmentos: int}>
     *         ordenado por módulo y luego por versión
     */
    public function listarManuales(): array
    {
        $conteo  = [];   // ['catalogo|light' => 12, ...]
        $offset  = null; // marcador de "por dónde iba" que devuelve Qdrant
        $pagina  = 0;

        do {
            $cuerpo = [
                'limit'       => self::TAMANO_PAGINA,
                'with_vector' => false,               // los vectores no nos sirven y pesan
                'with_payload' => ['modulo', 'version'], // solo estos dos campos
            ];

            // En la primera vuelta no hay offset; en las siguientes sí.
            if ($offset !== null) {
                $cuerpo['offset'] = $offset;
            }

            $respuesta = $this->peticion()->post($this->url('/points/scroll'), $cuerpo);

            if (! $respuesta->successful()) {
                Log::warning('Qdrant no respondió al listar manuales', [
                    'status' => $respuesta->status(),
                    'body'   => $respuesta->body(),
                ]);

                // Devolvemos lo que llevemos contado en lugar de reventar.
                break;
            }

            $resultado = $respuesta->json('result') ?? [];

            foreach ($resultado['points'] ?? [] as $punto) {
                $modulo  = $punto['payload']['modulo']  ?? null;
                $version = $punto['payload']['version'] ?? null;

                // Un fragmento sin metadatos es basura de una carga que falló
                // a medias. Lo agrupamos aparte para que se vea en el listado
                // y se pueda limpiar.
                $clave = ($modulo ?: '(sin módulo)') . '|' . ($version ?: '(sin versión)');

                $conteo[$clave] = ($conteo[$clave] ?? 0) + 1;
            }

            // Qdrant devuelve next_page_offset = null cuando ya no queda nada.
            $offset = $resultado['next_page_offset'] ?? null;
            $pagina++;
        } while ($offset !== null && $pagina < self::MAX_PAGINAS);

        // Convertimos el array de conteos en algo cómodo para la vista.
        $manuales = [];

        foreach ($conteo as $clave => $fragmentos) {
            [$modulo, $version] = explode('|', $clave, 2);

            $manuales[] = [
                'modulo'     => $modulo,
                'version'    => $version,
                'fragmentos' => $fragmentos,
            ];
        }

        // Ordenamos por módulo y, dentro de cada módulo, por versión.
        usort($manuales, function ($a, $b) {
            return [$a['modulo'], $a['version']] <=> [$b['modulo'], $b['version']];
        });

        return $manuales;
    }

    /**
     * Borra todos los fragmentos de un manual (un módulo en una versión).
     *
     * @param  string  $modulo   por ejemplo "catalogo"
     * @param  string  $version  "light" o "full"
     * @return bool  true si Qdrant confirmó el borrado
     */
    public function borrarManual(string $modulo, string $version): bool
    {
        // El filtro dice: borra los puntos donde modulo Y version coincidan.
        // Es la misma forma de filtrar que usa n8n cuando rellena metadatos.
        $filtro = [
            'filter' => [
                'must' => [
                    ['key' => 'modulo',  'match' => ['value' => $modulo]],
                    ['key' => 'version', 'match' => ['value' => $version]],
                ],
            ],
        ];

        // wait=true hace que Qdrant responda cuando el borrado YA está aplicado.
        // Sin eso responde de inmediato y el listado podría recargarse antes
        // de que el borrado surta efecto, mostrando el manual como si siguiera.
        $respuesta = $this->peticion()->post($this->url('/points/delete?wait=true'), $filtro);

        if (! $respuesta->successful()) {
            Log::warning('Qdrant rechazó el borrado de un manual', [
                'status'  => $respuesta->status(),
                'body'    => $respuesta->body(),
                'modulo'  => $modulo,
                'version' => $version,
            ]);

            return false;
        }

        Log::info('Manual borrado de Qdrant', [
            'modulo'  => $modulo,
            'version' => $version,
        ]);

        return true;
    }

    /**
     * ¿Está Qdrant accesible y existe la colección?
     * Sirve para avisar en pantalla en vez de mostrar una tabla vacía
     * que haría pensar que no hay manuales cargados.
     */
    public function disponible(): bool
    {
        try {
            return $this->peticion()->get($this->url())->successful();
        } catch (\Throwable $e) {
            Log::warning('No se pudo conectar con Qdrant', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Arma la URL completa de la colección.
     * $sufijo se añade al final, por ejemplo "/points/scroll".
     */
    private function url(string $sufijo = ''): string
    {
        return rtrim(config('qdrant.url'), '/')
            . '/collections/' . config('qdrant.collection')
            . $sufijo;
    }

    /**
     * Prepara la petición HTTP con el timeout y, si hace falta, la API key.
     */
    private function peticion()
    {
        $peticion = Http::timeout(config('qdrant.timeout'))->acceptJson();

        // El Qdrant local no pide clave; el de producción sí.
        $apiKey = config('qdrant.api_key');

        if (! empty($apiKey)) {
            $peticion = $peticion->withHeaders(['api-key' => $apiKey]);
        }

        return $peticion;
    }
}
