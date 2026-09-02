{{--
    Vista: Carga de manuales
    ---------------------------------------------------------------------------
    Formulario para subir un archivo .md y enviarlo al flujo de n8n.

    Variables que llegan desde ManualesController@create:
      $versiones        -> ['light', 'full', 'ambas']
      $maxKb            -> tamaño máximo permitido, en KB
--}}
@extends('layouts.panel')

@section('title', 'Manuales')
@section('page-title', 'Carga de manuales')
@section('page-subtitle', 'Alimenta la base de conocimiento del asistente de IA')

{{-- Estilos propios de esta pantalla. El layout los inserta dentro del <style> --}}
@section('extra-styles')
    .form-manual { display: flex; flex-direction: column; gap: 18px; margin-top: 6px; }

    .campo { display: flex; flex-direction: column; gap: 6px; }

    .campo label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .campo label i { color: var(--accent-light); font-size: 13px; }

    .campo .ayuda { font-size: 11px; color: var(--text-muted); line-height: 1.5; }

    /* Aspecto común de todos los controles del formulario */
    .campo input[type="text"],
    .campo input[type="file"],
    .campo select {
        width: 100%;
        background: rgba(11, 30, 61, 0.6);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-family: inherit;
        font-size: 13px;
        padding: 10px 12px;
        outline: none;
        transition: border-color .18s, box-shadow .18s;
    }
    .campo input[type="text"]:focus,
    .campo input[type="file"]:focus,
    .campo select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    /* El botón "Examinar" del input de archivo */
    .campo input[type="file"]::file-selector-button {
        background: rgba(59, 130, 246, 0.18);
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 6px;
        color: var(--accent-light);
        cursor: pointer;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        margin-right: 12px;
        padding: 6px 12px;
    }
    .campo input[type="file"]::file-selector-button:hover { background: rgba(59, 130, 246, 0.3); }

    /* Marca en rojo el campo que falló la validación */
    .campo input.con-error, .campo select.con-error { border-color: var(--red); }
    .campo .error-msg { font-size: 11px; color: #fca5a5; display: flex; align-items: center; gap: 5px; }

    .fila-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    @media (max-width: 720px) { .fila-2col { grid-template-columns: 1fr; } }

    .form-acciones { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

    /* Cajas de mensaje de éxito / error */
    .alerta {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 12.5px;
        line-height: 1.55;
        margin-bottom: 16px;
        border: 1px solid transparent;
    }
    .alerta i { font-size: 15px; margin-top: 1px; flex-shrink: 0; }
    .alerta-ok    { background: rgba(16, 185, 129, 0.12); border-color: rgba(16, 185, 129, 0.3); color: #6ee7b7; }
    .alerta-error { background: rgba(239, 68, 68, 0.12);  border-color: rgba(239, 68, 68, 0.3);  color: #fca5a5; }

    .resumen-envio {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }
    .resumen-envio span {
        background: rgba(11, 30, 61, 0.55);
        border: 1px solid var(--border);
        border-radius: 20px;
        color: var(--text-muted);
        font-size: 11px;
        padding: 3px 10px;
    }
    .resumen-envio span strong { color: var(--text-primary); font-weight: 600; }

    /* Tabla de manuales cargados */
    .tabla-wrap { overflow-x: auto; margin-top: 4px; }

    .tabla-manuales { width: 100%; border-collapse: collapse; font-size: 12.5px; min-width: 520px; }

    .tabla-manuales thead th {
        border-bottom: 1px solid var(--border);
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.4px;
        padding: 8px 10px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .tabla-manuales tbody td {
        border-bottom: 1px solid rgba(70, 130, 220, 0.09);
        padding: 10px;
        vertical-align: middle;
    }
    .tabla-manuales tbody tr:last-child td { border-bottom: none; }
    .tabla-manuales tbody tr:hover { background: rgba(59, 130, 246, 0.05); }

    .celda-modulo { font-weight: 600; }

    /* Etiquetas de versión */
    .tag-version {
        border-radius: 20px;
        display: inline-block;
        font-size: 10.5px;
        font-weight: 600;
        padding: 3px 10px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .tag-light  { background: rgba(59, 130, 246, 0.16); color: var(--accent-light); }
    .tag-full   { background: rgba(139, 92, 246, 0.16); color: #c4b5fd; }
    .tag-otra   { background: rgba(239, 68, 68, 0.14);  color: #fca5a5; }

    .celda-frags { color: var(--text-muted); font-variant-numeric: tabular-nums; }

    .btn-borrar {
        align-items: center;
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.28);
        border-radius: 7px;
        color: #fca5a5;
        cursor: pointer;
        display: inline-flex;
        font-family: inherit;
        font-size: 11.5px;
        font-weight: 600;
        gap: 6px;
        padding: 6px 12px;
        transition: background .18s;
    }
    .btn-borrar:hover { background: rgba(239, 68, 68, 0.25); }
    .btn-borrar:active { transform: scale(0.97); }

    .tabla-vacia {
        color: var(--text-muted);
        font-size: 12px;
        padding: 24px 10px;
        text-align: center;
    }
    .tabla-vacia i { display: block; font-size: 22px; margin-bottom: 8px; opacity: 0.5; }

    .alerta-mixta { background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.32); color: #fcd34d; }

    .estado-ok  { color: var(--green); font-size: 12px; white-space: nowrap; }
    .estado-mal { color: #fca5a5; font-size: 12px; }

    .resumen-total {
        border-top: 1px solid var(--border);
        color: var(--text-muted);
        font-size: 11px;
        margin-top: 12px;
        padding-top: 10px;
    }
@endsection

@section('content')

{{-- ===================== Mensajes de éxito / error ===================== --}}
{{-- session('ok') y session('error') los pone el controlador con ->with(...) --}}
@if(session('ok'))
    <div class="alerta alerta-ok">
        <i class="bi bi-check-circle-fill"></i>
        <div>{{ session('ok') }}</div>
    </div>
@endif

{{-- ===================== Resumen de la carga ===================== --}}
{{-- Una fila por archivo. En carga masiva es la única forma de saber
     cuáles entraron y cuáles no: un solo mensaje global no sirve. --}}
@if(session('resultados'))
    @php
        $res      = session('resultados');
        $okCount  = collect($res)->where('ok', true)->count();
        $malCount = count($res) - $okCount;
    @endphp
    <div class="alerta {{ $malCount === 0 ? 'alerta-ok' : ($okCount === 0 ? 'alerta-error' : 'alerta-mixta') }}">
        <i class="bi {{ $malCount === 0 ? 'bi-check-circle-fill' : ($okCount === 0 ? 'bi-x-circle-fill' : 'bi-exclamation-circle-fill') }}"></i>
        <div style="width:100%;">
            <strong>
                @if($malCount === 0)
                    {{ $okCount }} {{ $okCount === 1 ? 'manual cargado' : 'manuales cargados' }} correctamente.
                @elseif($okCount === 0)
                    No se pudo cargar {{ $malCount === 1 ? 'el manual' : 'ningún manual' }}.
                @else
                    {{ $okCount }} de {{ count($res) }} manuales cargados. Revisa los que fallaron.
                @endif
            </strong>

            <div class="tabla-wrap" style="margin-top:10px;">
                <table class="tabla-manuales" style="min-width:460px;">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Módulo</th>
                            <th>Versiones</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($res as $r)
                            <tr>
                                <td>{{ $r['archivo'] }}</td>
                                <td class="celda-modulo">{{ $r['modulo'] }}</td>
                                <td>
                                    @forelse($r['versiones'] as $v)
                                        <span class="tag-version {{ $v === 'light' ? 'tag-light' : 'tag-full' }}">{{ $v }}</span>
                                    @empty
                                        <span class="celda-frags">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($r['ok'])
                                        <span class="estado-ok"><i class="bi bi-check-lg"></i> {{ $r['kb'] }} KB</span>
                                    @else
                                        <span class="estado-mal"><i class="bi bi-x-lg"></i> {{ $r['detalle'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alerta alerta-error">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif

<div class="chart-card" style="margin-bottom:0;">
    <div class="chart-card-header">
        <div>
            <div class="chart-title"><i class="bi bi-file-earmark-arrow-up"></i> Subir manuales</div>
            <div class="chart-subtitle">Markdown (.md) — hasta {{ $maxArchivos }} archivos, {{ $maxKb / 1024 }} MB cada uno</div>
        </div>
    </div>

    {{--
        enctype="multipart/form-data" es OBLIGATORIO para subir archivos.
        Sin eso, el archivo nunca llega al servidor.
        @csrf inserta el token de seguridad que Laravel exige en todo POST.
    --}}
    <form class="form-manual"
          method="POST"
          action="{{ route('manuales.store') }}"
          enctype="multipart/form-data"
          id="formManual">
        @csrf

        {{-- ---------- Campo 1: uno o varios .md ---------- --}}
        {{-- El atributo "multiple" y el nombre terminado en [] son lo que
             convierte esto en carga masiva: Laravel recibe un array. --}}
        <div class="campo">
            <label for="archivos">
                <i class="bi bi-filetype-md"></i> Archivos del manual
            </label>
            <input type="file"
                   name="archivos[]"
                   id="archivos"
                   accept=".md,text/markdown"
                   multiple
                   class="{{ $errors->has('archivos') || $errors->has('archivos.*') ? 'con-error' : '' }}"
                   required>
            <span class="ayuda">
                Puedes seleccionar <strong>varios a la vez</strong> con Ctrl (o Cmd en Mac).
                Solo archivos <strong>.md</strong>; el contenido se lee como texto y se envía
                a n8n, sin guardarse en el servidor.
            </span>
            @error('archivos')
                <span class="error-msg"><i class="bi bi-x-circle"></i> {{ $message }}</span>
            @enderror
            @error('archivos.*')
                <span class="error-msg"><i class="bi bi-x-circle"></i> {{ $message }}</span>
            @enderror
        </div>

        {{-- Vista previa: qué se va a cargar y con qué módulo.
             Se rellena por JavaScript al elegir los archivos. --}}
        <div class="campo" id="previaWrap" style="display:none;">
            <label><i class="bi bi-list-check"></i> Se van a cargar</label>
            <div class="tabla-wrap">
                <table class="tabla-manuales" id="tablaPrevia" style="min-width:420px;">
                    <thead>
                        <tr><th>Archivo</th><th>Módulo</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <span class="ayuda" id="previaNota"></span>
        </div>

        <div class="fila-2col">

            {{-- ---------- Campo 2: versión ---------- --}}
            <div class="campo">
                <label for="version">
                    <i class="bi bi-layers"></i> Versión
                </label>
                <select name="version"
                        id="version"
                        class="{{ $errors->has('version') ? 'con-error' : '' }}"
                        required>
                    <option value="">— Selecciona una versión —</option>
                    {{--
                        Recorremos el array $versiones que envía el controlador.
                        old('version') vuelve a marcar lo que la usuaria eligió
                        antes, si el formulario falló la validación.
                    --}}
                    @foreach($versiones as $v)
                        <option value="{{ $v }}" @selected(old('version') === $v)>
                            {{ ucfirst($v) }}
                        </option>
                    @endforeach
                </select>
                <span class="ayuda">A qué versión del asistente aplica este conocimiento.</span>
                @error('version')
                    <span class="error-msg"><i class="bi bi-x-circle"></i> {{ $message }}</span>
                @enderror
            </div>

            {{-- ---------- Campo 3: módulo ---------- --}}
            <div class="campo">
                <label for="modulo">
                    <i class="bi bi-grid-3x3-gap"></i> Módulo
                </label>
                {{-- Escritura libre. Con varios archivos se deshabilita por
                     JavaScript, porque cada uno toma su módulo del nombre. --}}
                <input type="text"
                       name="modulo"
                       id="modulo"
                       value="{{ old('modulo') }}"
                       placeholder="Ej: catalogo"
                       maxlength="60"
                       autocomplete="off"
                       class="{{ $errors->has('modulo') ? 'con-error' : '' }}">
                <span class="ayuda" id="ayudaModulo">
                    Escribe el nombre del módulo. Si vas a actualizar uno que ya existe,
                    cópialo tal cual aparece en la tabla de abajo: <strong>catalogo</strong> y
                    <strong>Catálogo</strong> se guardan como dos módulos distintos.
                </span>
                @error('modulo')
                    <span class="error-msg"><i class="bi bi-x-circle"></i> {{ $message }}</span>
                @enderror
            </div>

        </div>

        {{-- ---------- Botón de envío ---------- --}}
        <div class="form-acciones">
            <button type="submit" class="btn-filter btn-filter-primary" id="btnEnviar">
                <i class="bi bi-cloud-arrow-up"></i> Enviar a la base de conocimiento
            </button>
            <span class="ayuda" id="avisoProcesando" style="display:none;">
                <i class="bi bi-hourglass-split"></i> Procesando… la vectorización puede tardar varios segundos.
            </span>
        </div>
    </form>
</div>


{{-- ===================== Manuales ya cargados en Qdrant ===================== --}}
<div class="chart-card" style="margin-top:18px;margin-bottom:0;">
    <div class="chart-card-header">
        <div>
            <div class="chart-title"><i class="bi bi-database-check"></i> Manuales cargados</div>
            <div class="chart-subtitle">Conocimiento que el asistente tiene disponible ahora mismo</div>
        </div>
        @if($qdrantDisponible)
            <span class="chart-badge">{{ count($manuales) }} {{ count($manuales) === 1 ? 'manual' : 'manuales' }}</span>
        @endif
    </div>

    @if(! $qdrantDisponible)
        {{-- Qdrant no responde: avisamos en vez de mostrar una tabla vacía,
             que haría pensar que no hay ningún manual cargado. --}}
        <div class="alerta alerta-error" style="margin-bottom:0;">
            <i class="bi bi-plug"></i>
            <div>
                No se pudo conectar con Qdrant. El listado no está disponible.
                <span class="ayuda" style="display:block;margin-top:4px;">
                    Revisa que el contenedor esté encendido y que <code>QDRANT_URL</code> del archivo
                    <code>.env</code> sea correcta.
                </span>
            </div>
        </div>
    @else
        <div class="tabla-wrap">
            <table class="tabla-manuales">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th>Versión</th>
                        <th>Fragmentos</th>
                        <th style="text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manuales as $m)
                        <tr>
                            <td class="celda-modulo">{{ $m['modulo'] }}</td>
                            <td>
                                {{-- Una versión que no sea light ni full indica una carga
                                     rara o incompleta, por eso se marca en rojo. --}}
                                @php
                                    $claseTag = match($m['version']) {
                                        'light' => 'tag-light',
                                        'full'  => 'tag-full',
                                        default => 'tag-otra',
                                    };
                                @endphp
                                <span class="tag-version {{ $claseTag }}">{{ $m['version'] }}</span>
                            </td>
                            <td class="celda-frags">{{ number_format($m['fragmentos']) }}</td>
                            <td style="text-align:right;">
                                {{-- Un formulario por fila. @method('DELETE') le dice a Laravel
                                     que use el verbo DELETE, porque los navegadores solo saben
                                     mandar GET y POST desde un formulario. --}}
                                <form method="POST" action="{{ route('manuales.destroy') }}"
                                      class="form-borrar" style="display:inline;"
                                      data-modulo="{{ $m['modulo'] }}"
                                      data-version="{{ $m['version'] }}"
                                      data-fragmentos="{{ $m['fragmentos'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="modulo"  value="{{ $m['modulo'] }}">
                                    <input type="hidden" name="version" value="{{ $m['version'] }}">
                                    <button type="submit" class="btn-borrar">
                                        <i class="bi bi-trash3"></i> Borrar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="tabla-vacia">
                                <i class="bi bi-inbox"></i>
                                Todavía no hay manuales cargados.
                                Sube el primero con el formulario de arriba.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($manuales) > 0)
            <p class="resumen-total">
                <i class="bi bi-info-circle"></i>
                Total: <strong>{{ number_format(array_sum(array_column($manuales, 'fragmentos'))) }}</strong> fragmentos
                en la colección <code>{{ config('qdrant.collection') }}</code>.
                Borrar un manual elimina todos sus fragmentos y <strong>no se puede deshacer</strong>:
                para recuperarlo hay que volver a subir el archivo.
            </p>
        @endif
    @endif
</div>
@endsection

@section('extra-scripts')
<script>
// Al enviar el formulario deshabilitamos el botón y mostramos un aviso.
// Así evitamos que se hagan dos envíos seguidos por doble clic, porque la
// vectorización puede tardar y parece que "no pasa nada".
(function () {
    const form   = document.getElementById('formManual');
    const boton  = document.getElementById('btnEnviar');
    const aviso  = document.getElementById('avisoProcesando');

    if (!form) return;

    form.addEventListener('submit', function () {
        // Damos un instante al navegador para que envíe el formulario
        // antes de deshabilitar el botón (si se deshabilita de inmediato,
        // algunos navegadores no envían el dato del botón).
        setTimeout(function () {
            boton.disabled = true;
            boton.style.opacity = '0.6';
            boton.style.cursor = 'not-allowed';
            aviso.style.display = 'inline-flex';
        }, 10);
    });
})();

// Vista previa de la carga: al elegir archivos, muestra qué se va a subir
// y con qué módulo. Con varios archivos el módulo sale del nombre del
// archivo, así que el campo de texto se deshabilita para no confundir.
//
// Esta función replica moduloDesdeNombre() del controlador. Si cambias una,
// cambia la otra: aquí es solo una previsualización, el valor que cuenta
// siempre lo calcula el servidor.
(function () {
    const input      = document.getElementById('archivos');
    const modulo     = document.getElementById('modulo');
    const ayuda      = document.getElementById('ayudaModulo');
    const previaWrap = document.getElementById('previaWrap');
    const tabla      = document.querySelector('#tablaPrevia tbody');
    const nota       = document.getElementById('previaNota');

    if (!input) return;

    const ayudaOriginal = ayuda.innerHTML;

    function moduloDesdeNombre(nombre) {
        return nombre
            .replace(/\.[^.]+$/, '')                      // quita la extensión
            .normalize('NFD').replace(/[̀-ͯ]/g, '')  // quita acentos
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')                  // símbolos -> guion bajo
            .replace(/^_+|_+$/g, '')
            .slice(0, 60) || 'sin_nombre';
    }

    input.addEventListener('change', function () {
        const archivos = Array.from(input.files || []);
        tabla.innerHTML = '';

        if (archivos.length === 0) {
            previaWrap.style.display = 'none';
            modulo.disabled = false;
            modulo.required = true;
            ayuda.innerHTML = ayudaOriginal;
            return;
        }

        const masivo = archivos.length > 1;

        archivos.forEach(function (f) {
            const fila = document.createElement('tr');
            const celdaArchivo = document.createElement('td');
            const celdaModulo  = document.createElement('td');

            celdaArchivo.textContent = f.name;
            celdaModulo.className = 'celda-modulo';
            celdaModulo.textContent = masivo ? moduloDesdeNombre(f.name) : (modulo.value || '—');

            fila.appendChild(celdaArchivo);
            fila.appendChild(celdaModulo);
            tabla.appendChild(fila);
        });

        previaWrap.style.display = '';

        if (masivo) {
            modulo.disabled = true;
            modulo.required = false;
            modulo.value = '';
            ayuda.innerHTML = 'Con varios archivos, el módulo de cada uno sale de su nombre. '
                            + 'Si quieres otro nombre de módulo, renombra el archivo antes de subirlo.';
            nota.textContent = archivos.length + ' archivos. Revisa que los módulos coincidan con los que ya existen '
                             + 'abajo: un nombre distinto crea un módulo nuevo en vez de actualizar el existente.';
        } else {
            modulo.disabled = false;
            modulo.required = true;
            ayuda.innerHTML = ayudaOriginal;
            nota.textContent = 'Se cargará con el módulo que escribas arriba.';
        }
    });

    // Si escribe el módulo después de elegir el archivo, refrescamos la previa.
    modulo.addEventListener('input', function () {
        const celda = tabla.querySelector('td.celda-modulo');
        if (celda && (input.files || []).length === 1) {
            celda.textContent = modulo.value || '—';
        }
    });
})();

// Confirmación antes de borrar un manual.
// Borrar es irreversible y Qdrant no tiene papelera, así que pedimos
// confirmación diciendo exactamente qué se va a eliminar. Un "¿estás seguro?"
// genérico se acepta sin leer; nombrar el manual y los fragmentos hace que
// la persona se detenga a mirar.
(function () {
    document.querySelectorAll('.form-borrar').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            var modulo     = form.dataset.modulo;
            var version    = form.dataset.version;
            var fragmentos = form.dataset.fragmentos;

            var mensaje = '¿Borrar el manual "' + modulo + '" en versión ' + version + '?\n\n'
                        + 'Se eliminarán ' + fragmentos + ' fragmentos de la base de conocimiento.\n'
                        + 'El asistente dejará de poder responder sobre este tema.\n\n'
                        + 'Esta acción no se puede deshacer.';

            // Si dice que no, cancelamos el envío del formulario.
            if (! window.confirm(mensaje)) {
                evento.preventDefault();
            }
        });
    });
})();
</script>
@endsection
