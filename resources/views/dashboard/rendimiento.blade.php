@extends('layouts.panel')

@section('title', 'Rendimiento')
@section('page-title', 'Rendimiento')
@section('page-subtitle', 'Análisis de latencia y velocidad de respuesta del asistente IA')

@section('content')

<div class="kpi-grid">
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Latencia Promedio</span><i class="bi bi-lightning kpi-icon"></i></div>
        <div class="kpi-value" style="font-size:24px;">{{ number_format($latPromedio) }}<small style="font-size:13px;font-weight:500"> ms</small></div>
        <div class="kpi-sub">Tiempo de respuesta promedio</div>
    </div>
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Latencia Mínima</span><i class="bi bi-arrow-down-circle kpi-icon"></i></div>
        <div class="kpi-value" style="font-size:24px;">{{ number_format($latMin) }}<small style="font-size:13px;font-weight:500"> ms</small></div>
        <div class="kpi-sub">Respuesta más rápida registrada</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Latencia Máxima</span><i class="bi bi-arrow-up-circle kpi-icon"></i></div>
        <div class="kpi-value" style="font-size:24px;">{{ number_format($latMax) }}<small style="font-size:13px;font-weight:500"> ms</small></div>
        <div class="kpi-sub">Respuesta más lenta registrada</div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-header"><span class="kpi-label">Respuestas Rápidas</span><i class="bi bi-check-circle kpi-icon"></i></div>
        @php $total = array_sum($rangos); $rapidas = $rangos['Rápida (<1s)'] ?? 0; @endphp
        <div class="kpi-value">{{ $total > 0 ? round(($rapidas / $total) * 100) : 0 }}<small style="font-size:15px;font-weight:500">%</small></div>
        <div class="kpi-sub">Respuestas bajo 1 segundo</div>
    </div>
</div>

<div class="charts-grid-3">
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div>
                <div class="chart-title"><i class="bi bi-pie-chart"></i> Distribución de Latencia</div>
                <div class="chart-subtitle">Clasificación por rangos de velocidad</div>
            </div>
        </div>
        <div class="chart-wrap-sm">
            <canvas id="chartRangos"></canvas>
        </div>
        <p class="chart-description">
            Divide las respuestas del asistente en 4 categorías según su tiempo de respuesta:
            rápida (&lt;1s), normal (1-3s), lenta (3-5s) y muy lenta (&gt;5s).
        </p>
    </div>
    <div class="chart-card" style="margin-bottom:0; grid-column: span 2;">
        <div class="chart-card-header">
            <div>
                <div class="chart-title"><i class="bi bi-clock-history"></i> Latencia por Hora del Día</div>
                <div class="chart-subtitle">Promedio histórico de velocidad de respuesta en cada franja horaria</div>
            </div>
        </div>
        <div class="chart-wrap-sm">
            <canvas id="chartLatHora"></canvas>
        </div>
        <p class="chart-description">
            Permite detectar si existen horarios donde el asistente tarda más en responder, por ejemplo por mayor carga de consultas simultáneas.
        </p>
    </div>
</div>

<div style="margin-top:22px;" class="chart-card">
    <div class="chart-card-header">
        <div>
            <div class="chart-title"><i class="bi bi-graph-up-arrow"></i> Evolución de Latencia</div>
            <div class="chart-subtitle">Últimos 30 días — promedio y máximo diario</div>
        </div>
        <span class="chart-badge">30 días</span>
    </div>
    <div class="chart-wrap">
        <canvas id="chartLatDia"></canvas>
    </div>
    <p class="chart-description">
        La línea verde representa el tiempo promedio de respuesta por día, mientras la línea roja punteada muestra el pico máximo alcanzado.
        Si ambas líneas se separan mucho, puede indicar casos atípicos que afectan la experiencia.
    </p>
</div>

<div class="table-card">
    <div class="table-card-header">
        <div class="table-title"><i class="bi bi-exclamation-triangle"></i> Respuestas Más Lentas</div>
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
Chart.defaults.color = '#7a9abf';
Chart.defaults.font.family = 'Inter';

// Distribucion por rangos de latencia
new Chart(document.getElementById('chartRangos'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($rangos)),
        datasets: [{
            data: @json(array_values($rangos)),
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
            borderColor: '#0b1e3d',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } },
            tooltip: {
                callbacks: {
                    label: function(item) {
                        var total = item.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                        var pct = total > 0 ? Math.round((item.parsed / total) * 100) : 0;
                        return item.label + ': ' + item.parsed + ' (' + pct + '%)';
                    }
                }
            }
        },
        cutout: '58%'
    }
});

// Latencia promedio por hora del dia
new Chart(document.getElementById('chartLatHora'), {
    type: 'bar',
    data: {
        labels: Array.from({length:24}, function(_,i) { return i + ':00'; }),
        datasets: [{
            label: 'Promedio (ms)',
            data: @json($horasLatencia),
            backgroundColor: 'rgba(139, 92, 246, 0.5)',
            borderColor: '#8b5cf6',
            borderWidth: 1,
            borderRadius: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    title: function(items) { return 'Hora: ' + items[0].label; },
                    label: function(item) { return 'Latencia promedio: ' + Math.round(item.parsed.y) + ' ms'; }
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 9 } } },
            y: {
                grid: { color: 'rgba(70,130,220,0.08)' },
                beginAtZero: true,
                ticks: { font: { size: 9 } },
                title: { display: true, text: 'ms', font: { size: 9 }, color: '#7a9abf' }
            }
        }
    }
});

// Evolucion de latencia diaria
new Chart(document.getElementById('chartLatDia'), {
    type: 'line',
    data: {
        labels: @json($latenciaDia->pluck('fecha')),
        datasets: [
            {
                label: 'Promedio',
                data: @json($latenciaDia->pluck('promedio')->map(fn($v) => round($v))),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.06)',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                borderWidth: 2
            },
            {
                label: 'Máximo',
                data: @json($latenciaDia->pluck('maximo')),
                borderColor: '#ef4444',
                backgroundColor: 'transparent',
                fill: false,
                tension: 0.3,
                pointRadius: 2,
                borderWidth: 1.5,
                borderDash: [5, 3]
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } },
            tooltip: {
                callbacks: {
                    title: function(items) { return 'Fecha: ' + items[0].label; },
                    label: function(item) { return item.dataset.label + ': ' + item.parsed.y + ' ms'; }
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 10 } },
            y: {
                grid: { color: 'rgba(70,130,220,0.08)' },
                beginAtZero: true,
                ticks: { font: { size: 10 } },
                title: { display: true, text: 'Milisegundos', font: { size: 10 }, color: '#7a9abf' }
            }
        }
    }
});
</script>
@endsection
