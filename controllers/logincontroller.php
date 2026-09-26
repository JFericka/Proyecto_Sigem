<?php
// controllers/login.php  (clase AuthController)

require_once __DIR__ . '/../models/LoginModel.php';
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
                $usuarioModel->actualizarPassword(
                    (int) $user['id_usuario'],
                    password_hash($passwordInput, PASSWORD_DEFAULT)
                );
            }
        }

        if (!$user || !$passwordValida) {
            $this->responderJson(false, "Usuario o contraseña incorrecta.");
        }

        if ((int) $user['activo'] !== 1) {
            $this->responderJson(false, "Tu cuenta está inactiva. Contacta al administrador.");
        }

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

    private function responderJson(bool $success, string $mensaje, ?string $redirect = null): void
    {
        if (!$success) {
            $_SESSION['error'] = $mensaje;
        }

        echo json_encode([
            'success'  => $success,
            'message'  => $mensaje,
            'redirect' => $redirect,
        ]);
        exit;
    }
}