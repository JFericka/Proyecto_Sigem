<?php
// controllers/login.php  (clase AuthController)

require_once __DIR__ . '/../models/LoginModel.php';
require_once __DIR__ . '/../models/IntentosLoginModel.php';
require_once __DIR__ . '/../config/Conexion.php';

class AuthController
{
    private mysqli $conn;

    public function __construct(mysqli $conexion)
    {
        $this->conn = $conexion;
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(false, "Método no permitido.");
        }

        // Bloqueo por demasiados intentos fallidos (se revisa antes de mirar las credenciales)
        $intentos = new IntentosLoginModel($this->conn);
        $estadoIntentos = $intentos->estado();
        if ($estadoIntentos['bloqueado']) {
            $this->responderJson(false, 'Acceso bloqueado temporalmente por exceder los intentos permitidos.', null, true);
        }

        $usuarioInput  = trim($_POST['usuario'] ?? '');
        $passwordInput = trim($_POST['password'] ?? '');

        if ($usuarioInput === '' || $passwordInput === '') {
            $this->responderJson(false, "Todos los campos son obligatorios.");
        }

        $usuarioModel = new UsuarioModel($this->conn);

        try {
            $user = $usuarioModel->getUsuarioPorNombre($usuarioInput);
        } catch (RuntimeException $e) {
            error_log($e->getMessage());
            $this->responderJson(false, "Ocurrió un error en el sistema. Intenta más tarde.");
            return; 
        }

        $passwordValida = false;

        if ($user) {
            $infoHash = password_get_info($user['password']);

            if ($infoHash['algo'] !== null) {
                // Contraseña ya almacenada como hash seguro
                $passwordValida = password_verify($passwordInput, $user['password']);
            } elseif (hash_equals((string) $user['password'], $passwordInput)) {

                $passwordValida = true;
                // Solo se cifra la contraseña si el modo de pruebas (texto plano) está desactivado
                if (!SIGEM_PASSWORD_PLANO) {
                    $usuarioModel->actualizarPassword(
                        (int) $user['id_usuario'],
                        password_hash($passwordInput, PASSWORD_DEFAULT)
                    );
                }
            }
        }

        if (!$user || !$passwordValida) {
            $nuevo = $intentos->registrarFallo();
            if ($nuevo['bloqueado']) {
                $this->responderJson(false, 'Superaste el número de intentos permitidos. Acceso bloqueado por '
                    . IntentosLoginModel::MINUTOS_BLOQUEO . ' minutos.', null, true);
            }
            $r = $nuevo['restantes'];
            $this->responderJson(false, 'Usuario o contraseña incorrecta. Te ' . ($r === 1 ? 'queda 1 intento' : "quedan {$r} intentos") . '.');
        }

        if ((int) $user['activo'] !== 1) {
            $this->responderJson(false, "Tu cuenta está inactiva. Contacta al administrador.");
        }

        $intentos->limpiar();

        // Previene session fixation
        session_regenerate_id(true);

        $_SESSION['id_usuario']     = $user['id_usuario'];
        $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
        $_SESSION['rol']            = $user['rol'];

        $this->responderJson(true, "Bienvenido, {$user['nombre_usuario']}.", 'views/Dashboard.php');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        header("Location: index.php");
        exit;
    }

    private function responderJson(bool $success, string $mensaje, ?string $redirect = null, bool $bloqueado = false): void
    {
        if (!$success && !$bloqueado) {
            $_SESSION['error'] = $mensaje;
        }

        echo json_encode([
            'success'  => $success,
            'message'  => $mensaje,
            'redirect' => $redirect,
            'bloqueado' => $bloqueado,
        ]);
        exit;
    }
}