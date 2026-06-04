@extends('layouts.panel')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')
@section('page-subtitle', 'Análisis de usuarios que interactúan con el asistente IA')

@section('content')

<div class="kpi-grid">
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Total Usuarios</span><i class="bi bi-people kpi-icon"></i></div>
        <div class="kpi-value">{{ number_format($totalUsuarios) }}</div>
        <div class="kpi-sub">IDs de contacto únicos</div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Activos Hoy</span><i class="bi bi-calendar-day kpi-icon"></i></div>
        <div class="kpi-value">{{ number_format($usuariosHoy) }}</div>
        <div class="kpi-sub">Usuarios con actividad hoy</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Esta Semana</span><i class="bi bi-calendar-week kpi-icon"></i></div>
        <div class="kpi-value">{{ number_format($usuariosSemana) }}</div>
        <div class="kpi-sub">Usuarios activos en 7 días</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-header"><span class="kpi-label">Msgs por Usuario</span><i class="bi bi-chat-left-text kpi-icon"></i></div>
        <div class="kpi-value">{{ $avgMsgPorUsuario }}</div>
        <div class="kpi-sub">Promedio histórico</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div>
                <div class="chart-title"><i class="bi bi-graph-up"></i> Actividad Diaria</div>
                <div class="chart-subtitle">Usuarios activos por día durante los últimos 30 días</div>
            </div>
            <span class="chart-badge">30 días</span>
        </div>
        <div class="chart-wrap">
            <canvas id="chartActividad"></canvas>
        </div>
        <p class="chart-description">
            Muestra cuántos usuarios distintos enviaron al menos un mensaje cada día.
            Permite detectar tendencias de crecimiento o caídas en la adopción del asistente.
        </p>
    </div>
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div>
                <div class="chart-title"><i class="bi bi-trophy"></i> Más Activos esta Semana</div>
                <div class="chart-subtitle">Top 5 usuarios por cantidad de mensajes</div>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;margin-top:8px;">
            @php $maxAct = $activosEstaSemana->max('total') ?: 1; @endphp
            @forelse($activosEstaSemana as $u)
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-size:11px;color:var(--text-muted);width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $u->id_usuario }}">
                        ID: {{ $u->id_usuario }}
                    </span>
                    <div style="flex:1;height:7px;background:rgba(59,130,246,0.1);border-radius:8px;overflow:hidden;">
                        <div style="width:{{ ($u->total / $maxAct) * 100 }}%;height:100%;background:var(--accent);border-radius:8px;"></div>
                    </div>
                    <span style="font-size:12px;font-weight:700;color:var(--accent-light);min-width:28px;text-align:right;">{{ $u->total }}</span>
                </div>
            @empty
                <p style="color:var(--text-muted);font-size:12px;">Sin actividad esta semana</p>
            @endforelse
        </div>
        <p class="chart-description">
            Los usuarios que más consultas han realizado al asistente IA en los últimos 7 días.
        </p>
    </div>
</div>

<div style="margin-top:22px;" class="table-card">
    <div class="table-card-header">
        <div class="table-title"><i class="bi bi-person-lines-fill"></i> Ranking de Usuarios</div>
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
    <div style="padding:16px 22px; border-top: 1px solid var(--border);">
        {{ $topUsuarios->links() }}
    </div>
</div>

<script>
Chart.defaults.color = '#7a9abf';
Chart.defaults.font.family = 'Inter';

new Chart(document.getElementById('chartActividad'), {
    type: 'line',
    data: {
        labels: @json($nuevosPorDia->pluck('fecha')),
        datasets: [{
            label: 'Usuarios activos',
            data: @json($nuevosPorDia->pluck('nuevos')),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.06)',
            fill: true,
            tension: 0.3,
            pointRadius: 2,
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    title: function(items) { return 'Fecha: ' + items[0].label; },
                    label: function(item) { return item.parsed.y + ' usuarios activos este día'; }
                }
            }
        },
        scales: {
            x: { grid: { color: 'rgba(70,130,220,0.08)' }, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
            y: {
                grid: { color: 'rgba(70,130,220,0.08)' },
                beginAtZero: true,
                ticks: { font: { size: 10 } },
                title: { display: true, text: 'Usuarios', font: { size: 10 }, color: '#7a9abf' }
            }
        }
    }
});
</script>
@endsection
