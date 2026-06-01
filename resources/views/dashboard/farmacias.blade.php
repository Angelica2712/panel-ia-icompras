@extends('layouts.panel')

@section('title', 'Farmacias')
@section('page-title', '🏪 Farmacias')
@section('page-subtitle', 'Estadísticas de uso por farmacia')

@section('content')

<div class="kpi-grid-3" style="margin-bottom:24px;">
    <div class="kpi-card blue">
        <div class="kpi-header"><span class="kpi-label">Farmacias Activas</span><span class="kpi-icon">🏪</span></div>
        <div class="kpi-value">{{ number_format($totalFarmacias) }}</div>
        <div class="kpi-sub">Con al menos 1 conversación</div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-header"><span class="kpi-label">Activas esta semana</span><span class="kpi-icon">📆</span></div>
        <div class="kpi-value">{{ $actividadReciente->count() }}</div>
        <div class="kpi-sub">Con actividad en los últimos 7 días</div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-header"><span class="kpi-label">Top Farmacia</span><span class="kpi-icon">🥇</span></div>
        <div class="kpi-value" style="font-size:16px; padding-top:8px;">{{ Str::limit($farmacias->first()?->nombre_farmacia ?? '—', 22) }}</div>
        <div class="kpi-sub">{{ number_format($farmacias->first()?->total_mensajes ?? 0) }} mensajes</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div><div class="chart-title">Actividad últimos 7 días</div><div class="chart-subtitle">Top farmacias por mensajes</div></div>
        </div>
        <div class="chart-wrap">
            <canvas id="chartFarmacias"></canvas>
        </div>
    </div>
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-card-header">
            <div><div class="chart-title">Distribución</div><div class="chart-subtitle">Por volumen total</div></div>
        </div>
        <div class="chart-wrap">
            <canvas id="chartDona"></canvas>
        </div>
    </div>
</div>

<div style="margin-top:24px;" class="table-card">
    <div class="table-card-header">
        <div class="table-title">📋 Detalle por Farmacia</div>
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
    <div style="padding:16px 24px; border-top: 1px solid var(--border);">
        {{ $farmacias->links() }}
    </div>
</div>

<script>
Chart.defaults.color = '#7fa4cc';
Chart.defaults.font.family = 'Inter';

const farmLabels = @json($actividadReciente->pluck('nombre_farmacia')->map(fn($n) => Str::limit($n, 25)));
const farmData   = @json($actividadReciente->pluck('total'));

new Chart(document.getElementById('chartFarmacias'), {
    type: 'bar',
    data: {
        labels: farmLabels,
        datasets: [{ label: 'Mensajes', data: farmData, backgroundColor: 'rgba(59,138,255,0.7)', borderColor: '#3b8aff', borderWidth: 1, borderRadius: 4 }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(56,120,220,0.1)' }, beginAtZero: true, ticks: { font: { size: 10 } } },
            y: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
    }
});

const donaLabels = @json($farmacias->take(6)->pluck('nombre_farmacia')->map(fn($n) => Str::limit($n, 20)));
const donaData   = @json($farmacias->take(6)->pluck('total_mensajes'));
const colors     = ['#3b8aff','#00d4a0','#ff8c42','#8b5cf6','#ff4e6a','#fbbf24'];

new Chart(document.getElementById('chartDona'), {
    type: 'doughnut',
    data: {
        labels: donaLabels,
        datasets: [{ data: donaData, backgroundColor: colors.map(c => c + 'cc'), borderColor: colors, borderWidth: 2 }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8, boxWidth: 10 } } },
        cutout: '60%'
    }
});
</script>
@endsection
