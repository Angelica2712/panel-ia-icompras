@extends('layouts.panel')

@section('title', 'Conversaciones')
@section('page-title', '💬 Conversaciones')
@section('page-subtitle', 'Historial completo de interacciones con el asistente IA')

@section('content')

<div class="kpi-grid">
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Total Registros</span><span class="kpi-icon">📋</span></div>
        <div class="kpi-value">{{ number_format($total) }}</div>
        <div class="kpi-sub">Conversaciones históricas</div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Hoy</span><span class="kpi-icon">📅</span></div>
        <div class="kpi-value">{{ number_format($hoy) }}</div>
        <div class="kpi-sub">Mensajes en las últimas 24h</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Long. Promedio Pregunta</span><span class="kpi-icon">✏️</span></div>
        <div class="kpi-value" style="font-size:24px;">{{ number_format($avgPregunta) }}<small style="font-size:12px"> chars</small></div>
        <div class="kpi-sub">Caracteres por pregunta</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-header"><span class="kpi-label">Long. Promedio Respuesta</span><span class="kpi-icon">🤖</span></div>
        <div class="kpi-value" style="font-size:24px;">{{ number_format($avgRespuesta) }}<small style="font-size:12px"> chars</small></div>
        <div class="kpi-sub">Caracteres por respuesta IA</div>
    </div>
</div>

<div class="table-card">
    <div class="table-card-header">
        <div class="table-title">📋 Registro de Conversaciones</div>
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
                    <th>Respuesta IA</th>
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
    <div style="padding:16px 24px; border-top: 1px solid var(--border);">
        {{ $conversaciones->links() }}
    </div>
</div>

@endsection
