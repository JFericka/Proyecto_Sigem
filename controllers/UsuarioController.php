<?php
require_once __DIR__ . '/../config/Auth.php';
auth_requerir('usuarios');
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/PermisoModel.php';

$usuarioModel = new UsuarioModel($conexion);
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'guardar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cedula = trim($_POST['cedula'] ?? '');
            $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
            $clave = trim($_POST['clave'] ?? '');
            $rol = $_POST['rol'] ?? '';

            if (count($usuarioModel->obtenerTodos()) >= UsuarioModel::MAX_USUARIOS) {
                header("Location: ../views/Usuarios.php?mensaje=limite_usuarios");
                exit();
            }

            if (!empty($cedula) && !empty($nombre_usuario) && !empty($clave) && !empty($rol)) {
                $clave_hash = SIGEM_PASSWORD_PLANO ? $clave : password_hash($clave, PASSWORD_DEFAULT);
                $usuarioModel->guardar($cedula, $nombre_usuario, $clave_hash, $rol);
            }
        }
        header("Location: ../views/Usuarios.php");
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
                
                // 2. Enviamos el parámetro $activo al modelo según si cambió o no la contraseña
                if (!empty($clave)) {
                    $clave_hash = SIGEM_PASSWORD_PLANO ? $clave : password_hash($clave, PASSWORD_DEFAULT);
                    $resultado  = $usuarioModel->actualizarConClave($id_usuario, $cedula, $nombre_usuario, $clave_hash, $rol, $activo);
                } else {
                    $resultado  = $usuarioModel->actualizarSinClave($id_usuario, $cedula, $nombre_usuario, $rol, $activo);
                }

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
    
    case 'permisos':
        // El Administrador concede o quita acceso extra a Entradas / Salidas
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = (int) ($_POST['id_usuario'] ?? 0);
            $usr = $usuarioModel->obtenerPorId($id_usuario);
            if ($usr && $usr['rol'] !== 'Administrador') {
                $permitidos = array_keys(PermisoModel::MODULOS);
                $elegidos = array_values(array_intersect((array) ($_POST['permisos'] ?? []), $permitidos));
                // Lo que el rol ya tiene de base no se guarda como extra
                $elegidos = array_values(array_filter($elegidos, function ($p) use ($usr) {
                    return !in_array($usr['rol'], SIGEM_PERMISOS[$p], true);
                }));
                (new PermisoModel($conexion))->guardar($id_usuario, $elegidos);
                header("Location: ../views/Usuarios.php?mensaje=permisos_ok");
                exit();
            }
        }
        header("Location: ../views/Usuarios.php");
        exit();

    case 'eliminar':
        $id_usuario = $_GET['id_usuario'] ?? null;
        if ($id_usuario && (int) $id_usuario === auth_id()) {
            // Un administrador no puede eliminar su propia cuenta
            header("Location: ../views/Usuarios.php?mensaje=no_autoeliminar");
            exit();
        }
        if ($id_usuario) {
            try {
                $usuarioModel->eliminar($id_usuario);
            } catch (mysqli_sql_exception $e) {
                // El usuario ya tiene entradas/salidas registradas: no se puede borrar
                header("Location: ../views/Usuarios.php?mensaje=con_movimientos");
                exit();
            }
        }
        header("Location: ../views/Usuarios.php?mensaje=eliminado");
        exit();

    default:
        header("Location: ../views/Usuarios.php");
        exit();
}
