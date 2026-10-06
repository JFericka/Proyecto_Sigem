<?php
// controllers/login.php — AuthController

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
            $this->responder(false, 'Método no permitido.');
        }

        $usuario  = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($usuario === '' || $password === '') {
            $this->responder(false, 'Todos los campos son obligatorios.');
        }

        $model = new UsuarioModel($this->conn);

        try {
            $user = $model->getUsuarioPorNombre($usuario); // consulta preparada, sin concatenar SQL
        } catch (RuntimeException $e) {
            error_log($e->getMessage());
            $this->responder(false, 'Ocurrió un error en el sistema. Intenta más tarde.');
        }

        
        if (!$user) {
            $this->responder(false, 'Usuario Incorrecto.');
        }

        if (!$this->passwordValida($password, $user, $model)) {
            $this->responder(false, 'Contraseña Incorrecta.');
        }

        if ((int) $user['activo'] !== 1) {
            $this->responder(false, 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        session_regenerate_id(true); // previene session fixation
        $_SESSION['id_usuario']     = $user['id_usuario'];
        $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
        $_SESSION['rol']            = $user['rol'];

        $this->responder(true, "Bienvenido, {$user['nombre_usuario']}.", 'views/Dashboard.php');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }

    /** Verifica el password; migra automáticamente hashes antiguos en texto plano. */
    private function passwordValida(string $input, array $user, UsuarioModel $model): bool
    {
        if (password_get_info($user['password'])['algo'] !== null) {
            return password_verify($input, $user['password']);
        }

        if (hash_equals((string) $user['password'], $input)) {
            $model->actualizarPassword((int) $user['id_usuario'], password_hash($input, PASSWORD_DEFAULT));
            return true;
        }

        return false;
    }

    private function responder(bool $success, string $mensaje, ?string $redirect = null): never
    {
        if (!$success) {
            $_SESSION['error'] = $mensaje;
        }

        echo json_encode(['success' => $success, 'message' => $mensaje, 'redirect' => $redirect]);
        exit;
    }
}