<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard IA iCompras360 — Estadísticas en tiempo real">
    <title> IA — iCompras360</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-deep:       #0d2244;
            --bg-dark:       #102a54;
            --bg-card:       rgba(18, 45, 95, 0.72);
            --bg-card-hover: rgba(24, 57, 118, 0.90);
            --border:        rgba(80, 148, 255, 0.22);
            --blue-1:        #2478ff;
            --blue-2:        #5aa0ff;
            --blue-glow:     rgba(36, 120, 255, 0.35);
            --green:         #00e6b0;
            --orange:        #ff9f55;
            --purple:        #a374ff;
            --red:           #ff6080;
            --text-primary:  #eef4ff;
            --text-muted:    #7fa4cc;
            --text-label:    #a4bfdd;
            --sidebar-w:     240px;
            --topbar-h:      70px;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── OVERLAY ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(4,13,26,0.75);
            backdrop-filter: blur(4px);
            z-index: 90;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active { opacity: 1; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            background: rgba(10, 28, 70, 0.97);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Botón cerrar dentro del sidebar */
        .sidebar-close {
            display: none;
            position: absolute;
            top: 16px; right: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            border-radius: 8px;
            width: 32px; height: 32px;
            align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 18px;
            color: var(--text-muted);
            transition: all 0.2s;
            line-height: 1;
        }
        .sidebar-close:hover { background: rgba(255,255,255,0.12); color: var(--text-primary); }

        .sidebar-logo {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            padding-right: 50px; /* espacio para el botón close */
        }

        .sidebar-logo img {
            width: 40px; height: 40px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 3px;
        }

        .sidebar-logo-text {
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo-text strong {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .sidebar-logo-text span {
            font-size: 10px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 8px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(26, 110, 247, 0.1);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: rgba(26, 110, 247, 0.15);
            color: var(--blue-2);
            border: 1px solid rgba(26, 110, 247, 0.2);
        }

        .nav-icon { font-size: 16px; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 8px;
            border-radius: 8px;
            margin-bottom: 8px;
            min-width: 0;
        }

        .user-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--blue-1), #0a3db5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-details { min-width: 0; }
        .user-details strong {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        .user-details span {
            font-size: 10px;
            color: var(--text-muted);
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block;
        }

        .btn-logout {
            width: 100%;
            padding: 9px;
            background: rgba(255, 78, 106, 0.1);
            border: 1px solid rgba(255, 78, 106, 0.2);
            border-radius: 8px;
            color: var(--red);
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-logout:hover {
            background: rgba(255, 78, 106, 0.2);
        }

        /* ── MAIN CONTENT ── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
            background:
                radial-gradient(ellipse 80% 50% at 85% 5%, rgba(36, 120, 255, 0.13) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 10% 90%, rgba(0, 230, 176, 0.06) 0%, transparent 50%),
                var(--bg-deep);
        }

        /* ── TOPBAR ── */
        .topbar {
            padding: 16px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(10, 28, 68, 0.75);
            backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 12px;
            min-height: var(--topbar-h);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }
        .topbar-left h1 {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-left p {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Hamburger ── */
        .hamburger {
            display: none;
            align-items: center; justify-content: center;
            width: 38px; height: 38px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            color: var(--text-primary);
            font-size: 20px;
            line-height: 1;
        }
        .hamburger:hover { background: rgba(26,110,247,0.15); border-color: rgba(26,110,247,0.35); }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .live-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 212, 160, 0.1);
            border: 1px solid rgba(0, 212, 160, 0.25);
            border-radius: 100px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--green);
            white-space: nowrap;
        }

        .live-dot {
            width: 7px; height: 7px;
            background: var(--green);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--green);
            animation: pulse 2s infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }

        /* ── CONTENT ── */
        .content {
            padding: 28px 32px;
            flex: 1;
        }

        /* ── KPI CARDS ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px;
            backdrop-filter: blur(16px);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            border-radius: 14px 14px 0 0;
        }

        .kpi-card.blue::before   { background: linear-gradient(90deg, var(--blue-1), var(--blue-2)); }
        .kpi-card.green::before  { background: linear-gradient(90deg, #00d4a0, #00b589); }
        .kpi-card.orange::before { background: linear-gradient(90deg, #ff8c42, #ff6b1a); }
        .kpi-card.purple::before { background: linear-gradient(90deg, #8b5cf6, #6d28d9); }

        .kpi-card:hover {
            background: var(--bg-card-hover);
            border-color: rgba(56, 120, 220, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(4, 13, 26, 0.4);
        }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .kpi-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-icon {
            font-size: 20px;
            opacity: 0.8;
        }

        .kpi-value {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1;
            margin-bottom: 6px;
        }

        .kpi-card.blue   .kpi-value { color: var(--blue-2); }
        .kpi-card.green  .kpi-value { color: var(--green); }
        .kpi-card.orange .kpi-value { color: var(--orange); }
        .kpi-card.purple .kpi-value { color: var(--purple); }

        .kpi-sub {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ── KPI ROW 2 ── */
        .kpi-grid-2 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        /* ── CHARTS GRID ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .charts-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        /* ── CHART CARD ── */
        .chart-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
            backdrop-filter: blur(16px);
        }

        .chart-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .chart-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .chart-badge {
            font-size: 10px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 100px;
            background: rgba(26, 110, 247, 0.12);
            border: 1px solid rgba(26, 110, 247, 0.25);
            color: var(--blue-2);
        }

        .chart-wrap {
            position: relative;
            height: 220px;
        }

        .chart-wrap-sm {
            position: relative;
            height: 180px;
        }

        /* ── TABLE CARD ── */
        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            backdrop-filter: blur(16px);
            margin-bottom: 24px;
        }

        .table-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-title {
            font-size: 14px;
            font-weight: 600;
        }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 12px 20px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid rgba(56, 120, 220, 0.08);
            transition: background 0.15s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(26, 110, 247, 0.05); }

        tbody td {
            padding: 12px 20px;
            font-size: 12px;
            color: var(--text-primary);
            vertical-align: middle;
            white-space: nowrap;
        }

        .td-muted { color: var(--text-muted); }

        .td-truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .latency-pill {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 600;
        }

        .latency-good  { background: rgba(0,212,160,0.12);  color: var(--green);  border: 1px solid rgba(0,212,160,0.25); }
        .latency-med   { background: rgba(255,140,66,0.12); color: var(--orange); border: 1px solid rgba(255,140,66,0.25); }
        .latency-slow  { background: rgba(255,78,106,0.12); color: var(--red);    border: 1px solid rgba(255,78,106,0.25); }

        /* ── FARMACIA LIST ── */
        .farmacia-list { display: flex; flex-direction: column; gap: 8px; }

        .farmacia-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .farmacia-bar-wrap {
            flex: 1;
            height: 6px;
            background: rgba(56,120,220,0.12);
            border-radius: 10px;
            overflow: hidden;
        }

        .farmacia-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--blue-1), var(--blue-2));
            border-radius: 10px;
            transition: width 1s ease;
        }

        .farmacia-name {
            font-size: 11px;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }

        .farmacia-count {
            font-size: 11px;
            font-weight: 700;
            color: var(--blue-2);
            min-width: 30px;
            text-align: right;
        }

        /* ── VERSION PILLS ── */
        .version-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .version-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .version-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .version-label {
            font-size: 12px;
            color: var(--text-primary);
            flex: 1;
            text-transform: capitalize;
        }

        .version-count {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .version-pct   { font-size: 10px; color: var(--text-muted); min-width: 35px; text-align: right; }

        /* ══════════════════════════════════════
           RESPONSIVE — TABLET  (< 1024px)
        ══════════════════════════════════════ */
        @media (max-width: 1024px) {
            .hamburger { display: flex; }
            .sidebar-close { display: flex; }
            .sidebar-overlay { display: block; }

            .sidebar {
                transform: translateX(-100%);
                box-shadow: 4px 0 40px rgba(0,0,0,0.5);
            }
            .sidebar.open { transform: translateX(0); }

            .main { margin-left: 0; }

            .topbar { padding: 14px 20px; }
            .content { padding: 20px 20px; }

            .kpi-grid   { grid-template-columns: repeat(2, 1fr); }
            .kpi-grid-2 { grid-template-columns: repeat(2, 1fr); }

            .charts-grid   { grid-template-columns: 1fr; }
            .charts-grid-3 { grid-template-columns: 1fr 1fr; }

            .kpi-value { font-size: 26px; }
        }

        /* ══════════════════════════════════════
           RESPONSIVE — MÓVIL  (< 640px)
        ══════════════════════════════════════ */
        @media (max-width: 640px) {
            .topbar { padding: 12px 16px; min-height: 60px; }
            .topbar-left h1 { font-size: 14px; }
            .topbar-left p  { display: none; }
            .live-badge-text { display: none; }
            .live-badge     { padding: 5px 10px; }

            .content { padding: 14px 12px; }

            .kpi-grid   { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .kpi-grid-2 { grid-template-columns: 1fr; gap: 10px; }

            .kpi-card { padding: 14px; }
            .kpi-value { font-size: 22px; }
            .kpi-label { font-size: 10px; }
            .kpi-icon  { font-size: 16px; }

            .charts-grid   { grid-template-columns: 1fr; gap: 12px; }
            .charts-grid-3 { grid-template-columns: 1fr; gap: 12px; }

            .chart-card { padding: 16px; margin-bottom: 12px; }
            .chart-card-header { flex-wrap: wrap; gap: 8px; }
            .chart-wrap    { height: 190px; }
            .chart-wrap-sm { height: 155px; }

            .table-card-header { padding: 14px 16px; flex-wrap: wrap; gap: 8px; }
            thead th { padding: 10px 12px; }
            tbody td { padding: 10px 12px; }
        }

        /* ══════════════════════════════════════
           RESPONSIVE — MÓVIL XS  (< 400px)
        ══════════════════════════════════════ */
        @media (max-width: 400px) {
            .kpi-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ── SIDEBAR ── -->
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-close" id="sidebarClose" aria-label="Cerrar menú">✕</button>
        <div class="sidebar-logo">
            <img src="/panel-ia/public/images/mascota-ia.png" alt="IA">
            <div class="sidebar-logo-text">
                <strong>Panel IA</strong>
                <span>iCompras360</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-section-label">Principal</span>
            <a class="nav-item active" href="{{ route('dashboard') }}">
                <span class="nav-icon">📊</span> Dashboard
            </a>

            <span class="nav-section-label">Análisis</span>
            <a class="nav-item" href="{{ route('conversaciones') }}">
                <span class="nav-icon">💬</span> Conversaciones
            </a>
            <a class="nav-item" href="{{ route('farmacias') }}">
                <span class="nav-icon">🏪</span> Farmacias
            </a>
            <a class="nav-item" href="{{ route('rendimiento') }}">
                <span class="nav-icon">⚡</span> Rendimiento
            </a>
            <a class="nav-item" href="{{ route('usuarios') }}">
                <span class="nav-icon">👥</span> Usuarios
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="user-details">
                    <strong>{{ Auth::user()->name }}</strong>
                    <span>{{ Auth::user()->email }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">🚪 Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <!-- ── MAIN ── -->
    <main class="main">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="hamburger" aria-label="Abrir menú" aria-expanded="false">☰</button>
                <div>
                    <h1>RESUMEN DE ICOMPRAS IA</h1>
                    <p>Estadísticas en tiempo real del asistente IA · iCompras360</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="live-badge">
                    <span class="live-dot"></span>
                    <span class="live-badge-text">En Vivo</span>
                </div>
                <span class="topbar-date" style="font-size:12px; color:var(--text-muted)">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="content">

            <!-- ── KPI ROW 1 ── -->
            <div class="kpi-grid">
                <div class="kpi-card blue">
                    <div class="kpi-header">
                        <span class="kpi-label">Total Mensajes</span>
                        <span class="kpi-icon">💬</span>
                    </div>
                    <div class="kpi-value">{{ number_format($totalMensajes) }}</div>
                    <div class="kpi-sub">Todos los registros históricos</div>
                </div>
                <div class="kpi-card green">
                    <div class="kpi-header">
                        <span class="kpi-label">Hoy</span>
                        <span class="kpi-icon">📅</span>
                    </div>
                    <div class="kpi-value">{{ number_format($mensajesHoy) }}</div>
                    <div class="kpi-sub">Mensajes en las últimas 24h</div>
                </div>
                <div class="kpi-card orange">
                    <div class="kpi-header">
                        <span class="kpi-label">Esta Semana</span>
                        <span class="kpi-icon">📆</span>
                    </div>
                    <div class="kpi-value">{{ number_format($mensajesSemana) }}</div>
                    <div class="kpi-sub">Desde el lunes</div>
                </div>
                <div class="kpi-card purple">
                    <div class="kpi-header">
                        <span class="kpi-label">Este Mes</span>
                        <span class="kpi-icon">🗓️</span>
                    </div>
                    <div class="kpi-value">{{ number_format($mensajesMes) }}</div>
                    <div class="kpi-sub">{{ now()->format('F Y') }}</div>
                </div>
            </div>

            <!-- ── KPI ROW 2 ── -->
            <div class="kpi-grid-2">
                <div class="kpi-card blue">
                    <div class="kpi-header">
                        <span class="kpi-label">Latencia Promedio</span>
                        <span class="kpi-icon">⚡</span>
                    </div>
                    <div class="kpi-value" style="font-size:26px;">{{ number_format($latenciaPromedio) }}<small style="font-size:14px;font-weight:500"> ms</small></div>
                    <div class="kpi-sub">Mín: {{ number_format($latenciaMin) }} ms · Máx: {{ number_format($latenciaMax) }} ms</div>
                </div>
                <div class="kpi-card green">
                    <div class="kpi-header">
                        <span class="kpi-label">Usuarios Únicos</span>
                        <span class="kpi-icon">👥</span>
                    </div>
                    <div class="kpi-value">{{ number_format($usuariosUnicos) }}</div>
                    <div class="kpi-sub">IDs de contacto distintos</div>
                </div>
                <div class="kpi-card orange">
                    <div class="kpi-header">
                        <span class="kpi-label">Farmacias Activas</span>
                        <span class="kpi-icon">🏪</span>
                    </div>
                    <div class="kpi-value">{{ number_format($farmaciasActivas) }}</div>
                    <div class="kpi-sub">{{ number_format($sesionesUnicas) }} sesiones únicas</div>
                </div>
            </div>

            <!-- ── CHARTS ROW 1: Mensajes por día + Farmacias ── -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title">Mensajes por Día</div>
                            <div class="chart-subtitle">Últimos 30 días</div>
                        </div>
                        <span class="chart-badge">30 días</span>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="chartDia"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title">Top Farmacias</div>
                            <div class="chart-subtitle">Por volumen de mensajes</div>
                        </div>
                    </div>
                    @php $maxFarm = $topFarmacias->max('total') ?: 1; @endphp
                    <div class="farmacia-list">
                        @forelse($topFarmacias as $f)
                            <div class="farmacia-item">
                                <span class="farmacia-name" title="{{ $f->nombre_farmacia }}">
                                    {{ Str::limit($f->nombre_farmacia, 22) }}
                                </span>
                                <div class="farmacia-bar-wrap">
                                    <div class="farmacia-bar" style="width:{{ ($f->total / $maxFarm) * 100 }}%"></div>
                                </div>
                                <span class="farmacia-count">{{ $f->total }}</span>
                            </div>
                        @empty
                            <p style="color:var(--text-muted);font-size:12px;">Sin datos de farmacias</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- ── CHARTS ROW 2: Hora del día + Versión + Latencia ── -->
            <div class="charts-grid-3">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title">Actividad por Hora</div>
                            <div class="chart-subtitle">Distribución horaria</div>
                        </div>
                    </div>
                    <div class="chart-wrap-sm">
                        <canvas id="chartHora"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title">Versión iCompras</div>
                            <div class="chart-subtitle">Uso por versión</div>
                        </div>
                    </div>
                    <div class="chart-wrap-sm">
                        <canvas id="chartVersion"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title">Latencia IA</div>
                            <div class="chart-subtitle">Últimos 14 días (ms)</div>
                        </div>
                    </div>
                    <div class="chart-wrap-sm">
                        <canvas id="chartLatencia"></canvas>
                    </div>
                </div>
            </div>

            <!-- ── TABLA DE ACTIVIDAD RECIENTE ── -->
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-title">🕐 Actividad Reciente</div>
                    <span class="chart-badge">Últimos 10 registros</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha / Hora</th>
                                <th>Farmacia</th>
                                <th>Pregunta</th>
                                <th>Latencia</th>
                                <th>Versión</th>
                                <th>Página</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recientes as $r)
                                @php
                                    $lat = $r->latencia_ms;
                                    $latClass = $lat < 2000 ? 'latency-good' : ($lat < 5000 ? 'latency-med' : 'latency-slow');
                                @endphp
                                <tr>
                                    <td class="td-muted">{{ $r->id }}</td>
                                    <td class="td-muted" style="white-space:nowrap;">
                                        {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/y H:i') }}
                                    </td>
                                    <td>{{ $r->nombre_farmacia ?? '<span class="td-muted">—</span>' }}</td>
                                    <td class="td-truncate" title="{{ $r->pregunta }}">
                                        {{ Str::limit($r->pregunta, 45) }}
                                    </td>
                                    <td>
                                        <span class="latency-pill {{ $latClass }}">
                                            {{ number_format($lat) }} ms
                                        </span>
                                    </td>
                                    <td class="td-muted">{{ $r->version_icompras ?? '—' }}</td>
                                    <td class="td-truncate td-muted" title="{{ $r->pagina_origen }}" style="max-width:150px;">
                                        {{ Str::limit($r->pagina_origen, 30) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content -->
    </main><!-- /main -->

    <script>
    // Colores base
    const C = {
        blue:   '#5aa0ff',
        green:  '#00e6b0',
        orange: '#ff9f55',
        purple: '#a374ff',
        red:    '#ff6080',
        grid:   'rgba(90,160,255,0.12)',
        text:   '#7fa4cc',
    };

    Chart.defaults.color = C.text;
    Chart.defaults.font.family = 'Inter';

    // ── Mensajes por día ──
    const diasLabels = @json($mensajesPorDia->pluck('fecha'));
    const diasData   = @json($mensajesPorDia->pluck('total'));

    new Chart(document.getElementById('chartDia'), {
        type: 'line',
        data: {
            labels: diasLabels,
            datasets: [{
                label: 'Mensajes',
                data: diasData,
                borderColor: C.blue,
                backgroundColor: 'rgba(59,138,255,0.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: C.blue,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: C.grid }, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
                y: { grid: { color: C.grid }, beginAtZero: true, ticks: { font: { size: 10 } } }
            }
        }
    });

    // ── Actividad por hora ──
    const horasLabels = Array.from({length: 24}, (_, i) => i + 'h');
    const horasData   = @json($horasData);

    new Chart(document.getElementById('chartHora'), {
        type: 'bar',
        data: {
            labels: horasLabels,
            datasets: [{
                label: 'Mensajes',
                data: horasData,
                backgroundColor: 'rgba(139,92,246,0.6)',
                borderColor: C.purple,
                borderWidth: 1,
                borderRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 }, maxTicksLimit: 12 } },
                y: { grid: { color: C.grid }, beginAtZero: true, ticks: { font: { size: 9 } } }
            }
        }
    });

    // ── Versiones ──
    const versionLabels = @json($porVersion->pluck('version_icompras'));
    const versionData   = @json($porVersion->pluck('total'));
    const vColors       = [C.blue, C.green, C.orange, C.purple, C.red];

    new Chart(document.getElementById('chartVersion'), {
        type: 'doughnut',
        data: {
            labels: versionLabels,
            datasets: [{
                data: versionData,
                backgroundColor: vColors.map(c => c + 'cc'),
                borderColor: vColors,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8, boxWidth: 10 } }
            },
            cutout: '65%',
        }
    });

    // ── Latencia por día ──
    const latLabels  = @json($latenciaPorDia->pluck('fecha'));
    const latProm    = @json($latenciaPorDia->pluck('promedio')->map(fn($v) => round($v)));
    const latMax     = @json($latenciaPorDia->pluck('maximo'));

    new Chart(document.getElementById('chartLatencia'), {
        type: 'line',
        data: {
            labels: latLabels,
            datasets: [
                {
                    label: 'Promedio',
                    data: latProm,
                    borderColor: C.green,
                    backgroundColor: 'rgba(0,212,160,0.07)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    borderWidth: 2,
                },
                {
                    label: 'Máximo',
                    data: latMax,
                    borderColor: C.red,
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 2,
                    borderWidth: 1.5,
                    borderDash: [4, 3],
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 }, maxTicksLimit: 7 } },
                y: { grid: { color: C.grid }, beginAtZero: true, ticks: { font: { size: 9 } } }
            }
        }
    });
    </script>

    <script>
    (function () {
        const sidebar   = document.getElementById('sidebar');
        const overlay   = document.getElementById('sidebarOverlay');
        const hamburger = document.getElementById('hamburger');
        const closeBtn  = document.getElementById('sidebarClose');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('active');
            hamburger.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        hamburger.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });

        sidebar.querySelectorAll('.nav-item').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 1024) closeSidebar();
            });
        });
    })();
    </script>
</body>
</html>
