<?php
require_once __DIR__ . '/config/Conexion.php';
require_once __DIR__ . '/controllers/logincontroller.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$route = $_GET['route'] ?? '';

// ¿Este equipo está bloqueado por intentos fallidos?
$bloqueo_segundos = 0;
if ($route === '') {
    $intentosLogin = new IntentosLoginModel($conexion);
    $estadoLogin = $intentosLogin->estado();
    if ($estadoLogin['bloqueado']) {
        $bloqueo_segundos = $estadoLogin['segundos'];
    }
}
if ($route === 'login') {
    $auth = new AuthController($conexion);
    $auth->login();
    exit;
}

if ($route === 'logout') {
    $auth = new AuthController($conexion);
    $auth->logout(); 
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script>if (window.self !== window.top) { window.top.location.href = window.location.href; }</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Bodega Municipal - SIGEM</title>

    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/index.css?v=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .lock-overlay { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center;
                        background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(5px); }
        .lock-card { background: #fff; width: 92%; max-width: 440px; border-radius: 16px; padding: 32px 28px; text-align: center;
                     box-shadow: 0 20px 50px rgba(0,0,0,0.35); border-top: 6px solid #E53E3E; }
        .lock-icon { width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 50%; background: #FFF5F5; color: #E53E3E;
                     display: flex; align-items: center; justify-content: center; font-size: 1.7rem; }
        .lock-card h2 { font-size: 1.25rem; font-weight: 800; color: #0F172A; margin-bottom: 8px; }
        .lock-card p { font-size: 0.88rem; color: #475569; margin-bottom: 14px; }
        .lock-timer { font-size: 3rem; font-weight: 800; letter-spacing: 3px; color: #C53030; font-variant-numeric: tabular-nums; margin-bottom: 8px; }
        .lock-card small { color: #94A3B8; font-size: 0.75rem; }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Panel Izquierdo (Imagen de fondo + Logo SIGEM) -->
        <div class="left-panel">
            <div class="overlay"></div>
            <div class="logo-container">
                <img src="assets/img/logo SIGEM.png" alt="SIGEM Logo" class="sigem-logo">
            </div>
            <!-- Franjas decorativas inferiores -->
            <div class="bottom-shapes">
                <div class="shape shape-teal"></div>
                <div class="shape shape-brown"></div>
            </div>
        </div>

        <!-- Panel Derecho (Formulario de Login) -->
        <div class="right-panel">
            <div class="header-top">
                <div class="system-title">
                    <i class="fa-solid fa-warehouse icon-building"></i>
                    <span>Sistema de Bodega Municipal</span>
                </div>
                <div class="top-pills">
                    <span class="pill pill-teal"></span>
                    <span class="pill pill-brown"></span>
                </div>
            </div>

            <div class="login-content">
                <h1>Bienvenido</h1>
                <p class="subtitle">Inicia sesión para acceder al sistema<br>de la Bodega Municipal.</p>

                <div id="alertContainer">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 text-center" role="alert" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close py-2 px-3" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                </div>

                <form action="index.php?route=login" method="POST" class="login-form" id="loginForm">

                    <div class="form-field">
                        <label for="usuario">Usuario</label>
                        <div class="input-field">
                            <div class="icon-box">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="password">Contraseña</label>
                        <div class="input-field">
                            <div class="icon-box">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                            <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember" checked>
                            Recordar usuario
                        </label>
                        <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        Iniciar sesión
                    </button>

                </form>

                <div class="footer-secure">
                    <div class="divider"></div>
                    <div class="shield-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Acceso seguro</span>
                    </div>
                    <p class="footer-tagline">Tu municipio, nuestros recursos</p>
                </div>
            </div>

            <!-- Franja decorativa inferior derecha -->
            <div class="right-bottom-shape"></div>
        </div>

        <?php if ($bloqueo_segundos > 0): ?>
        <!-- Capa de bloqueo por exceso de intentos -->
        <div id="lockOverlay" class="lock-overlay">
            <div class="lock-card">
                <div class="lock-icon"><i class="fa-solid fa-lock"></i></div>
                <h2>Acceso bloqueado temporalmente</h2>
                <p>Superaste el número de intentos permitidos (3). Por seguridad, el inicio de sesión se habilitará de nuevo en:</p>
                <div class="lock-timer" id="lockTimer">--:--</div>
                <small>No cierres esta ventana: el acceso se reactivará automáticamente.</small>
            </div>
        </div>
        <?php endif; ?>

        <!-- Capa Overlay para Animación de Validación -->
        <div id="loaderOverlay" class="loader-overlay hidden">
            <div class="spinner" id="loaderSpinner"></div>
            <i class="fa-solid fa-circle-check check-icon hidden" id="checkIcon"></i>
            <p id="loaderText">Verificando credenciales...</p>
        </div>
    </div>

    <!-- Scripts de Interacción -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($bloqueo_segundos > 0): ?>
    <script>
    // Cronómetro del bloqueo: al llegar a 00:00 se quita la capa y se reactiva el login
    (function () {
        let restante = <?php echo (int) $bloqueo_segundos; ?>;
        const timer = document.getElementById('lockTimer');
        function pintar() {
            const m = String(Math.floor(restante / 60)).padStart(2, '0');
            const s = String(restante % 60).padStart(2, '0');
            timer.textContent = m + ':' + s;
        }
        pintar();
        const intervalo = setInterval(function () {
            restante--;
            if (restante <= 0) {
                clearInterval(intervalo);
                window.location.href = 'index.php';
                return;
            }
            pintar();
        }, 1000);
    })();
    </script>
    <?php endif; ?>
    <script>
    // Mostrar / Ocultar Contraseña
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    // Envío del login vía AJAX: la animación refleja el resultado real de la validación
    const loginForm     = document.querySelector('#loginForm');
    const loaderOverlay = document.querySelector('#loaderOverlay');
    const loaderSpinner = document.querySelector('#loaderSpinner');
    const checkIcon      = document.querySelector('#checkIcon');
    const loaderText     = document.querySelector('#loaderText');
    const alertContainer = document.querySelector('#alertContainer');
    const submitBtn       = loginForm.querySelector('.btn-submit');

    function mostrarAlerta(mensaje) {
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show py-2 px-3 text-center" role="alert" style="font-size: 0.88rem;">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                ${mensaje}
                <button type="button" class="btn-close py-2 px-3" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    function mostrarLoader() {
        alertContainer.innerHTML = '';
        loaderSpinner.classList.remove('hidden');
        checkIcon.classList.add('hidden');
        loaderText.textContent = 'Verificando credenciales...';
        loaderOverlay.classList.remove('hidden');
        submitBtn.disabled = true;
    }

    function ocultarLoader() {
        loaderOverlay.classList.add('hidden');
        submitBtn.disabled = false;
    }

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        mostrarLoader();

        fetch('index.php?route=login', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(loginForm)
        })
        .then(function (respuesta) { return respuesta.json(); })
        .then(function (data) {
            if (data.success) {
                loaderSpinner.classList.add('hidden');
                checkIcon.classList.remove('hidden');
                loaderText.textContent = data.message || '¡Acceso concedido!';

                setTimeout(function () {
                    window.location.href = data.redirect || 'views/Dashboard.php';
                }, 800);
            } else {
                setTimeout(function () {
                    window.location.href = 'index.php';
                }, 900);
            }
        })
        .catch(function () {
            ocultarLoader();
            mostrarAlerta('No se pudo conectar con el servidor. Intenta de nuevo.');
        });
    });
    </script>
</body>
</html>