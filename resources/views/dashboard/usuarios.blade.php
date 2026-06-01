@extends('layouts.panel')

@section('title', 'Usuarios')
@section('page-title', '👥 Usuarios')
@section('page-subtitle', 'Análisis de usuarios que interactúan con el asistente IA')

@section('content')

<div class="kpi-grid">
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Total Usuarios</span><span class="kpi-icon">👥</span></div>
        <div class="kpi-value">{{ number_format($totalUsuarios) }}</div>
        <div class="kpi-sub">IDs de contacto únicos</div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Activos Hoy</span><span class="kpi-icon">📅</span></div>
        <div class="kpi-value">{{ number_format($usuariosHoy) }}</div>
        <div class="kpi-sub">Usuarios con actividad hoy</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Esta Semana</span><span class="kpi-icon">📆</span></div>
        <div class="kpi-value">{{ number_format($usuariosSemana) }}</div>
        <div class="kpi-sub">Usuarios activos en 7 días</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-header"><span class="kpi-label">Msgs por Usuario</span><span class="kpi-icon">💬</span></div>
        <div class="kpi-value">{{ $avgMsgPorUsuario }}</div>
        <div class="kpi-sub">Promedio histórico</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div><div class="chart-title">Actividad Diaria</div><div class="chart-subtitle">Últimos 30 días</div></div>
            <span class="chart-badge">30 días</span>
        </div>
        <div class="chart-wrap">
            <canvas id="chartActividad"></canvas>
        </div>
    </div>
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div><div class="chart-title">Más Activos esta Semana</div><div class="chart-subtitle">Top 5 por mensajes</div></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;margin-top:8px;">
            @php $maxAct = $activosEstaSemana->max('total') ?: 1; @endphp
            @forelse($activosEstaSemana as $u)
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-size:11px;color:var(--text-muted);width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $u->id_usuario }}">
                        ID: {{ $u->id_usuario }}
                    </span>
                    <div style="flex:1;height:8px;background:rgba(56,120,220,0.12);border-radius:10px;overflow:hidden;">
                        <div style="width:{{ ($u->total / $maxAct) * 100 }}%;height:100%;background:linear-gradient(90deg,#1a6ef7,#3b8aff);border-radius:10px;"></div>
                    </div>
                    <span style="font-size:12px;font-weight:700;color:var(--blue-2);min-width:28px;text-align:right;">{{ $u->total }}</span>
                </div>
            @empty
                <p style="color:var(--text-muted);font-size:12px;">Sin actividad esta semana</p>
            @endforelse
        </div>
    </div>
</div>

<div style="margin-top:24px;" class="table-card">
    <div class="table-card-header">
        <div class="table-title">📋 Ranking de Usuarios</div>
        <span class="chart-badge">{{ number_format($totalUsuarios) }} usuarios</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID Usuario</th>
                    <th>Mensajes</th>
                    <th>Sesiones</th>
                    <th>Días Activo</th>
                    <th>Latencia Prom.</th>
                    <th>Primera Actividad</th>
                    <th>Última Actividad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topUsuarios as $i => $u)
                    <tr>
                        <td class="td-muted">{{ $i + 1 }}</td>
                        <td><strong>{{ $u->id_usuario }}</strong></td>
                        <td><span class="pill pill-blue">{{ number_format($u->total_mensajes) }}</span></td>
                        <td class="td-muted">{{ $u->sesiones }}</td>
                        <td class="td-muted">{{ $u->dias_activo }} días</td>
                        <td>
                            @php $lat = round($u->latencia_promedio); @endphp
                            <span class="pill {{ $lat < 2000 ? 'pill-green' : ($lat < 5000 ? 'pill-orange' : 'pill-red') }}">
                                {{ number_format($lat) }} ms
                            </span>
                        </td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($u->primera_actividad)->format('d/m/y') }}</td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($u->ultima_actividad)->format('d/m/y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:16px 24px; border-top: 1px solid var(--border);">
        {{ $topUsuarios->links() }}
    </div>
</div>

<script>
Chart.defaults.color = '#7fa4cc';
Chart.defaults.font.family = 'Inter';

new Chart(document.getElementById('chartActividad'), {
    type: 'line',
    data: {
        labels: @json($nuevosPorDia->pluck('fecha')),
        datasets: [{
            label: 'Usuarios activos',
            data: @json($nuevosPorDia->pluck('nuevos')),
            borderColor: '#3b8aff',
            backgroundColor: 'rgba(59,138,255,0.08)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(56,120,220,0.1)' }, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
            y: { grid: { color: 'rgba(56,120,220,0.1)' }, beginAtZero: true, ticks: { font: { size: 10 } } }
        }
    }
});
</script>
@endsection
