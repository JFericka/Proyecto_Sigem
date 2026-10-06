<?php
require_once '../config/Conexion.php';
require_once '../models/UsuarioModel.php';

$usuarioModel = new UsuarioModel($conexion);
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cedula = trim($_POST['cedula'] ?? '');
            $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
            $clave = trim($_POST['clave'] ?? '');
            $rol = $_POST['rol'] ?? '';

            if (!empty($cedula) && !empty($nombre_usuario) && !empty($clave) && !empty($rol)) {
                $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
                $usuarioModel->guardar($cedula, $nombre_usuario, $clave_hash, $rol);
            }
        }
        header("Location: ../views/usuarios.php");
        exit();

   case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario     = $_POST['id_usuario'] ?? null;
            $cedula         = trim($_POST['cedula'] ?? '');
            $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
            $clave          = $_POST['clave'] ?? '';
            $rol            = $_POST['rol'] ?? '';
            $activo         = $_POST['activo'] ?? 1; //  1.Capturamos el estado activo/inactivo

            if ($id_usuario && !empty($cedula) && !empty($nombre_usuario) && !empty($rol)) {
                
            //si el usuario ingreso un nueva clave se aplica el hash, de lo contrario se deja en null para no actualizar la clave
            $clave_hash = !empty($clave) ? password_hash($clave, PASSWORD_DEFAULT) : null;

            //llamamos a la funcion del modelo
            $resultado = $usuarioModel->actualizar($id_usuario, $cedula, $nombre_usuario, $rol, $activo, $clave_hash);

                // 3. Evaluamos la respuesta para redirigir adecuadamente
                if ($resultado === true || (is_numeric($resultado) && $resultado >= 0)) {
                    header("Location: ../views/Usuarios.php?mensaje=actualizado");
                    exit();
                } else {
                    die("Error al actualizar en la BD: " . var_export($resultado, true));
                }

            } else {
                die("Faltan datos obligatorios para la actualización. ID: " . var_export($id_usuario, true));
            }
        }
        header("Location: ../views/Usuarios.php");
    exit();
    
    case 'eliminar':
        $id_usuario = $_GET['id_usuario'] ?? null;
        if ($id_usuario) {
            $usuarioModel->eliminar($id_usuario);
        }
        header("Location: ../views/Usuarios.php?mensaje=eliminado");
        exit();

    default:
        header("Location: ../views/Usuarios.php");
        exit();
}
