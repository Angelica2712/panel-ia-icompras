@extends('layouts.panel')

@section('title', 'Conversaciones')
@section('page-title', 'Conversaciones')
@section('page-subtitle', 'Historial completo de interacciones del asistente')

@section('content')

<div class="kpi-grid">
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Total Registros</span><i class="bi bi-journal-text kpi-icon"></i></div>
        <div class="kpi-value">{{ number_format($total) }}</div>
        <div class="kpi-sub">Conversaciones históricas</div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Hoy</span><i class="bi bi-calendar-day kpi-icon"></i></div>
        <div class="kpi-value">{{ number_format($hoy) }}</div>
        <div class="kpi-sub">Mensajes en las últimas 24h</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Long. Promedio Pregunta</span><i class="bi bi-pencil kpi-icon"></i></div>
        <div class="kpi-value" style="font-size:22px;">{{ number_format($avgPregunta) }}<small style="font-size:12px"> chars</small></div>
        <div class="kpi-sub">Caracteres por pregunta</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-header"><span class="kpi-label">Long. Promedio Respuesta</span><i class="bi bi-text-paragraph kpi-icon"></i></div>
        <div class="kpi-value" style="font-size:22px;">{{ number_format($avgRespuesta) }}<small style="font-size:12px"> chars</small></div>
        <div class="kpi-sub">Caracteres por respuesta</div>
    </div>
</div>

<div class="table-card">
    <div class="table-card-header" style="flex-wrap:wrap;gap:12px;">
        <div class="table-title"><i class="bi bi-list-ul"></i> Registro de Conversaciones</div>

        {{-- Filtro de fechas + botón descarga --}}
        <form method="GET" action="{{ route('conversaciones') }}"
              id="formFiltro" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">

            <input type="date" name="fecha_desde"
                   value="{{ request('fecha_desde') }}"
                   title="Desde"
                   style="background:rgba(11,30,61,.6);border:1px solid var(--border);border-radius:8px;
                          color:var(--text-primary);font-family:inherit;font-size:12px;padding:7px 10px;outline:none;">

            <span style="color:var(--text-muted);font-size:12px;">—</span>

            <input type="date" name="fecha_hasta"
                   value="{{ request('fecha_hasta') }}"
                   title="Hasta"
                   style="background:rgba(11,30,61,.6);border:1px solid var(--border);border-radius:8px;
                          color:var(--text-primary);font-family:inherit;font-size:12px;padding:7px 10px;outline:none;">

            <button type="submit" class="btn-filter btn-filter-primary" style="padding:7px 14px;font-size:12px;">
                <i class="bi bi-funnel"></i> Filtrar
            </button>

            @if(request('fecha_desde') || request('fecha_hasta'))
                <a href="{{ route('conversaciones') }}" class="btn-filter" style="padding:7px 14px;font-size:12px;">
                    <i class="bi bi-x-lg"></i> Limpiar
                </a>
            @endif
        </form>

        {{-- Botón descargar CSV: pasa los mismos filtros activos --}}
        <a href="{{ route('conversaciones.descargar', array_filter(['fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta')])) }}"
           class="btn-filter btn-filter-primary"
           style="padding:7px 14px;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;
                  background:rgba(16,185,129,.14);border-color:rgba(16,185,129,.3);color:#6ee7b7;"
           title="{{ request('fecha_desde') ? 'Descarga el período filtrado' : 'Descarga todo el historial' }}">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            Descargar CSV
            @if(request('fecha_desde'))
                <span style="font-size:10px;opacity:.8;">(filtrado)</span>
            @else
                <span style="font-size:10px;opacity:.8;">(todo)</span>
            @endif
        </a>

        <span class="chart-badge">{{ number_format($total) }} registros</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha / Hora</th>
                    <th>Usuario</th>
                    <th>Farmacia</th>
                    <th>Pregunta</th>
                    <th>Respuesta</th>
                    <th>Latencia</th>
                    <th>Versión</th>
                </tr>
            </thead>
            <tbody>
                @foreach($conversaciones as $c)
                    @php
                        $lat = $c->latencia_ms;
                        $latClass = $lat < 2000 ? 'pill-green' : ($lat < 5000 ? 'pill-orange' : 'pill-red');
                    @endphp
                    <tr>
                        <td class="td-muted">{{ $c->id }}</td>
                        <td class="td-muted" style="white-space:nowrap;">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/y H:i') }}</td>
                        <td class="td-muted">{{ $c->id_usuario }}</td>
                        <td class="td-truncate" title="{{ $c->nombre_farmacia }}">{{ $c->nombre_farmacia ?? '—' }}</td>
                        <td class="td-truncate" title="{{ $c->pregunta }}" style="max-width:220px;">{{ Str::limit($c->pregunta, 50) }}</td>
                        <td class="td-truncate td-muted" title="{{ $c->respuesta }}" style="max-width:220px;">{{ Str::limit($c->respuesta, 50) }}</td>
                        <td><span class="pill {{ $latClass }}">{{ number_format($lat) }} ms</span></td>
                        <td class="td-muted">{{ $c->version_icompras ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:16px 22px; border-top: 1px solid var(--border);">
        {{ $conversaciones->links() }}
    </div>
</div>

@endsection
