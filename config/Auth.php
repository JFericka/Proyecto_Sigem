<?php
// config/Auth.php
// Sesión, roles y permisos del sistema. Se incluye al inicio de cada vista/controlador protegido.

require_once __DIR__ . '/Helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Managua');

// Evita que el navegador muestre páginas privadas desde caché después de cerrar sesión
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
}

const SIGEM_ROLES = [
    'Administrador'     => 'Administrador',
    'Operador_Entradas' => 'Operador de Entradas',
    'Operador_Salidas'  => 'Operador de Salidas',
];

// Qué roles pueden usar cada módulo / función
const SIGEM_PERMISOS = [
    'usuarios'           => ['Administrador'],
    'entradas'           => ['Administrador', 'Operador_Entradas'],
    'salidas'            => ['Administrador', 'Operador_Salidas'],
    'inventario'         => ['Administrador', 'Operador_Entradas', 'Operador_Salidas'],
    'baja'               => ['Administrador'],
    'reportes'           => ['Administrador', 'Operador_Entradas', 'Operador_Salidas'],
    'reporte_inventario' => ['Administrador'],
    'reporte_entradas'   => ['Administrador', 'Operador_Entradas'],
    'reporte_salidas'    => ['Administrador', 'Operador_Salidas'],
    'respaldo'           => ['Administrador'],
];

function auth_logueado(): bool
{
    return !empty($_SESSION['id_usuario']);
}

function auth_id(): int
{
    return (int) ($_SESSION['id_usuario'] ?? 0);
}

function auth_nombre(): string
{
    return (string) ($_SESSION['nombre_usuario'] ?? '');
}

function auth_rol(): string
{
    return (string) ($_SESSION['rol'] ?? '');
}

function auth_nombre_rol(): string
{
    return SIGEM_ROLES[auth_rol()] ?? auth_rol();
}

// Permisos extra concedidos por el Administrador (se leen de la base en cada petición, así se aplican al instante)
function auth_extras(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    if (!auth_logueado()) {
        return $cache;
    }
    try {
        global $conexion;
        if (!isset($conexion)) {
            require_once __DIR__ . '/Conexion.php';
        }
        $id = auth_id();
        $stmt = $conexion->prepare("SELECT permiso FROM permisos_extra WHERE id_usuario = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($f = $res->fetch_assoc()) {
            $cache[] = $f['permiso'];
            $cache[] = 'reporte_' . $f['permiso'];   // el permiso de Entradas/Salidas incluye su reporte
        }
        $stmt->close();
    } catch (Throwable $e) {
        $cache = [];   // si la tabla aún no existe, simplemente no hay permisos extra
    }
    return $cache;
}

function auth_puede(string $permiso): bool
{
    if (!auth_logueado()) {
        return false;
    }
    if (in_array(auth_rol(), SIGEM_PERMISOS[$permiso] ?? [], true)) {
        return true;
    }
    return in_array($permiso, auth_extras(), true);
}

// Exige sesión iniciada y, si se indica, el permiso. $modo 'json' responde con código HTTP en vez de redirigir.
function auth_requerir(?string $permiso = null, string $modo = 'html'): void
{
    if (!auth_logueado()) {
        if ($modo === 'json') {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Sesión no válida.']);
        } else {
            header('Location: ../index.php');
        }
        exit;
    }

    if ($permiso !== null && !auth_puede($permiso)) {
        if ($modo === 'json') {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'No tiene permiso para esta acción.']);
        } else {
            header('Location: ../views/Dashboard.php?msg=sin_permiso');
        }
        exit;
    }
}

// Datos para el "pill" de usuario de las vistas
function auth_inicial(): string
{
    return mb_strtoupper(mb_substr(auth_nombre() !== '' ? auth_nombre() : '?', 0, 1));
}
