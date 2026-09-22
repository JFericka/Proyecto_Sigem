<?php
require_once __DIR__ . '/config/Conexion.php';
require_once __DIR__ . '/controllers/login.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$route = $_GET['route'] ?? '';
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Bodega Municipal - SIGEM</title>

    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/index.css?v=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        <!-- Capa Overlay para Animación de Validación -->
        <div id="loaderOverlay" class="loader-overlay hidden">
            <div class="spinner" id="loaderSpinner"></div>
            <i class="fa-solid fa-circle-check check-icon hidden" id="checkIcon"></i>
            <p id="loaderText">Verificando credenciales...</p>
        </div>
    </div>

    <!-- Scripts de Interacción -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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