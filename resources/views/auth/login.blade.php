<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de administración - iCompras360">
    <title>Iniciar Sesión — iCompras360</title>

    {{-- Icono de la pestana del navegador.
         asset() arma la URL a partir de la peticion actual, asi que funciona
         igual servido por Apache en un subdirectorio que por artisan serve.
         Si el sitio se sirve desde otro host/CDN, definir ASSET_URL en el .env. --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-deep:      #0d2244;
            --bg-dark:      #102a54;
            --bg-card:      rgba(16, 42, 90, 0.82);
            --border:       rgba(80, 148, 255, 0.25);
            --border-focus: rgba(90, 160, 255, 0.75);
            --blue-primary: #2478ff;
            --blue-light:   #5aa0ff;
            --blue-glow:    rgba(36, 120, 255, 0.4);
            --text-primary: #eef4ff;
            --text-muted:   #7fa4cc;
            --text-label:   #a4bfdd;
            --error:        #ff6080;
            --error-bg:     rgba(255, 96, 128, 0.1);
            --success:      #00e6b0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-deep);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Fondo con gradiente sutil */
        .bg-scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-gradient {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(36, 120, 255, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 90%, rgba(59, 130, 246, 0.08) 0%, transparent 60%),
                linear-gradient(180deg, #0d2244 0%, #102a54 100%);
        }

        .grid-lines {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(80, 148, 255, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(80, 148, 255, 0.06) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 30%, transparent 100%);
        }

        /* Contenedor del login */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        /* Card */
        .login-card {
            background: rgba(14, 38, 85, 0.80);
            border: 1px solid rgba(90, 160, 255, 0.22);
            border-radius: 16px;
            padding: 44px 40px;
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            box-shadow:
                0 0 0 1px rgba(90, 160, 255, 0.08),
                0 24px 60px rgba(8, 20, 55, 0.5);
            animation: cardIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Logo / Header */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px; height: 80px;
            background: white;
            border-radius: 50%;
            margin-bottom: 16px;
            box-shadow: 0 6px 30px rgba(26, 110, 247, 0.3);
            position: relative;
            overflow: hidden;
            padding: 5px;
        }

        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .login-title {
            font-size: 19px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Separador */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin-bottom: 28px;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-label);
            letter-spacing: 0.3px;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 15px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 13px 14px 13px 40px;
            background: rgba(12, 32, 72, 0.65);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            outline: none;
            transition: all 0.25s ease;
        }

        .form-input::placeholder {
            color: rgba(127, 164, 204, 0.55);
        }

        .form-input:focus {
            border-color: var(--border-focus);
            background: rgba(16, 42, 92, 0.85);
            box-shadow: 0 0 0 3px rgba(90, 160, 255, 0.14);
        }

        .form-input:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: var(--blue-light);
        }

        .form-input.is-error {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(255, 78, 106, 0.1);
        }

        /* Toggle contraseña */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 16px;
            transition: color 0.2s;
            padding: 4px;
            line-height: 1;
        }

        .toggle-password:hover {
            color: var(--blue-light);
        }

        /* Error message */
        .error-message {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--error-bg);
            border: 1px solid rgba(255, 78, 106, 0.25);
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--error);
            animation: shakeIn 0.4s ease;
        }

        @keyframes shakeIn {
            0%  { transform: translateX(-8px); opacity: 0; }
            40% { transform: translateX(6px); }
            70% { transform: translateX(-4px); }
            100%{ transform: translateX(0); opacity: 1; }
        }

        /* Botón */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2478ff 0%, #1258d4 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 24px rgba(36, 120, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 36px rgba(36, 120, 255, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login .btn-text { display: inline-flex; align-items: center; gap: 8px; }
        .btn-login .btn-spinner { display: none; }
        .btn-login.loading .btn-text { display: none; }
        .btn-login.loading .btn-spinner { display: inline-block; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner-icon {
            display: inline-block;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .login-footer span {
            color: rgba(107, 140, 190, 0.5);
        }

        @media (max-width: 480px) {
            .login-wrapper { padding: 16px; }
            .login-card { padding: 32px 24px; border-radius: 14px; }
            .logo-container { width: 64px; height: 64px; }
            .login-title { font-size: 17px; }
        }
    </style>
</head>
<body>

    <!-- Fondo -->
    <div class="bg-scene">
        <div class="bg-gradient"></div>
        <div class="grid-lines"></div>
    </div>

    <!-- Tarjeta de login -->
    <div class="login-wrapper">
        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="logo-container">
                    <img src="{{ asset('images/mascota-ia.png') }}" alt="iCompras360" class="logo-img">
                </div>
                <h1 class="login-title">Panel Administrativo</h1>
                <p class="login-subtitle">iCompras360 — Acceso al sistema</p>
            </div>

            <div class="divider"></div>

            <!-- Errores -->
            @if ($errors->any())
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>{{ $errors->first('email') }}</span>
                </div>
            @endif

            <!-- Formulario -->
            <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <div class="input-wrapper">
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            placeholder="correo@empresa.com"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                        >
                        <i class="bi bi-envelope input-icon"></i>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            placeholder="••••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <i class="bi bi-lock input-icon"></i>
                        <button type="button" class="toggle-password" id="togglePass" title="Mostrar/ocultar contraseña">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Botón -->
                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="btn-text"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</span>
                    <span class="btn-spinner"><span class="spinner-icon"></span></span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <span>&copy; {{ date('Y') }} iCompras360</span>
            </div>
        </div>
    </div>

    <script>
        // Toggle mostrar/ocultar contraseña
        document.getElementById('togglePass').addEventListener('click', function() {
            var input = document.getElementById('password');
            var icon  = document.getElementById('toggleIcon');
            var isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
        });

        // Loading en submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('btnLogin').classList.add('loading');
        });
    </script>
</body>
</html>
