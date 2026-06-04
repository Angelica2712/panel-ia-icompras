@extends('layouts.panel')

@section('title', 'Farmacias')
@section('page-title', 'Farmacias')
@section('page-subtitle', 'Estadísticas de uso por farmacia')

@section('content')

<div class="kpi-grid-3" style="margin-bottom:22px;">
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Farmacias Activas</span><i class="bi bi-shop kpi-icon"></i></div>
        <div class="kpi-value">{{ number_format($totalFarmacias) }}</div>
        <div class="kpi-sub">Con al menos 1 conversación</div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Activas esta semana</span><i class="bi bi-calendar-week kpi-icon"></i></div>
        <div class="kpi-value">{{ $actividadReciente->count() }}</div>
        <div class="kpi-sub">Con actividad en los últimos 7 días</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Top Farmacia</span><i class="bi bi-trophy kpi-icon"></i></div>
        <div class="kpi-value" style="font-size:15px; padding-top:8px;">{{ Str::limit($farmacias->first()?->nombre_farmacia ?? '—', 22) }}</div>
        <div class="kpi-sub">{{ number_format($farmacias->first()?->total_mensajes ?? 0) }} mensajes</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div>
                <div class="chart-title"><i class="bi bi-bar-chart-line"></i> Actividad últimos 7 días</div>
                <div class="chart-subtitle">Top farmacias por cantidad de mensajes enviados esta semana</div>
            </div>
        </div>
        <div class="chart-wrap">
            <canvas id="chartFarmacias"></canvas>
        </div>
        <p class="chart-description">
            Comparación horizontal de las farmacias con mayor cantidad de consultas al asistente IA durante la última semana.
        </p>
    </div>
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div>
                <div class="chart-title"><i class="bi bi-pie-chart"></i> Distribución</div>
                <div class="chart-subtitle">Proporción de mensajes por farmacia (top 6)</div>
            </div>
        </div>
        <div class="chart-wrap">
            <canvas id="chartDona"></canvas>
        </div>
        <p class="chart-description">
            Porcentaje que representa cada farmacia del total de mensajes procesados.
        </p>
    </div>
</div>

<div style="margin-top:22px;" class="table-card">
    <div class="table-card-header">
        <div class="table-title"><i class="bi bi-table"></i> Detalle por Farmacia</div>
        <span class="chart-badge">{{ number_format($totalFarmacias) }} farmacias</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Farmacia</th>
                    <th>ID</th>
                    <th>Mensajes</th>
                    <th>Usuarios</th>
                    <th>Sesiones</th>
                    <th>Latencia Prom.</th>
                    <th>Último Mensaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($farmacias as $i => $f)
                    <tr>
                        <td class="td-muted">{{ $i + 1 }}</td>
                        <td><strong>{{ $f->nombre_farmacia }}</strong></td>
                        <td class="td-muted">{{ $f->id_farmacia }}</td>
                        <td><span class="pill pill-blue">{{ number_format($f->total_mensajes) }}</span></td>
                        <td class="td-muted">{{ $f->usuarios_unicos }}</td>
                        <td class="td-muted">{{ $f->sesiones }}</td>
                        <td>
                            @php $lat = round($f->latencia_promedio); @endphp
                            <span class="pill {{ $lat < 2000 ? 'pill-green' : ($lat < 5000 ? 'pill-orange' : 'pill-red') }}">
                                {{ number_format($lat) }} ms
                            </span>
                        </td>
                        <td class="td-muted">{{ \Carbon\Carbon::parse($f->ultimo_mensaje)->format('d/m/y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:16px 22px; border-top: 1px solid var(--border);">
        {{ $farmacias->links() }}
    </div>
</div>

<script>
Chart.defaults.color = '#7a9abf';
Chart.defaults.font.family = 'Inter';

var farmLabels = @json($actividadReciente->pluck('nombre_farmacia')->map(fn($n) => Str::limit($n, 25)));
var farmData   = @json($actividadReciente->pluck('total'));

new Chart(document.getElementById('chartFarmacias'), {
    type: 'bar',
    data: {
        labels: farmLabels,
        datasets: [{
            label: 'Mensajes',
            data: farmData,
            backgroundColor: 'rgba(59, 130, 246, 0.6)',
            borderColor: '#3b82f6',
            borderWidth: 1,
            borderRadius: 3
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(item) { return item.parsed.x + ' mensajes esta semana'; }
                }
            }
        },
        scales: {
            x: { grid: { color: 'rgba(70,130,220,0.08)' }, beginAtZero: true, ticks: { font: { size: 10 } } },
            y: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
    }
});

var donaLabels = @json($farmacias->take(6)->pluck('nombre_farmacia')->map(fn($n) => Str::limit($n, 20)));
var donaData   = @json($farmacias->take(6)->pluck('total_mensajes'));
var colors     = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#f97316'];

new Chart(document.getElementById('chartDona'), {
    type: 'doughnut',
    data: {
        labels: donaLabels,
        datasets: [{
            data: donaData,
            backgroundColor: colors,
            borderColor: '#0b1e3d',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8, boxWidth: 10 } },
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
</script>
@endsection
