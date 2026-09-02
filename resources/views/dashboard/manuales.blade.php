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
        <div>
            {{ session('ok') }}
            @if(session('resumen'))
                <div class="resumen-envio">
                    <span>Archivo: <strong>{{ session('resumen')['archivo'] }}</strong></span>
                    <span>Módulo: <strong>{{ session('resumen')['modulo'] }}</strong></span>
                    <span>Versión: <strong>{{ session('resumen')['version'] }}</strong></span>
                    <span>Tamaño: <strong>{{ session('resumen')['kb'] }} KB</strong></span>
                </div>
            @endif
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
            <div class="chart-title"><i class="bi bi-file-earmark-arrow-up"></i> Subir manual</div>
            <div class="chart-subtitle">Archivo Markdown (.md) — máximo {{ $maxKb / 1024 }} MB</div>
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

        {{-- ---------- Campo 1: archivo .md ---------- --}}
        <div class="campo">
            <label for="archivo">
                <i class="bi bi-filetype-md"></i> Archivo del manual
            </label>
            <input type="file"
                   name="archivo"
                   id="archivo"
                   accept=".md,text/markdown"
                   class="{{ $errors->has('archivo') ? 'con-error' : '' }}"
                   required>
            <span class="ayuda">
                Solo archivos con extensión <strong>.md</strong>. El contenido se lee como
                texto y se envía a n8n; el archivo no se guarda en el servidor.
            </span>
            @error('archivo')
                <span class="error-msg"><i class="bi bi-x-circle"></i> {{ $message }}</span>
            @enderror
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
                {{-- Campo de escritura libre: se escribe el módulo a mano. --}}
                <input type="text"
                       name="modulo"
                       id="modulo"
                       value="{{ old('modulo') }}"
                       placeholder="Ej: catalogo"
                       maxlength="60"
                       autocomplete="off"
                       class="{{ $errors->has('modulo') ? 'con-error' : '' }}"
                       required>
                <span class="ayuda">
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
