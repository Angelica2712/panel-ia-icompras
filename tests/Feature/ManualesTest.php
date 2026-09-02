<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\QdrantService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ManualesTest extends TestCase
{
    private function usuario(): User
    {
        $u = User::first();
        if (! $u) {
            $this->markTestSkipped('No hay usuarios en la BD');
        }
        return $u;
    }

    public function test_la_vista_carga(): void
    {
        $this->actingAs($this->usuario())
            ->get('/manuales')
            ->assertOk()
            ->assertSee('Subir manual')
            ->assertSee('multipart/form-data', false)
            ->assertSee("name=\"modulo\"", false)
            ->assertDontSee("Rango de Fechas");
    }

    public function test_envia_el_manual_a_n8n(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $md = UploadedFile::fake()->createWithContent('manual-catalogo.md', "# Catalogo\n\nComo buscar productos.");

        $this->actingAs($this->usuario())
            ->post('/manuales', ['archivos' => [$md], 'version' => 'light', 'modulo' => 'catalogo'])
            ->assertRedirect('/manuales')
            ->assertSessionHas('resultados');

        Http::assertSentCount(1);

        Http::assertSent(function ($request) {
            $d = $request->data();
            return $request->url() === 'https://n8n.example.test/webhook/manuales'
                && $request->method() === 'POST'
                && $request->hasHeader('Content-Type', 'application/json')
                && $d['version'] === 'light'
                && $d['modulo']  === 'catalogo'
                && str_contains($d['texto'], 'Como buscar productos')
                && $d['nombre_archivo'] === 'manual-catalogo.md';
        });
    }

    /**
     * El flujo de n8n guarda una sola versión por carga ("light" o "full"),
     * igual que la carga masiva del orquestador. Cuando se pide "ambas",
     * el controlador debe mandar DOS peticiones, nunca una con version="ambas".
     */
    public function test_ambas_dispara_dos_cargas_una_por_version(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $md = UploadedFile::fake()->createWithContent('carrito.md', '# Carrito');

        $this->actingAs($this->usuario())
            ->post('/manuales', ['archivos' => [$md], 'version' => 'ambas', 'modulo' => 'carrito'])
            ->assertRedirect('/manuales')
            ->assertSessionHas('resultados');

        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => $r->data()['version'] === 'light');
        Http::assertSent(fn ($r) => $r->data()['version'] === 'full');
        // Nunca debe salir "ambas": n8n no sabe qué hacer con ese valor.
        Http::assertNotSent(fn ($r) => $r->data()['version'] === 'ambas');
    }

    /**
     * Si la primera versión se cargó y la segunda falla, el manual quedó a
     * medias en Qdrant. Hay que avisarlo, porque un reintento a ciegas
     * duplicaría los fragmentos de la versión que sí entró.
     */
    public function test_avisa_si_una_carga_quedo_a_medias(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);

        // La 1a peticion (light) funciona, la 2a (full) falla.
        Http::fakeSequence()
            ->push(['ok' => true], 200)
            ->push('boom', 500);

        $md = UploadedFile::fake()->createWithContent('pedido.md', '# Pedido');

        $this->actingAs($this->usuario())
            ->post('/manuales', ['archivos' => [$md], 'version' => 'ambas', 'modulo' => 'pedido'])
            ->assertSessionHas('resultados');

        $r = session('resultados')[0];

        // El resumen deja ver que "light" SI entro aunque el archivo fallara:
        // sin ese detalle, un reintento a ciegas duplicaria esa version.
        $this->assertFalse($r['ok']);
        $this->assertSame(['light'], $r['versiones']);
        $this->assertStringContainsString('full', $r['detalle']);
    }

    public function test_rechaza_archivo_que_no_es_md(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake();

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [UploadedFile::fake()->createWithContent('manual.txt', 'hola')],
                'version' => 'light',
                'modulo'  => 'carrito',
            ])
            ->assertSessionHasErrors('archivos.0');

        Http::assertNothingSent();
    }

    public function test_rechaza_version_invalida(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake();

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [UploadedFile::fake()->createWithContent('m.md', 'hola')],
                'version'  => 'premium',
                'modulo'   => 'catalogo',
            ])
            ->assertSessionHasErrors('version');

        Http::assertNothingSent();
    }

    /**
     * Con UN archivo el modulo es obligatorio. Podria deducirse del nombre,
     * pero es el caso normal y conviene que se escriba: un nombre de archivo
     * descuidado crearia un modulo nuevo en Qdrant sin que nadie lo note.
     */
    public function test_un_solo_archivo_exige_modulo(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake();

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [UploadedFile::fake()->createWithContent('m.md', 'hola')],
                'version'  => 'light',
                'modulo'   => '',
            ])
            ->assertSessionHasErrors('modulo');

        Http::assertNothingSent();
    }

    public function test_avisa_si_el_webhook_no_esta_configurado(): void
    {
        config(['n8n.manuales.webhook_url' => null]);
        Http::fake();

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [UploadedFile::fake()->createWithContent('m.md', 'hola')],
                'version' => 'full',
                'modulo'  => 'pedidos',
            ])
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_muestra_error_si_n8n_falla(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake(['*' => Http::response('boom', 500)]);

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [UploadedFile::fake()->createWithContent('m.md', 'hola')],
                'version'  => 'full',
                'modulo'   => 'pedidos',
            ])
            ->assertSessionHas('resultados');

        $this->assertFalse(session('resultados')[0]['ok']);
    }

    public function test_rechaza_archivo_vacio(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake();

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [UploadedFile::fake()->createWithContent('vacio.md', "   \n  ")],
                'version' => 'light',
                'modulo'  => 'catalogo',
            ])
            ->assertSessionHas('resultados');

        $r = session('resultados')[0];
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('vac', $r['detalle']);

        Http::assertNothingSent();
    }

    // ---------------------------------------------------------------------
    // Carga masiva
    // ---------------------------------------------------------------------

    /**
     * Con varios archivos no hay forma de escribir un modulo por cada uno,
     * asi que sale del nombre del archivo, normalizado.
     */
    public function test_carga_masiva_toma_el_modulo_del_nombre_del_archivo(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [
                    UploadedFile::fake()->createWithContent('Grafico panel principal.md', '# Grafico'),
                    UploadedFile::fake()->createWithContent('REPORTE DE SOBRESTOCK.md', '# Sobrestock'),
                    UploadedFile::fake()->createWithContent('configuracion.md', '# Config'),
                ],
                'version' => 'light',
            ])
            ->assertSessionHas('resultados');

        $modulos = array_column(session('resultados'), 'modulo');

        $this->assertSame(
            ['grafico_panel_principal', 'reporte_de_sobrestock', 'configuracion'],
            $modulos
        );

        Http::assertSentCount(3);
    }

    /**
     * En carga masiva NO se pide modulo: se ignora si viene.
     */
    public function test_carga_masiva_no_exige_modulo(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [
                    UploadedFile::fake()->createWithContent('carrito.md', '# Carrito'),
                    UploadedFile::fake()->createWithContent('catalogo.md', '# Catalogo'),
                ],
                'version' => 'ambas',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('resultados');

        // 2 archivos x 2 versiones = 4 peticiones
        Http::assertSentCount(4);
    }

    /**
     * Si un archivo del lote falla, los demas deben cargarse igual.
     * Abortar todo por uno seria peor: en un lote de quince manuales,
     * dejaria catorce sin cargar sin razon.
     */
    public function test_carga_masiva_sigue_aunque_un_archivo_falle(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);

        Http::fakeSequence()
            ->push(['ok' => true], 200)   // carrito -> bien
            ->push('boom', 500)           // catalogo -> falla
            ->push(['ok' => true], 200);  // pedido -> bien

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [
                    UploadedFile::fake()->createWithContent('carrito.md', '# Carrito'),
                    UploadedFile::fake()->createWithContent('catalogo.md', '# Catalogo'),
                    UploadedFile::fake()->createWithContent('pedido.md', '# Pedido'),
                ],
                'version' => 'light',
            ])
            ->assertSessionHas('resultados');

        $res = session('resultados');

        $this->assertCount(3, $res);
        $this->assertTrue($res[0]['ok'], 'carrito deberia haber entrado');
        $this->assertFalse($res[1]['ok'], 'catalogo deberia haber fallado');
        $this->assertTrue($res[2]['ok'], 'pedido deberia haber entrado pese al fallo anterior');
    }

    public function test_carga_masiva_rechaza_un_archivo_que_no_es_md(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake();

        $this->actingAs($this->usuario())
            ->post('/manuales', [
                'archivos' => [
                    UploadedFile::fake()->createWithContent('bueno.md', '# Bueno'),
                    UploadedFile::fake()->createWithContent('malo.txt', 'texto'),
                ],
                'version' => 'light',
            ])
            ->assertSessionHasErrors('archivos.1');

        // Ni siquiera el archivo valido se envia: la validacion corta antes.
        Http::assertNothingSent();
    }

    public function test_carga_masiva_limita_la_cantidad_de_archivos(): void
    {
        config(['n8n.manuales.webhook_url' => 'https://n8n.example.test/webhook/manuales']);
        Http::fake();

        $muchos = [];
        for ($i = 0; $i < 21; $i++) {
            $muchos[] = UploadedFile::fake()->createWithContent("m{$i}.md", '# Manual');
        }

        $this->actingAs($this->usuario())
            ->post('/manuales', ['archivos' => $muchos, 'version' => 'light'])
            ->assertSessionHasErrors('archivos');

        Http::assertNothingSent();
    }

    public function test_requiere_estar_autenticado(): void
    {
        $this->get('/manuales')->assertRedirect('/login');
        $this->delete('/manuales', ['modulo' => 'x', 'version' => 'light'])->assertRedirect('/login');
    }

    /**
     * Sustituye el QdrantService real por un doble de pruebas, para que los
     * tests no dependan de que Qdrant esté encendido.
     */
    private function fingirQdrant(array $manuales, bool $disponible = true, bool $borradoOk = true): \Mockery\MockInterface
    {
        $doble = \Mockery::mock(QdrantService::class);
        $doble->shouldReceive('disponible')->andReturn($disponible);
        $doble->shouldReceive('listarManuales')->andReturn($manuales);
        $doble->shouldReceive('borrarManual')->andReturn($borradoOk);

        $this->app->instance(QdrantService::class, $doble);

        return $doble;
    }

    public function test_lista_los_manuales_cargados(): void
    {
        $this->fingirQdrant([
            ['modulo' => 'catalogo', 'version' => 'light', 'fragmentos' => 12],
            ['modulo' => 'carrito',  'version' => 'full',  'fragmentos' => 30],
        ]);

        $this->actingAs($this->usuario())
            ->get('/manuales')
            ->assertOk()
            ->assertSee('Manuales cargados')
            ->assertSee('catalogo')
            ->assertSee('carrito')
            ->assertSee('12')
            ->assertSee('30')
            // El total de fragmentos de las dos filas.
            ->assertSee('42');
    }

    public function test_avisa_si_qdrant_no_responde(): void
    {
        $this->fingirQdrant([], disponible: false);

        $this->actingAs($this->usuario())
            ->get('/manuales')
            ->assertOk()
            ->assertSee('No se pudo conectar con Qdrant')
            // El formulario de carga debe seguir funcionando aunque el
            // listado no esté disponible: son cosas independientes.
            ->assertSee('Subir manual');
    }

    public function test_borra_un_manual(): void
    {
        $doble = $this->fingirQdrant([]);

        $this->actingAs($this->usuario())
            ->delete('/manuales', ['modulo' => 'catalogo', 'version' => 'light'])
            ->assertRedirect('/manuales')
            ->assertSessionHas('ok');

        $doble->shouldHaveReceived('borrarManual')->with('catalogo', 'light')->once();
    }

    public function test_avisa_si_el_borrado_falla(): void
    {
        $this->fingirQdrant([], borradoOk: false);

        $this->actingAs($this->usuario())
            ->delete('/manuales', ['modulo' => 'catalogo', 'version' => 'light'])
            ->assertSessionHas('error');
    }

    public function test_el_borrado_exige_modulo_y_version(): void
    {
        $doble = $this->fingirQdrant([]);

        $this->actingAs($this->usuario())
            ->delete('/manuales', ['modulo' => '', 'version' => ''])
            ->assertSessionHasErrors(['modulo', 'version']);

        // Con datos inválidos no debe llegar a tocar Qdrant.
        $doble->shouldNotHaveReceived('borrarManual');
    }

    /**
     * El enlace "Manuales" debe aparecer en el menú lateral de TODAS las
     * pantallas del panel. Ojo: no todas las vistas usan layouts/panel.blade.php;
     * dashboard/index.blade.php tiene su propio menú copiado a mano, así que si
     * alguien agrega un módulo nuevo hay que tocar los dos sitios.
     * Este test avisa si se olvida uno.
     */
    public function test_el_enlace_de_manuales_esta_en_todas_las_pantallas(): void
    {
        $usuario = $this->usuario();

        foreach (['/dashboard', '/conversaciones', '/farmacias', '/rendimiento', '/usuarios', '/manuales'] as $url) {
            $this->actingAs($usuario)
                ->get($url)
                ->assertOk()
                ->assertSee('nav-manuales', false);
        }
    }
}
