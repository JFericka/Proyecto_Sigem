<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Bodega Municipal - SIGEM</title>

    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/index.css?v=1.0">
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

                <form action="controllers/login.php" method="POST" class="login-form" id="loginForm">
                    
                    <div class="input-group">
                        <label for="usuario">Usuario</label>
                        <div class="input-field">
                            <div class="icon-box">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required autocomplete="off">
                        </div>
                    </div>

                    <div class="input-group">
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

        <!-- Capa Overlay para Animación de Entrada -->
        <div id="loaderOverlay" class="loader-overlay hidden">
            <div class="spinner" id="loaderSpinner"></div>
            <i class="fa-solid fa-circle-check check-icon hidden" id="checkIcon"></i>
            <p id="loaderText">Verificando credenciales...</p>
        </div>
    </div>

    <!-- Scripts de Interacción -->
    <script>
        // Ocultar / Mostrar Contraseña
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Animación al enviar formulario
        const loginForm = document.querySelector('#loginForm');
        const loaderOverlay = document.querySelector('#loaderOverlay');
        const loaderSpinner = document.querySelector('#loaderSpinner');
        const checkIcon = document.querySelector('#checkIcon');
        const loaderText = document.querySelector('#loaderText');

        loginForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Detiene envío inmediato para mostrar la animación

            loaderOverlay.classList.remove('hidden');

            setTimeout(() => {
                loaderSpinner.classList.add('hidden');
                checkIcon.classList.remove('hidden');
                loaderText.textContent = "¡Acceso Concedido!";

                setTimeout(() => {
                    loginForm.submit(); // Realiza el POST real hacia controllers/login.php
                }, 800);
            }, 1000);
        });
    </script>
</body>
</html>