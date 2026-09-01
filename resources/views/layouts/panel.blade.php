<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Panel iCompras360</title>
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

        .sidebar-logo {
            padding: 22px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 11px;
        }
        .sidebar-logo img {
            width: 38px; height: 38px;
            border-radius: 50%;
            object-fit: contain;
            background: white;
            padding: 3px;
            flex-shrink: 0;
        }
        .sidebar-logo-text strong { display: block; font-size: 13px; font-weight: 700; }
        .sidebar-logo-text span { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        /* boton cerrar sidebar (movil) */
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
        }
        .nav-item:hover { background: rgba(59, 130, 246, 0.1); color: var(--text-primary); }
        .nav-item.active { background: rgba(59, 130, 246, 0.14); color: var(--accent-light); border: 1px solid rgba(59, 130, 246, 0.18); }
        .nav-icon { font-size: 15px; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 14px 10px;
            border-top: 1px solid var(--border);
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

        /* boton hamburguesa */
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

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
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
        .topbar-date { font-size: 12px; color: var(--text-muted); white-space: nowrap; }

        /* contenido */
        .content { padding: 22px 24px; flex: 1; }

        /* grids de indicadores */
        .kpi-grid   { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; }
        .kpi-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
        .kpi-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 18px; }

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
        .kpi-card.red::before    { background: var(--red); }
        .kpi-card:hover { background: var(--bg-card-hover); }
        .kpi-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .kpi-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; }
        .kpi-icon { font-size: 17px; color: var(--text-muted); }
        .kpi-value { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; line-height: 1; margin-bottom: 4px; }
        .kpi-card.blue   .kpi-value { color: var(--accent-light); }
        .kpi-card.green  .kpi-value { color: var(--green); }
        .kpi-card.orange .kpi-value { color: var(--orange); }
        .kpi-card.purple .kpi-value { color: var(--purple); }
        .kpi-card.red    .kpi-value { color: var(--red); }
        .kpi-sub { font-size: 11px; color: var(--text-muted); }

        /* tarjetas de graficas */
        .chart-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 18px;
        }
        .chart-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .chart-title { font-size: 14px; font-weight: 600; }
        .chart-subtitle { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
        .chart-badge {
            font-size: 10px; font-weight: 600;
            padding: 3px 9px;
            border-radius: 6px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: var(--accent-light);
            white-space: nowrap;
        }
        .chart-wrap    { position: relative; height: 220px; }
        .chart-wrap-sm { position: relative; height: 170px; }
        .charts-grid   { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 18px; }
        .charts-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 18px; }
        .charts-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; }

        /* descripcion de graficas */
        .chart-description {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
            line-height: 1.5;
        }

        /* tarjetas de tablas */
        .table-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin-bottom: 18px; }
        .table-card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
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

        .pill { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .pill-green  { background: rgba(16, 185, 129, 0.12); color: var(--green);       border: 1px solid rgba(16, 185, 129, 0.22); }
        .pill-orange { background: rgba(245, 158, 11, 0.12); color: var(--orange);      border: 1px solid rgba(245, 158, 11, 0.22); }
        .pill-red    { background: rgba(239, 68, 68, 0.12);  color: var(--red);         border: 1px solid rgba(239, 68, 68, 0.22); }
        .pill-blue   { background: rgba(59, 130, 246, 0.12); color: var(--accent-light); border: 1px solid rgba(59, 130, 246, 0.22); }

        /* lista de farmacias */
        .farmacia-list  { display: flex; flex-direction: column; gap: 8px; }
        .farmacia-item  { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .farmacia-bar-wrap { flex: 1; height: 5px; background: rgba(59, 130, 246, 0.1); border-radius: 8px; overflow: hidden; }
        .farmacia-bar { height: 100%; background: var(--accent); border-radius: 8px; transition: width 0.8s ease; }
        .farmacia-name { font-size: 11px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
        .farmacia-count { font-size: 11px; font-weight: 700; color: var(--accent-light); min-width: 30px; text-align: right; }

        /* lista de versiones */
        .version-list  { display: flex; flex-direction: column; gap: 10px; }
        .version-item  { display: flex; align-items: center; gap: 10px; }
        .version-dot   { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .version-label { font-size: 12px; color: var(--text-primary); flex: 1; text-transform: capitalize; }
        .version-count { font-size: 12px; font-weight: 700; color: var(--text-primary); }
        .version-pct   { font-size: 10px; color: var(--text-muted); min-width: 35px; text-align: right; }

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
            text-decoration: none;
        }
        .btn-filter:active { transform: scale(0.97); }
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
        .btn-filter-ghost:hover { background: rgba(239, 68, 68, 0.15); }
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
            animation: fadeInTag 0.3s ease;
        }
        @keyframes fadeInTag {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @yield('extra-styles')

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
            .sidebar.open {
                transform: translateX(0);
            }

            .main { margin-left: 0; }
            .topbar { padding: 12px 16px; }
            .content { padding: 16px 14px; }

            .kpi-grid   { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .kpi-grid-3 { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .kpi-grid-2 { grid-template-columns: repeat(2, 1fr); gap: 10px; }

            .charts-grid   { grid-template-columns: 1fr; }
            .charts-grid-3 { grid-template-columns: 1fr 1fr; }
            .charts-grid-2 { grid-template-columns: 1fr; }

            .charts-grid-3 > div[style*="grid-column"] { grid-column: span 1 !important; }

            .kpi-value { font-size: 22px; }
        }

        /* responsive movil */
        @media (max-width: 640px) {
            .topbar { padding: 10px 12px; min-height: 52px; }
            .topbar-left h1 { font-size: 14px; }
            .topbar-left p { display: none; }
            .topbar-date { display: none; }
            .status-badge { padding: 4px 8px; font-size: 10px; }

            .content { padding: 12px 10px; }

            .kpi-grid   { grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px; }
            .kpi-grid-3 { grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px; }
            .kpi-grid-2 { grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px; }

            .kpi-card { padding: 12px; }
            .kpi-value { font-size: 20px; }
            .kpi-label { font-size: 10px; }
            .kpi-header { margin-bottom: 6px; }
            .kpi-sub { font-size: 10px; }

            .charts-grid   { grid-template-columns: 1fr; gap: 8px; margin-bottom: 12px; }
            .charts-grid-3 { grid-template-columns: 1fr; gap: 8px; margin-bottom: 12px; }
            .charts-grid-2 { grid-template-columns: 1fr; gap: 8px; margin-bottom: 12px; }

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

            .kpi-icon { font-size: 14px; }

            .farmacia-name { max-width: 90px; font-size: 10px; }
            .farmacia-count { font-size: 10px; }

            .sidebar-logo { padding: 16px 12px; }
            .sidebar-nav { padding: 10px 8px; }
            .sidebar-footer { padding: 10px 8px; }

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

        /* movil xs */
        @media (max-width: 400px) {
            .kpi-grid   { grid-template-columns: 1fr 1fr; }
            .kpi-grid-3 { grid-template-columns: 1fr; }
            .topbar-left h1 { font-size: 13px; }
        }

        /* paginacion laravel */
        nav[aria-label="Pagination Navigation"],
        .pagination-wrapper {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        nav[aria-label="Pagination Navigation"] > p,
        .pagination-wrapper p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
        }

        nav[aria-label="Pagination Navigation"] > div:last-child,
        .pagination-wrapper > div:last-child {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-items: center;
        }

        nav[aria-label="Pagination Navigation"] span,
        nav[aria-label="Pagination Navigation"] a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            font-family: 'Inter', system-ui, sans-serif;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        nav[aria-label="Pagination Navigation"] span[aria-current="page"] > span {
            background: rgba(59, 130, 246, 0.18);
            border-color: rgba(59, 130, 246, 0.35);
            color: var(--accent-light);
            min-width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        nav[aria-label="Pagination Navigation"] a {
            background: rgba(16, 38, 78, 0.5);
            border-color: var(--border);
            color: var(--text-muted);
        }
        nav[aria-label="Pagination Navigation"] a:hover {
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.3);
            color: var(--text-primary);
        }

        nav[aria-label="Pagination Navigation"] span:not([aria-current]) {
            background: rgba(16, 38, 78, 0.25);
            border-color: var(--border);
            color: rgba(122, 154, 191, 0.35);
            cursor: not-allowed;
        }

        nav[aria-label="Pagination Navigation"] svg {
            width: 14px;
            height: 14px;
            display: inline-block;
            vertical-align: middle;
            fill: currentColor;
        }

        nav[aria-label="Pagination Navigation"] span[aria-disabled] {
            background: transparent;
            border-color: transparent;
            color: var(--text-muted);
        }
    </style>

</head>
<body>

<!-- overlay movil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

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
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" id="nav-dashboard">
            <i class="bi bi-house-door nav-icon"></i> Inicio
        </a>
        <span class="nav-section-label">Módulos</span>
        <a class="nav-item {{ request()->routeIs('conversaciones') ? 'active' : '' }}" href="{{ route('conversaciones') }}" id="nav-conversaciones">
            <i class="bi bi-chat-square-dots nav-icon"></i> Conversaciones
        </a>
        <a class="nav-item {{ request()->routeIs('farmacias') ? 'active' : '' }}" href="{{ route('farmacias') }}" id="nav-farmacias">
            <i class="bi bi-building nav-icon"></i> Farmacias
        </a>
        <a class="nav-item {{ request()->routeIs('rendimiento') ? 'active' : '' }}" href="{{ route('rendimiento') }}" id="nav-rendimiento">
            <i class="bi bi-bar-chart-line nav-icon"></i> Rendimiento
        </a>
        <a class="nav-item {{ request()->routeIs('usuarios') ? 'active' : '' }}" href="{{ route('usuarios') }}" id="nav-usuarios">
            <i class="bi bi-person-lines-fill nav-icon"></i> Usuarios
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
            <button type="submit" class="btn-logout" id="btn-logout"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</button>
        </form>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="hamburger" id="hamburger" aria-label="Abrir menú" aria-expanded="false"><i class="bi bi-list"></i></button>
            <div>
                <h1>@yield('page-title')</h1>
                <p>@yield('page-subtitle')</p>
            </div>
        </div>
        <div class="topbar-right">
            <div class="status-badge"><span class="status-dot"></span> Activo</div>
            <span class="topbar-date">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>
    <div class="content">
        <!-- Filtro de rango de fechas (global) -->
        @php
            $currentRoute = request()->route()->getName();
            $fechaDesde = request('fecha_desde');
            $fechaHasta = request('fecha_hasta');
            $filtroActivo = $fechaDesde && $fechaHasta;
        @endphp
        <form class="date-filter-bar" id="dateFilterForm" method="GET" action="{{ route($currentRoute) }}">
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
                <a href="{{ route($currentRoute) }}" class="btn-filter btn-filter-ghost">
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

        @yield('content')
    </div>
</main>

<script>
(function () {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
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

<script>
// ── Quick-select buttons para el filtro de fechas ───────────────
(function () {
    var form       = document.getElementById('dateFilterForm');
    var inputDesde = document.getElementById('fechaDesde');
    var inputHasta = document.getElementById('fechaHasta');
    if (!form) return;

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

@yield('extra-scripts')

</body>
</html>
