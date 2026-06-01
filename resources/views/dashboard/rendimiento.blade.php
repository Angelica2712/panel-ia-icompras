@extends('layouts.panel')

@section('title', 'Rendimiento')
@section('page-title', '⚡ Rendimiento')
@section('page-subtitle', 'Análisis de latencia y velocidad de respuesta del asistente IA')


@section('content')

<div class="kpi-grid">
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Latencia Promedio</span><span class="kpi-icon">⚡</span></div>
        <div class="kpi-value" style="font-size:26px;">{{ number_format($latPromedio) }}<small style="font-size:13px;font-weight:500"> ms</small></div>
        <div class="kpi-sub">Tiempo de respuesta promedio</div>
    </div>
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Latencia Mínima</span><span class="kpi-icon">🚀</span></div>
        <div class="kpi-value" style="font-size:26px;">{{ number_format($latMin) }}<small style="font-size:13px;font-weight:500"> ms</small></div>
        <div class="kpi-sub">Respuesta más rápida registrada</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Latencia Máxima</span><span class="kpi-icon">🐢</span></div>
        <div class="kpi-value" style="font-size:26px;">{{ number_format($latMax) }}<small style="font-size:13px;font-weight:500"> ms</small></div>
        <div class="kpi-sub">Respuesta más lenta registrada</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-header"><span class="kpi-label">Respuestas Rápidas</span><span class="kpi-icon">✅</span></div>
        @php $total = array_sum($rangos); $rapidas = $rangos['Rápida (<1s)'] ?? 0; @endphp
        <div class="kpi-value">{{ $total > 0 ? round(($rapidas / $total) * 100) : 0 }}<small style="font-size:16px;font-weight:500">%</small></div>
        <div class="kpi-sub">Respuestas bajo 1 segundo</div>
    </div>
</div>

<div class="charts-grid-3">
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div><div class="chart-title">Distribución de Latencia</div><div class="chart-subtitle">Por rangos de velocidad</div></div>
        </div>
        <div class="chart-wrap-sm">
            <canvas id="chartRangos"></canvas>
        </div>
    </div>
    <div class="chart-card" style="margin-bottom:0; grid-column: span 2;">
        <div class="chart-card-header">
            <div><div class="chart-title">Latencia por Hora del Día</div><div class="chart-subtitle">Promedio histórico</div></div>
        </div>
        <div class="chart-wrap-sm">
            <canvas id="chartLatHora"></canvas>
        </div>
    </div>
</div>

<div style="margin-top:24px;" class="chart-card">
    <div class="chart-card-header">
        <div><div class="chart-title">Evolución de Latencia</div><div class="chart-subtitle">Últimos 30 días — promedio y máximo</div></div>
        <span class="chart-badge">30 días</span>
    </div>
    <div class="chart-wrap">
        <canvas id="chartLatDia"></canvas>
    </div>
</div>

<div class="table-card">
    <div class="table-card-header">
        <div class="table-title">🐢 Respuestas Más Lentas</div>
        <span class="chart-badge">Top 10</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Latencia</th>
                    <th>Fecha</th>
                    <th>Farmacia</th>
                    <th>Pregunta</th>
                    <th>Versión</th>
                </tr>
            </thead>
            <tbody>
                @foreach($masLentas as $i => $r)
                    <tr>
                        <td class="td-muted">{{ $i + 1 }}</td>
                        <td><span class="pill pill-red">{{ number_format($r->latencia_ms) }} ms</span></td>
                        <td class="td-muted" style="white-space:nowrap;">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/y H:i') }}</td>
                        <td class="td-truncate">{{ $r->nombre_farmacia ?? '—' }}</td>
                        <td class="td-truncate" style="max-width:280px;" title="{{ $r->pregunta }}">{{ Str::limit($r->pregunta, 60) }}</td>
                        <td class="td-muted">{{ $r->version_icompras ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
Chart.defaults.color = '#7fa4cc';
Chart.defaults.font.family = 'Inter';

// Rangos
new Chart(document.getElementById('chartRangos'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($rangos)),
        datasets: [{ data: @json(array_values($rangos)), backgroundColor: ['rgba(0,212,160,0.8)','rgba(59,138,255,0.8)','rgba(255,140,66,0.8)','rgba(255,78,106,0.8)'], borderColor: ['#00d4a0','#3b8aff','#ff8c42','#ff4e6a'], borderWidth: 2 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } } }, cutout: '60%' }
});

// Latencia por hora
new Chart(document.getElementById('chartLatHora'), {
    type: 'bar',
    data: {
        labels: Array.from({length:24}, (_,i) => i+'h'),
        datasets: [{ label: 'Promedio (ms)', data: @json($horasLatencia), backgroundColor: 'rgba(139,92,246,0.6)', borderColor: '#8b5cf6', borderWidth: 1, borderRadius: 3 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 9 } } }, y: { grid: { color: 'rgba(56,120,220,0.1)' }, beginAtZero: true, ticks: { font: { size: 9 } } } } }
});

// Latencia por día
new Chart(document.getElementById('chartLatDia'), {
    type: 'line',
    data: {
        labels: @json($latenciaDia->pluck('fecha')),
        datasets: [
            { label: 'Promedio', data: @json($latenciaDia->pluck('promedio')->map(fn($v)=>round($v))), borderColor: '#00d4a0', backgroundColor: 'rgba(0,212,160,0.07)', fill: true, tension: 0.4, pointRadius: 2, borderWidth: 2 },
            { label: 'Máximo',   data: @json($latenciaDia->pluck('maximo')),  borderColor: '#ff4e6a', backgroundColor: 'transparent', fill: false, tension: 0.4, pointRadius: 2, borderWidth: 1.5, borderDash: [4,3] }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 10 } }, y: { grid: { color: 'rgba(56,120,220,0.1)' }, beginAtZero: true, ticks: { font: { size: 10 } } } } }
});
</script>
@endsection
