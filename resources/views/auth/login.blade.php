<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de administración IA - iCompras360">
    <title>Iniciar Sesión — Panel IA iCompras360</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-deep:      #040d1a;
            --bg-dark:      #071428;
            --bg-card:      rgba(10, 25, 55, 0.85);
            --border:       rgba(56, 120, 220, 0.25);
            --border-focus: rgba(56, 140, 255, 0.7);
            --blue-primary: #1a6ef7;
            --blue-light:   #3b8aff;
            --blue-glow:    rgba(26, 110, 247, 0.35);
            --text-primary: #e8f0ff;
            --text-muted:   #6b8cbe;
            --text-label:   #94b3d8;
            --error:        #ff4e6a;
            --error-bg:     rgba(255, 78, 106, 0.1);
            --success:      #00d4a0;
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

        /* Fondo animado con partículas y gradiente */
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
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(26, 110, 247, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 90%, rgba(0, 212, 160, 0.08) 0%, transparent 60%),
                linear-gradient(180deg, #040d1a 0%, #071428 100%);
        }

        .grid-lines {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(56, 120, 220, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56, 120, 220, 0.06) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: float 8s ease-in-out infinite;
        }

        .orb-1 {
            width: 500px; height: 500px;
            background: rgba(26, 110, 247, 0.12);
            top: -150px; left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 350px; height: 350px;
            background: rgba(0, 212, 160, 0.07);
            bottom: -100px; right: -80px;
            animation-delay: -4s;
        }

        .orb-3 {
            width: 250px; height: 250px;
            background: rgba(100, 160, 255, 0.08);
            top: 40%; right: 15%;
            animation-delay: -2s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        /* Partículas flotantes */
        .particles {
            position: absolute;
            inset: 0;
        }

        .particle {
            position: absolute;
            width: 2px; height: 2px;
            background: var(--blue-light);
            border-radius: 50%;
            opacity: 0;
            animation: rise linear infinite;
        }

        @keyframes rise {
            0%   { transform: translateY(100vh) translateX(0); opacity: 0; }
            10%  { opacity: 0.6; }
            90%  { opacity: 0.3; }
            100% { transform: translateY(-10vh) translateX(30px); opacity: 0; }
        }

        /* Contenedor del login */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 24px;
        }

        /* Card glassmorphism */
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px 44px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow:
                0 0 0 1px rgba(56, 120, 220, 0.1),
                0 32px 80px rgba(4, 13, 26, 0.7),
                0 0 60px rgba(26, 110, 247, 0.06);
            animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Logo / Header */
        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 90px; height: 90px;
            background: white;
            border-radius: 50%;
            margin-bottom: 16px;
            box-shadow: 0 8px 40px rgba(26, 110, 247, 0.4), 0 0 0 4px rgba(56,120,220,0.15);
            position: relative;
            overflow: hidden;
            padding: 6px;
        }

        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .brand-logo {
            display: block;
            margin: 0 auto 8px;
            height: 36px;
            object-fit: contain;
            filter: brightness(0) invert(1) opacity(0.9);
        }

        .login-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        /* Badge IA */
        .ia-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(26, 110, 247, 0.12);
            border: 1px solid rgba(26, 110, 247, 0.3);
            border-radius: 100px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            color: var(--blue-light);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .ia-badge-dot {
            width: 6px; height: 6px;
            background: var(--success);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--success);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.8); }
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
            letter-spacing: 0.5px;
            text-transform: uppercase;
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
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            background: rgba(7, 20, 40, 0.6);
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
            color: rgba(107, 140, 190, 0.5);
        }

        .form-input:focus {
            border-color: var(--border-focus);
            background: rgba(10, 28, 60, 0.8);
            box-shadow: 0 0 0 3px rgba(56, 140, 255, 0.12);
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

        /* Recuérdame */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .checkbox-custom {
            width: 18px; height: 18px;
            appearance: none;
            -webkit-appearance: none;
            background: rgba(7, 20, 40, 0.6);
            border: 1px solid var(--border);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            position: relative;
        }

        .checkbox-custom:checked {
            background: var(--blue-primary);
            border-color: var(--blue-primary);
            box-shadow: 0 0 12px var(--blue-glow);
        }

        .checkbox-custom:checked::after {
            content: '✓';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            font-weight: 700;
        }

        .remember-label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        /* Botón */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--blue-primary) 0%, #0a3db5 100%);
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
            box-shadow: 0 8px 32px var(--blue-glow);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.25s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(26, 110, 247, 0.5);
        }

        .btn-login:hover::before {
            opacity: 1;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login .btn-text { display: inline-block; }
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
            margin-top: 28px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .login-footer span {
            color: rgba(107, 140, 190, 0.5);
        }
    </style>
</head>
<body>

    <!-- Fondo animado -->
    <div class="bg-scene">
        <div class="bg-gradient"></div>
        <div class="grid-lines"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="particles" id="particles"></div>
    </div>

    <!-- Tarjeta de login -->
    <div class="login-wrapper">
        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="logo-container">
                    <img src="/panel-ia/public/images/mascota-ia.png" alt="Mascota IA" class="logo-img">
                </div>
                <h1 class="login-title">Centro de Control IA</h1>
                <p class="login-subtitle">Panel de Inteligencia Artificial</p>
                <div class="ia-badge">
                    <span class="ia-badge-dot"></span>
                    Sistema Activo
                </div>
            </div>

            <div class="divider"></div>

            <!-- Errores -->
            @if ($errors->any())
                <div class="error-message">
                    <span>⚠️</span>
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
                        <span class="input-icon">✉️</span>
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
                        <span class="input-icon">🔒</span>
                        <button type="button" class="toggle-password" id="togglePass" title="Mostrar/ocultar contraseña">
                            👁️
                        </button>
                    </div>
                </div>


                <!-- Botón -->
                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="btn-text">Iniciar Sesión</span>
                    <span class="btn-spinner"><span class="spinner-icon"></span></span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <span>© 2025 iCompras360 · Panel de Inteligencia Artificial</span>
            </div>
        </div>
    </div>

    <script>
        // Generar partículas flotantes
        (function() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 25; i++) {
                const p = document.createElement('div');
                p.classList.add('particle');
                p.style.left = Math.random() * 100 + 'vw';
                p.style.animationDuration = (8 + Math.random() * 12) + 's';
                p.style.animationDelay = (Math.random() * 15) + 's';
                p.style.width = p.style.height = (1 + Math.random() * 2) + 'px';
                p.style.opacity = Math.random() * 0.5;
                container.appendChild(p);
            }
        })();

        // Toggle mostrar/ocultar contraseña
        document.getElementById('togglePass').addEventListener('click', function() {
            const input = document.getElementById('password');
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            this.textContent = isPass ? '🙈' : '👁️';
        });

        // Loading en submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('btnLogin').classList.add('loading');
        });
    </script>
</body>
</html>
