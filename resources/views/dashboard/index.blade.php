<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel Administrativo iCompras360 — Estadísticas del asistente">
    <title>Inicio — Panel iCompras360</title>

    {{-- Icono de la pestana del navegador.
         asset() arma la URL a partir de la peticion actual, asi que funciona
         igual servido por Apache (/panel-ia/public/...) que por artisan serve. --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-deep:       #0b1e3d;
            --bg-card:       rgba(16, 38, 78, 0.75);
            --bg-card-hover: rgba(22, 50, 100, 0.85);
            --border:        rgba(70, 130, 220, 0.18);
            --accent:        #3b82f6;
            --accent-light:  #60a5fa;
            --green:         #10b981;
            --orange:        #f59e0b;
            --purple:        #8b5cf6;
            --red:           #ef4444;
            --text-primary:  #e8edf5;
            --text-muted:    #7a9abf;
            --sidebar-w:     235px;
            --topbar-h:      64px;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* overlay movil */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(4, 13, 26, 0.75);
            z-index: 90;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        /* sidebar */
        .sidebar {
            width: var(--sidebar-w);
            background: rgba(8, 22, 55, 0.97);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-close {
            display: none;
            position: absolute;
            top: 16px; right: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            border-radius: 8px;
            width: 34px; height: 34px;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-muted);
            transition: background 0.2s, color 0.2s;
            line-height: 1;
            z-index: 110;
        }
        .sidebar-close:hover { background: rgba(255,255,255,0.1); color: var(--text-primary); }

        .sidebar-logo {
            padding: 22px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 11px;
            padding-right: 48px;
        }
        .sidebar-logo img {
            width: 38px; height: 38px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 3px;
        }
        .sidebar-logo-text { display: flex; flex-direction: column; }
        .sidebar-logo-text strong { font-size: 13px; font-weight: 700; color: var(--text-primary); line-height: 1.2; }
        .sidebar-logo-text span { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        .sidebar-nav {
            flex: 1;
            padding: 14px 10px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            overflow-y: auto;
        }
        .nav-section-label {
            font-size: 10px; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px 10px 5px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 13px; font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
        }
        .nav-item:hover { background: rgba(59, 130, 246, 0.1); color: var(--text-primary); }
        .nav-item.active { background: rgba(59, 130, 246, 0.14); color: var(--accent-light); border: 1px solid rgba(59, 130, 246, 0.18); }
        .nav-icon { font-size: 15px; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 14px 10px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 8px;
            min-width: 0;
        }
        .user-avatar {
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            flex-shrink: 0;
        }
        .user-details { min-width: 0; }
        .user-details strong { display: block; font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-details span { font-size: 10px; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }

        .btn-logout {
            width: 100%; padding: 8px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.18);
            border-radius: 8px;
            color: var(--red);
            font-size: 12px; font-family: 'Inter', system-ui, sans-serif; font-weight: 500;
            cursor: pointer; transition: background 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .btn-logout:hover { background: rgba(239, 68, 68, 0.15); }

        /* area principal */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
            background: var(--bg-deep);
        }

        /* topbar */
        .topbar {
            padding: 14px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(8, 22, 55, 0.8);
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 12px;
            min-height: var(--topbar-h);
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
        .topbar-left h1 { font-size: 17px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .topbar-left p { font-size: 11px; color: var(--text-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .hamburger {
            display: none;
            align-items: center; justify-content: center;
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            color: var(--text-primary);
            font-size: 18px;
            line-height: 1;
        }
        .hamburger:hover { background: rgba(59, 130, 246, 0.12); }

        .topbar-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .status-badge {
            display: flex; align-items: center; gap: 6px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 11px; font-weight: 600;
            color: var(--green);
            white-space: nowrap;
        }
        .status-dot {
            width: 6px; height: 6px;
            background: var(--green);
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* contenido */
        .content { padding: 22px 24px; flex: 1; }

        /* tarjetas de indicadores */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; }
        .kpi-grid-2 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }

        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            transition: background 0.2s;
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 10px 10px 0 0;
        }
        .kpi-card.blue::before   { background: var(--accent); }
        .kpi-card.green::before  { background: var(--green); }
        .kpi-card.orange::before { background: var(--orange); }
        .kpi-card.purple::before { background: var(--purple); }

        .kpi-card:hover { background: var(--bg-card-hover); }

        .kpi-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .kpi-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; }
        .kpi-icon { font-size: 17px; color: var(--text-muted); }

        .kpi-value { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; line-height: 1; margin-bottom: 4px; }
        .kpi-card.blue   .kpi-value { color: var(--accent-light); }
        .kpi-card.green  .kpi-value { color: var(--green); }
        .kpi-card.orange .kpi-value { color: var(--orange); }
        .kpi-card.purple .kpi-value { color: var(--purple); }

        .kpi-sub { font-size: 11px; color: var(--text-muted); }

        /* graficas */
        .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 18px; }
        .charts-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 18px; }

        .chart-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px;
        }
        .chart-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .chart-title { font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 7px; }
        .chart-subtitle { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .chart-badge {
            font-size: 10px; font-weight: 600;
            padding: 3px 9px;
            border-radius: 6px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: var(--accent-light);
        }

        .chart-wrap { position: relative; height: 220px; }
        .chart-wrap-sm { position: relative; height: 180px; }

        .chart-description {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            line-height: 1.5;
        }

        /* tabla */
        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 18px;
        }
        .table-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .table-title { font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 11px 18px; text-align: left; font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.7px; border-bottom: 1px solid var(--border); white-space: nowrap; }
        tbody tr { border-bottom: 1px solid rgba(70, 130, 220, 0.07); transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(59, 130, 246, 0.04); }
        tbody td { padding: 11px 18px; font-size: 12px; color: var(--text-primary); vertical-align: middle; white-space: nowrap; }
        .td-muted { color: var(--text-muted); }
        .td-truncate { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .latency-pill { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .latency-good { background: rgba(16,185,129,0.12); color: var(--green); border: 1px solid rgba(16,185,129,0.22); }
        .latency-med  { background: rgba(245,158,11,0.12); color: var(--orange); border: 1px solid rgba(245,158,11,0.22); }
        .latency-slow { background: rgba(239,68,68,0.12); color: var(--red); border: 1px solid rgba(239,68,68,0.22); }

        /* lista de farmacias */
        .farmacia-list { display: flex; flex-direction: column; gap: 8px; }
        .farmacia-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .farmacia-bar-wrap { flex: 1; height: 5px; background: rgba(59,130,246,0.1); border-radius: 8px; overflow: hidden; }
        .farmacia-bar { height: 100%; background: var(--accent); border-radius: 8px; transition: width 0.8s ease; }
        .farmacia-name { font-size: 11px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
        .farmacia-count { font-size: 11px; font-weight: 700; color: var(--accent-light); min-width: 30px; text-align: right; }

        /* responsive tablet */
        @media (max-width: 1024px) {
            .hamburger {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .sidebar-close {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar {
                transform: translateX(-100%);
                box-shadow: 4px 0 30px rgba(0,0,0,0.4);
            }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .topbar { padding: 12px 16px; }
            .content { padding: 16px 14px; }

            .kpi-grid   { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .kpi-grid-2 { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .charts-grid   { grid-template-columns: 1fr; }
            .charts-grid-3 { grid-template-columns: 1fr 1fr; }
            .kpi-value { font-size: 22px; }
        }

        /* responsive movil */
        @media (max-width: 640px) {
            .topbar { padding: 10px 12px; min-height: 52px; }
            .topbar-left h1 { font-size: 14px; }
            .topbar-left p { display: none; }
            .status-badge span:last-child { display: none; }
            .status-badge { padding: 4px 8px; }

            .content { padding: 12px 10px; }

            .kpi-grid   { grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px; }
            .kpi-grid-2 { grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px; }
            .kpi-card { padding: 12px; }
            .kpi-value { font-size: 20px; }
            .kpi-label { font-size: 10px; }
            .kpi-icon { font-size: 14px; }
            .kpi-header { margin-bottom: 6px; }
            .kpi-sub { font-size: 10px; }

            .charts-grid   { grid-template-columns: 1fr; gap: 8px; margin-bottom: 12px; }
            .charts-grid-3 { grid-template-columns: 1fr; gap: 8px; margin-bottom: 12px; }
            .chart-card { padding: 12px; margin-bottom: 0; }
            .chart-card-header { flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
            .chart-title { font-size: 13px; }
            .chart-wrap    { height: 170px; }
            .chart-wrap-sm { height: 140px; }
            .chart-description { display: none; }

            .table-card { margin-bottom: 12px; }
            .table-card-header { padding: 12px 14px; flex-wrap: wrap; gap: 6px; }
            thead th { padding: 8px 10px; font-size: 9px; }
            tbody td { padding: 8px 10px; font-size: 11px; }
            .td-truncate { max-width: 100px; }

            .farmacia-name { max-width: 90px; font-size: 10px; }
            .farmacia-count { font-size: 10px; }

            .sidebar-logo { padding: 16px 12px; }
            .sidebar-nav { padding: 10px 8px; }
            .sidebar-footer { padding: 10px 8px; }
        }

        @media (max-width: 400px) {
            .kpi-grid { grid-template-columns: 1fr 1fr; }
            .topbar-left h1 { font-size: 13px; }
        }

        /* ── Filtro de rango de fechas ────────────────────────────────── */
        .date-filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            padding: 14px 18px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            flex-wrap: wrap;
        }
        .date-filter-bar .filter-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .date-filter-bar .filter-label i {
            font-size: 15px;
            color: var(--accent-light);
        }
        .date-filter-bar input[type="date"] {
            background: rgba(11, 30, 61, 0.9);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 12px;
            color: var(--text-primary);
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 12px;
            font-weight: 500;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            min-width: 140px;
        }
        .date-filter-bar input[type="date"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .date-filter-bar input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.7) sepia(0.5) saturate(3) hue-rotate(190deg);
            cursor: pointer;
        }
        .date-filter-sep {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 8px;
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
            white-space: nowrap;
        }
        .btn-filter:active {
            transform: scale(0.97);
        }
        .btn-filter-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        .btn-filter-primary:hover {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
        }
        .btn-filter-ghost {
            background: rgba(239, 68, 68, 0.08);
            color: var(--red);
            border: 1px solid rgba(239, 68, 68, 0.18);
        }
        .btn-filter-ghost:hover {
            background: rgba(239, 68, 68, 0.15);
        }

        .filter-quick-btns {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: auto;
        }
        .btn-quick {
            padding: 5px 11px;
            border-radius: 6px;
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 11px;
            font-weight: 500;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.03);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-quick:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-light);
            border-color: rgba(59, 130, 246, 0.25);
        }

        .filter-active-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 6px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: var(--accent-light);
            font-size: 11px;
            font-weight: 600;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 640px) {
            .date-filter-bar {
                padding: 10px 12px;
                gap: 8px;
                margin-bottom: 12px;
            }
            .date-filter-bar input[type="date"] {
                min-width: 0;
                flex: 1;
                font-size: 11px;
                padding: 6px 8px;
            }
            .filter-quick-btns {
                margin-left: 0;
                width: 100%;
                flex-wrap: wrap;
            }
            .btn-quick { font-size: 10px; padding: 4px 8px; }
            .btn-filter { font-size: 11px; padding: 6px 12px; }
        }
    </style>
</head>
<body>

    <!-- overlay movil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-close" id="sidebarClose" aria-label="Cerrar menú"><i class="bi bi-x-lg"></i></button>
        <div class="sidebar-logo">
            <img src="/panel-ia/public/images/mascota-ia.png" alt="iCompras360">
            <div class="sidebar-logo-text">
                <strong>Panel Administrativo</strong>
                <span>iCompras360</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-section-label">Principal</span>
            <a class="nav-item active" href="{{ route('dashboard') }}">
                <i class="bi bi-house-door nav-icon"></i> Inicio
            </a>

            <span class="nav-section-label">Módulos</span>
            <a class="nav-item" href="{{ route('conversaciones') }}">
                <i class="bi bi-chat-square-dots nav-icon"></i> Conversaciones
            </a>
            <a class="nav-item" href="{{ route('farmacias') }}">
                <i class="bi bi-building nav-icon"></i> Farmacias
            </a>
            <a class="nav-item" href="{{ route('rendimiento') }}">
                <i class="bi bi-bar-chart-line nav-icon"></i> Rendimiento
            </a>
            <a class="nav-item" href="{{ route('usuarios') }}">
                <i class="bi bi-person-lines-fill nav-icon"></i> Usuarios
            </a>

            {{-- Carga de manuales a la base de conocimiento del asistente --}}
            <span class="nav-section-label">Conocimiento</span>
            <a class="nav-item" href="{{ route('manuales.create') }}" id="nav-manuales">
                <i class="bi bi-journal-arrow-up nav-icon"></i> Manuales
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
                <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <!-- contenido principal -->
    <main class="main">

        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="hamburger" aria-label="Abrir menú" aria-expanded="false"><i class="bi bi-list"></i></button>
                <div>
                    <h1>Resumen General</h1>
                    <p>Estadísticas del asistente · iCompras360</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="status-badge">
                    <span class="status-dot"></span>
                    <span>Activo</span>
                </div>
                <span style="font-size:12px; color:var(--text-muted)">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="content">

            <!-- Filtro de rango de fechas -->
            <form class="date-filter-bar" id="dateFilterForm" method="GET" action="{{ route('dashboard') }}">
                <span class="filter-label">
                    <i class="bi bi-calendar2-range"></i>
                    Rango de Fechas
                </span>
                <input type="date" name="fecha_desde" id="fechaDesde" value="{{ $fechaDesde ?? '' }}" title="Fecha inicio">
                <span class="date-filter-sep">—</span>
                <input type="date" name="fecha_hasta" id="fechaHasta" value="{{ $fechaHasta ?? '' }}" title="Fecha fin">
                <button type="submit" class="btn-filter btn-filter-primary">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                @if($filtroActivo)
                    <a href="{{ route('dashboard') }}" class="btn-filter btn-filter-ghost">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                    <span class="filter-active-tag">
                        <i class="bi bi-check-circle"></i>
                        {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                    </span>
                @endif
                <div class="filter-quick-btns">
                    <button type="button" class="btn-quick" data-range="today">Hoy</button>
                    <button type="button" class="btn-quick" data-range="7days">7 días</button>
                    <button type="button" class="btn-quick" data-range="30days">30 días</button>
                    <button type="button" class="btn-quick" data-range="month">Este Mes</button>
                </div>
            </form>

            <!-- Indicadores principales: total de mensajes por periodo -->
            <div class="kpi-grid">
                <div class="kpi-card blue">
                    <div class="kpi-header">
                        <span class="kpi-label">Total Mensajes</span>
                        <i class="bi bi-chat-dots kpi-icon"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($totalMensajes) }}</div>
                    <div class="kpi-sub">Todos los registros históricos</div>
                </div>
                <div class="kpi-card green">
                    <div class="kpi-header">
                        <span class="kpi-label">Hoy</span>
                        <i class="bi bi-calendar-day kpi-icon"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($mensajesHoy) }}</div>
                    <div class="kpi-sub">Mensajes en las últimas 24h</div>
                </div>
                <div class="kpi-card orange">
                    <div class="kpi-header">
                        <span class="kpi-label">Esta Semana</span>
                        <i class="bi bi-calendar-week kpi-icon"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($mensajesSemana) }}</div>
                    <div class="kpi-sub">Desde el lunes</div>
                </div>
                <div class="kpi-card purple">
                    <div class="kpi-header">
                        <span class="kpi-label">Este Mes</span>
                        <i class="bi bi-calendar-month kpi-icon"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($mensajesMes) }}</div>
                    <div class="kpi-sub">{{ now()->format('F Y') }}</div>
                </div>
            </div>

            <!-- Indicadores secundarios: latencia, usuarios y farmacias -->
            <div class="kpi-grid-2">
                <div class="kpi-card blue">
                    <div class="kpi-header">
                        <span class="kpi-label">Latencia Promedio</span>
                        <i class="bi bi-lightning kpi-icon"></i>
                    </div>
                    <div class="kpi-value" style="font-size:24px;">{{ number_format($latenciaPromedio) }}<small style="font-size:13px;font-weight:500"> ms</small></div>
                    <div class="kpi-sub">Mín: {{ number_format($latenciaMin) }} ms · Máx: {{ number_format($latenciaMax) }} ms</div>
                </div>
                <div class="kpi-card green">
                    <div class="kpi-header">
                        <span class="kpi-label">Usuarios Únicos</span>
                        <i class="bi bi-person-check kpi-icon"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($usuariosUnicos) }}</div>
                    <div class="kpi-sub">IDs de contacto distintos</div>
                </div>
                <div class="kpi-card orange">
                    <div class="kpi-header">
                        <span class="kpi-label">Farmacias Activas</span>
                        <i class="bi bi-shop kpi-icon"></i>
                    </div>
                    <div class="kpi-value">{{ number_format($farmaciasActivas) }}</div>
                    <div class="kpi-sub">{{ number_format($sesionesUnicas) }} sesiones únicas</div>
                </div>
            </div>

            <!-- Gráfica de tendencia diaria y ranking de farmacias -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title"><i class="bi bi-graph-up"></i> Mensajes por Día</div>
                            <div class="chart-subtitle">Tendencia de los últimos 30 días</div>
                        </div>
                        <span class="chart-badge">30 días</span>
                    </div>
                    <div class="chart-wrap">
                        <canvas id="chartDia"></canvas>
                    </div>
                    <p class="chart-description">
                        Esta gráfica muestra la cantidad de mensajes procesados por el asistente cada día durante el último mes.
                        Permite identificar picos de actividad y tendencias de uso a lo largo del tiempo.
                    </p>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title"><i class="bi bi-bar-chart"></i> Top Farmacias</div>
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
                    <p class="chart-description">
                        Ranking de farmacias según la cantidad total de consultas realizadas al asistente.
                    </p>
                </div>
            </div>

            <!-- Gráficas: distribución horaria, versiones y latencia -->
            <div class="charts-grid-3">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title"><i class="bi bi-clock"></i> Actividad por Hora</div>
                            <div class="chart-subtitle">Distribución horaria de consultas</div>
                        </div>
                    </div>
                    <div class="chart-wrap-sm">
                        <canvas id="chartHora"></canvas>
                    </div>
                    <p class="chart-description">
                        Muestra en qué horas del día se concentran más las consultas. Útil para identificar horarios de mayor demanda.
                    </p>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title"><i class="bi bi-box-seam"></i> Versión iCompras</div>
                            <div class="chart-subtitle">Distribución por versión de la app</div>
                        </div>
                    </div>
                    <div class="chart-wrap-sm">
                        <canvas id="chartVersion"></canvas>
                    </div>
                    <p class="chart-description">
                        Proporción de mensajes enviados desde cada versión de la aplicación iCompras.
                    </p>
                </div>

                <div class="chart-card">
                    <div class="chart-card-header">
                        <div>
                            <div class="chart-title"><i class="bi bi-activity"></i> Latencia</div>
                            <div class="chart-subtitle">Últimos 14 días (milisegundos)</div>
                        </div>
                    </div>
                    <div class="chart-wrap-sm">
                        <canvas id="chartLatencia"></canvas>
                    </div>
                    <p class="chart-description">
                        Evolución del tiempo de respuesta del modelo. La línea verde indica el promedio y la roja punteada el máximo registrado por día.
                    </p>
                </div>
            </div>

            <!-- Tabla con los últimos 10 mensajes procesados -->
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-title"><i class="bi bi-clock-history"></i> Actividad Reciente</div>
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

        </div>
    </main>

    <script>
    // Configuracion general de Chart.js
    Chart.defaults.color = '#7a9abf';
    Chart.defaults.font.family = 'Inter';

    // Grafica 1: Mensajes por dia (linea)
    var diasLabels = @json($mensajesPorDia->pluck('fecha'));
    var diasData   = @json($mensajesPorDia->pluck('total'));

    new Chart(document.getElementById('chartDia'), {
        type: 'line',
        data: {
            labels: diasLabels,
            datasets: [{
                label: 'Mensajes',
                data: diasData,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.06)',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                pointBackgroundColor: '#3b82f6',
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
                        label: function(item) { return item.parsed.y + ' mensajes procesados'; }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(70,130,220,0.08)' },
                    ticks: { maxTicksLimit: 8, font: { size: 10 } }
                },
                y: {
                    grid: { color: 'rgba(70,130,220,0.08)' },
                    beginAtZero: true,
                    ticks: { font: { size: 10 } },
                    title: { display: true, text: 'Cantidad', font: { size: 10 }, color: '#7a9abf' }
                }
            }
        }
    });

    // Grafica 2: Actividad por hora (barras)
    var horasLabels = Array.from({length: 24}, function(_, i) { return i + ':00'; });
    var horasData   = @json($horasData);

    new Chart(document.getElementById('chartHora'), {
        type: 'bar',
        data: {
            labels: horasLabels,
            datasets: [{
                label: 'Mensajes',
                data: horasData,
                backgroundColor: 'rgba(139, 92, 246, 0.5)',
                borderColor: '#8b5cf6',
                borderWidth: 1,
                borderRadius: 3,
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
                        label: function(item) { return item.parsed.y + ' mensajes en este horario'; }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 }, maxTicksLimit: 12 }
                },
                y: {
                    grid: { color: 'rgba(70,130,220,0.08)' },
                    beginAtZero: true,
                    ticks: { font: { size: 9 } },
                    title: { display: true, text: 'Mensajes', font: { size: 9 }, color: '#7a9abf' }
                }
            }
        }
    });

    // Grafica 3: Versiones de iCompras (dona)
    var versionLabels = @json($porVersion->pluck('version_icompras'));
    var versionData   = @json($porVersion->pluck('total'));
    var vColors       = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'];

    new Chart(document.getElementById('chartVersion'), {
        type: 'doughnut',
        data: {
            labels: versionLabels,
            datasets: [{
                data: versionData,
                backgroundColor: vColors,
                borderColor: '#0b1e3d',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, padding: 8, boxWidth: 10 }
                },
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
            cutout: '60%',
        }
    });

    // Grafica 4: Latencia por dia (linea con promedio y maximo)
    var latLabels = @json($latenciaPorDia->pluck('fecha'));
    var latProm   = @json($latenciaPorDia->pluck('promedio')->map(fn($v) => round($v)));
    var latMax    = @json($latenciaPorDia->pluck('maximo'));

    new Chart(document.getElementById('chartLatencia'), {
        type: 'line',
        data: {
            labels: latLabels,
            datasets: [
                {
                    label: 'Promedio (ms)',
                    data: latProm,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.06)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    borderWidth: 2,
                },
                {
                    label: 'Máximo (ms)',
                    data: latMax,
                    borderColor: '#ef4444',
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.3,
                    pointRadius: 2,
                    borderWidth: 1.5,
                    borderDash: [5, 3],
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
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 }, maxTicksLimit: 7 }
                },
                y: {
                    grid: { color: 'rgba(70,130,220,0.08)' },
                    beginAtZero: true,
                    ticks: { font: { size: 9 } },
                    title: { display: true, text: 'ms', font: { size: 9 }, color: '#7a9abf' }
                }
            }
        }
    });
    </script>

    <script>
    // ── Quick-select buttons para el filtro de fechas ───────────────
    (function () {
        var form       = document.getElementById('dateFilterForm');
        var inputDesde = document.getElementById('fechaDesde');
        var inputHasta = document.getElementById('fechaHasta');

        function toISO(date) {
            var y = date.getFullYear();
            var m = String(date.getMonth() + 1).padStart(2, '0');
            var d = String(date.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + d;
        }

        document.querySelectorAll('.btn-quick').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var range = this.getAttribute('data-range');
                var hoy   = new Date();
                var desde, hasta;

                switch (range) {
                    case 'today':
                        desde = hasta = toISO(hoy);
                        break;
                    case '7days':
                        hasta = toISO(hoy);
                        var d7 = new Date(hoy);
                        d7.setDate(d7.getDate() - 6);
                        desde = toISO(d7);
                        break;
                    case '30days':
                        hasta = toISO(hoy);
                        var d30 = new Date(hoy);
                        d30.setDate(d30.getDate() - 29);
                        desde = toISO(d30);
                        break;
                    case 'month':
                        desde = toISO(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
                        hasta = toISO(hoy);
                        break;
                }

                inputDesde.value = desde;
                inputHasta.value = hasta;
                form.submit();
            });
        });
    })();
    </script>

    <script>
    (function () {
        var sidebar   = document.getElementById('sidebar');
        var overlay   = document.getElementById('sidebarOverlay');
        var hamburger = document.getElementById('hamburger');
        var closeBtn  = document.getElementById('sidebarClose');

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
