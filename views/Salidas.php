<?php
// views/Salidas.php
require_once __DIR__ . '/../config/Auth.php';
auth_requerir('salidas');

$action = $_GET['action'] ?? 'listar';
$id_voucher = (int) ($_GET['id'] ?? 0);

// Filtro opcional por día (viene del calendario del Dashboard)
$fecha_filtro = null;
if (!empty($_GET['fecha'])) {
    $df = DateTime::createFromFormat('Y-m-d', (string) $_GET['fecha']);
    if ($df && $df->format('Y-m-d') === $_GET['fecha']) {
        $fecha_filtro = $_GET['fecha'];
    }
}

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../controllers/SalidaController.php';

$controller = new SalidaController($conexion);

$error_form = null;
$old = [];

// Quitar un lugar guardado (petición AJAX desde "Administrar lugares")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_destino') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $controller->eliminarDestino((int) ($_POST['id_destino'] ?? 0))]);
    exit;
}

// Registro de una nueva salida (envío del formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controller->registrar($_POST, auth_id(), auth_nombre());
    if ($resultado['ok']) {
        header('Location: Salidas.php?action=voucher&id=' . $resultado['id']);
        exit;
    }
    $error_form = $resultado['error'];
    $old = $_POST;
    $action = 'agregar';
}

$salidas_db         = [];
$productos_catalogo = [];
$destinos_db        = [];
$voucher_actual     = null;

if ($action === 'agregar') {
    $productos_catalogo = $controller->catalogo();
    $destinos_db        = $controller->destinos();
} elseif ($action === 'voucher') {
    $voucher_actual = $controller->obtener($id_voucher);
    if (!$voucher_actual) {
        header('Location: Salidas.php');
        exit;
    }
} else {
    $salidas_db = $controller->listar($fecha_filtro);
}

function old_val($campo)
{
    global $old;
    return htmlspecialchars((string) ($old[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
}

$estados_salida = [
    'entregado' => ['En préstamo (pendiente de devolver)', 'status-bajo'],
    'parcial'   => ['Devolución parcial', 'status-bajo'],
    'devuelto'  => ['Devuelto', 'status-optimo'],
    'vencido'   => ['Vencido (sin devolver)', 'status-agotado'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Salidas y Vouchers - SIGEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<<<<<<< HEAD
=======
    <!-- Bootstrap 5.3 (CSS) + capa de compatibilidad para conservar el diseño propio -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap-compat.css">
    <script src="../assets/js/efecto.js"></script>
>>>>>>> dcbe80f0b00f2448b1923b4c126b94b010c240aa
    <style>
        :root {
            --bg-page: #F2F0EF;          
            --card-bg: #FFFFFF;
            --border-light: #BBBDBC;     
            --text-main: #1E293B;
            --text-muted: #64748B;
            --color-primary: #245F73;    
            --color-salida: #A0522D;     
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-page); color: var(--text-main); height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        .navbar { background-color: var(--card-bg); border-bottom: 1px solid var(--border-light); padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.95rem; }
        .nav-brand-icon { background: #E2E8F0; color: var(--color-primary); width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; }

        .user-pill { display: flex; align-items: center; gap: 10px; }
        .user-avatar { background: var(--color-salida); color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
        .user-meta span { display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-primary); }
        .user-meta small { font-size: 0.68rem; color: var(--text-muted); }

        .page-wrapper { flex-grow: 1; display: flex; align-items: center; justify-content: center; padding: 12px 20px; background: radial-gradient(circle, rgba(36, 95, 115, 0.08) 0%, rgba(187, 189, 188, 0.15) 100%); }
        .main-container { max-width: 1420px; width: 98%; height: 90vh; background: var(--card-bg); border: 1px solid var(--border-light); border-radius: 12px; padding: 20px 24px; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(36, 95, 115, 0.08); overflow: hidden; }

        .module-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--bg-page); padding-bottom: 14px; margin-bottom: 16px; flex-shrink: 0; }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .btn-back { background-color: #E2E8F0; color: var(--text-main); border: none; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; cursor: pointer; }
        .btn-back:hover { background-color: var(--color-primary); color: white; }
        .module-title h1 { font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .module-title h1 i { color: var(--color-salida); }
        .module-title p { color: var(--text-muted); font-size: 0.8rem; }

        .btn-add { background-color: var(--color-salida); color: white; border: none; padding: 9px 16px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        
        .table-responsive { flex-grow: 1; overflow-y: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #FAFAFA; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; font-weight: 700; padding: 12px 16px; border-bottom: 1px solid var(--border-light); position: sticky; top: 0; z-index: 10; }
        td { padding: 13px 16px; font-size: 0.87rem; border-bottom: 1px solid #F1F5F9; }
        tr:hover { background-color: #F8FAFC; }

        .type-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
        .type-consumo { background: #F3E8FF; color: #7C3AED; }
        .type-prestamo { background: #FEF3C7; color: #D97706; }
        
        .btn-voucher { background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }

        .form-wrapper-scroll { flex-grow: 1; overflow-y: auto; padding: 5px 10px; }
        .form-container-structured { max-width: 950px; margin: 0 auto; background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 10px; padding: 24px 30px; }
        .form-section-title { font-size: 0.85rem; font-weight: 700; color: var(--color-primary); text-transform: uppercase; border-bottom: 1px solid #E2E8F0; padding-bottom: 6px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .form-section-title:not(:first-child) { margin-top: 24px; }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; }

        .input-with-icon { position: relative; display: flex; align-items: center; }
        .input-with-icon i { position: absolute; left: 14px; color: var(--text-muted); font-size: 0.85rem; }
        .form-control { width: 100%; padding: 10px 14px 10px 38px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.88rem; outline: none; background-color: #FAFAFA; transition: all 0.2s; }
        .form-control:focus { border-color: var(--color-primary); background-color: #FFFFFF; box-shadow: 0 0 0 3px rgba(36, 95, 115, 0.1); }
        select.form-control { cursor: pointer; }
        textarea.form-control { padding-left: 14px; height: 70px; resize: none; }

        /* TABLA DINÁMICA CON SELECTOR REAL EN CADA LÍNEA */
        .prod-table { width: 100%; border: 1px solid var(--border-light); border-radius: 8px; overflow: hidden; font-size: 0.82rem; background: #FFFFFF; margin-top: 10px; }
        .prod-table th { background: #F1F5F9; padding: 10px 12px; font-size: 0.7rem; color: var(--text-muted); }
        .prod-table td { padding: 10px 12px; border-bottom: 1px solid #E2E8F0; vertical-align: middle; }

        .btn-add-row { background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; padding: 8px 16px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; }
        .btn-add-row:hover { background: #BAE6FD; }

        .btn-remove-row { background: #FEE2E2; color: #991B1B; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .btn-remove-row:hover { background: #FCA5A5; }

        .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; border-top: 1px solid var(--bg-page); padding-top: 16px; }
        .btn-cancel { background: #E2E8F0; color: var(--text-main); padding: 9px 18px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-save { background: var(--color-salida); color: white; border: none; padding: 9px 20px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-save:hover { opacity: 0.9; }

        /* ESTILOS VOUCHER */
        .voucher-card { max-width: 800px; margin: 0 auto; background: white; border: 1px solid var(--border-light); border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .voucher-header { text-align: center; border-bottom: 2px dashed var(--border-light); padding-bottom: 15px; margin-bottom: 20px; }
        .voucher-header h2 { font-size: 1.15rem; font-weight: 800; color: var(--color-primary); }
        .voucher-header p { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; }
        .voucher-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; font-size: 0.82rem; background: #FAFAFA; padding: 14px; border-radius: 8px; border: 1px solid #E2E8F0; }
        .voucher-info-grid div strong { color: var(--text-muted); display: block; font-size: 0.65rem; text-transform: uppercase; }
        .voucher-footer-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 30px; align-items: center; }
        .stamp-box { border: 2px dashed var(--border-light); border-radius: 8px; height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.65rem; text-transform: uppercase; font-weight: 700; }
        .signature-box { text-align: center; font-size: 0.78rem; }
        .signature-line { border-top: 1px solid var(--text-main); padding-top: 6px; margin-top: 45px; font-weight: 700; }
        .signature-line small { color: var(--text-muted); font-weight: 400; display: block; font-size: 0.68rem; }
        .btn-print { background-color: var(--color-primary); color: white; border: none; padding: 9px 18px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .alert-devolucion { background: #FEF3C7; color: #92400E; padding: 10px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border: 1px solid #FCD34D; }
        .alert-consumo { background: #F3E8FF; color: #6B21A8; padding: 10px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border: 1px solid #D8B4FE; }
            .badge-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; }
        .status-optimo { background: #D1FAE5; color: #065F46; }
        .status-bajo { background: #FEF3C7; color: #92400E; }
        .status-agotado { background: #FEE2E2; color: #991B1B; }
    
        /* Título "Sistema de Bodega Municipal" e icono con el mismo color del botón Salir */
        .nav-brand { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
        .nav-brand-icon { -webkit-text-fill-color: initial; }
        .nav-brand-icon i { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
        /* Fondo con tono suave del color distintivo del módulo */
        .page-wrapper { background: linear-gradient(180deg, #F8F0EA 0%, #EDDDCF 100%); }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
            Sistema de Bodega Municipal
        </div>
        <div class="user-pill">
            <div class="user-avatar"><?php echo htmlspecialchars(auth_inicial()); ?></div>
            <div class="user-meta">
                <span><?php echo htmlspecialchars(auth_nombre()); ?></span>
                <small><?php echo htmlspecialchars(auth_nombre_rol()); ?></small>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <main class="main-container">

            <?php if ($action === 'listar'): ?>
                <!-- VISTA 1: LISTADO DE SALIDAS -->
                <div class="module-header">
                    <div class="header-left">
                        <a href="Dashboard.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-truck-ramp-box"></i> Control de Salidas</h1>
                            <p>Registro de despachos de inventario por consumo interno y préstamos temporales.</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="Salidas.php?action=agregar" class="btn-add"><i class="fa-solid fa-plus-circle"></i> Registrar Salida</a>
                    </div>
                </div>

                <?php if ($fecha_filtro): ?>
                    <div style="background:#EBF8FF;border:1px solid #BEE3F8;color:#2C5282;padding:8px 12px;border-radius:8px;font-size:0.8rem;margin-bottom:10px;">
                        <i class="fa-regular fa-calendar-days"></i> Mostrando solo las salidas del <strong><?php echo date('d/m/Y', strtotime($fecha_filtro)); ?></strong>.
                        <a href="Salidas.php" style="margin-left:8px;font-weight:700;">Ver todas</a>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nº Documento</th>
                                <th>Tipo de Salida</th>
                                <th>Destino</th>
                                <th>Quien Recibe</th>
                                <th>Fecha Salida</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salidas_db as $sal): ?>
                            <tr>
                                <td><strong><?php echo $sal['id_salida']; ?></strong></td>
                                <td><code><?php echo htmlspecialchars($sal['numero_documento']); ?></code></td>
                                <td>
                                    <?php $clase_tipo = ($sal['tipo'] == 'consumo') ? 'type-consumo' : 'type-prestamo'; ?>
                                    <span class="type-badge <?php echo $clase_tipo; ?>"><i class="fa-solid fa-tag"></i> <?php echo ($sal['tipo'] === 'prestamo') ? 'Préstamo' : 'Consumo'; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($sal['nombre_destino']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($sal['nombre_quien_recibe']); ?>
                                    <?php if ($sal['cedula_quien_recibe'] !== '' || $sal['celular_quien_recibe'] !== ''): ?>
                                        <small style="display:block;color:var(--text-muted);font-size:0.7rem;">
                                            <?php echo htmlspecialchars($sal['cedula_quien_recibe']); ?>
                                            <?php echo $sal['celular_quien_recibe'] !== '' ? ' · Cel. ' . htmlspecialchars(fmt_celular($sal['celular_quien_recibe'])) : ''; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $sal['fecha_salida']; ?></td>
                                <td>
                                    <?php if ($sal['tipo'] === 'consumo'): ?>
                                        <span style="font-size:0.75rem;color:var(--text-muted);">No retorna</span>
                                    <?php else: ?>
                                        <?php $est = $estados_salida[$sal['estado']] ?? [$sal['estado'], 'status-optimo']; ?>
                                        <span class="badge-status <?php echo $est[1]; ?>"><?php echo $est[0]; ?></span>
                                        <?php if ($sal['estado'] !== 'devuelto' && $sal['fecha_prevista_devolucion']): ?>
                                            <small style="display:block;color:var(--text-muted);font-size:0.7rem;">Devolver antes del <?php echo date('d/m/Y', strtotime($sal['fecha_prevista_devolucion'])); ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="Salidas.php?action=voucher&id=<?php echo $sal['id_salida']; ?>" class="btn-voucher">
                                        <i class="fa-solid fa-print"></i> Ver Vale
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($salidas_db)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    <?php echo $fecha_filtro ? 'No hay salidas registradas en esa fecha.' : 'Aún no hay salidas registradas.'; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($action === 'agregar'): ?>
                <!-- VISTA 2: FORMULARIO DE NUEVA SALIDA CON SELECTOR REAL Y LIBRE -->
                <div class="module-header" style="margin-bottom: 15px;">
                    <div class="header-left">
                        <a href="Salidas.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-square-plus"></i> Registrar Nueva Salida</h1>
                            <p>Complete la cabecera y seleccione libremente los productos del inventario a despachar.</p>
                        </div>
                    </div>
                </div>

                <div class="form-wrapper-scroll">
                    <div class="form-container-structured">
                        <?php if ($error_form): ?>
                            <div style="background:#FFF5F5;border:1px solid #FEB2B2;color:#C53030;padding:10px 14px;border-radius:8px;font-size:0.85rem;margin-bottom:14px;">
                                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error_form); ?>
                            </div>
                        <?php endif; ?>
                        <form action="Salidas.php?action=agregar" method="POST">
                            
                            <div class="form-section-title"><i class="fa-solid fa-file-invoice"></i> Información General del Despacho</div>
                            
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Número de Documento</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-barcode"></i>
                                        <input type="text" name="numero_documento" class="form-control" placeholder="Ej. SAL-2026-013" value="<?php echo old_val('numero_documento'); ?>" maxlength="50" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Tipo de Salida</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-shuffle"></i>
                                        <select name="tipo" class="form-control" id="tipoSalida" onchange="togglePrestamoFields()" required>
                                            <option value="consumo" <?php echo (($old['tipo'] ?? '') === 'consumo') ? 'selected' : ''; ?>>Consumo (Gasto definitivo / No regresa)</option>
                                            <option value="prestamo" <?php echo (($old['tipo'] ?? '') === 'prestamo') ? 'selected' : ''; ?>>Préstamo (Requiere devolución)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Motivo de Salida</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <input type="text" name="motivo_salida" class="form-control" placeholder="Ej. Reparación y mantenimiento" value="<?php echo old_val('motivo_salida'); ?>" maxlength="255" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Destino Asignado</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <select name="id_destino" id="selectDestino" class="form-control" onchange="toggleDestinoNuevo()" required>
                                            <option value="">Seleccione un destino...</option>
                                            <?php foreach ($destinos_db as $dest): ?>
                                                <option value="<?php echo (int) $dest['id_destino']; ?>" <?php echo ((string) ($old['id_destino'] ?? '') === (string) $dest['id_destino']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dest['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <option value="nuevo" <?php echo (($old['id_destino'] ?? '') === 'nuevo') ? 'selected' : ''; ?>>➕ Escribir un lugar nuevo...</option>
                                        </select>
                                    </div>
                                    <div style="margin-top: 4px; text-align: right;">
                                        <button type="button" onclick="abrirLugares()" style="background: none; border: none; color: var(--color-salida); font-size: 0.74rem; font-weight: 700; cursor: pointer;">
                                            <i class="fa-solid fa-trash-can"></i> Administrar lugares guardados
                                        </button>
                                    </div>
                                    <div id="boxDestinoNuevo" style="display: none; margin-top: 6px; background: #F8FAFC; padding: 10px; border: 1px solid #CBD5E1; border-radius: 8px;">
                                        <input type="text" name="destino_nuevo" id="inputDestinoNuevo" class="form-control" style="padding-left: 12px;" placeholder="Nombre del lugar nuevo" maxlength="150" value="<?php echo old_val('destino_nuevo'); ?>">
                                        <div style="margin-top: 8px; font-size: 0.78rem; font-weight: 700;">¿Desea guardar este lugar para usarlo después?</div>
                                        <label style="font-size: 0.8rem; margin-right: 14px;"><input type="radio" name="guardar_destino" value="si" <?php echo (($old['guardar_destino'] ?? '') === 'si') ? 'checked' : ''; ?>> Sí, guardarlo</label>
                                        <label style="font-size: 0.8rem;"><input type="radio" name="guardar_destino" value="no" <?php echo (($old['guardar_destino'] ?? 'no') !== 'si') ? 'checked' : ''; ?>> No, solo esta vez</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Nombre de Quien Entrega (Bodega)</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-user-tie"></i>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars(auth_nombre()); ?> (Bodega)" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Quien Recibe (Destino)</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-user-check"></i>
                                        <input type="text" name="nombre_quien_recibe" class="form-control" placeholder="Responsable que retira el material" value="<?php echo old_val('nombre_quien_recibe'); ?>" maxlength="150" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Cédula de Quien Solicita</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-id-card"></i>
                                        <input type="text" name="cedula_quien_recibe" class="form-control" placeholder="Ej. 001-010190-0001A" value="<?php echo old_val('cedula_quien_recibe'); ?>" maxlength="20" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Número de Celular</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-mobile-screen"></i>
                                        <input type="text" name="celular_quien_recibe" class="form-control" placeholder="Ej. 8921-8911" value="<?php echo old_val('celular_quien_recibe'); ?>" maxlength="14" inputmode="numeric" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo condicional para Préstamo -->
                            <div class="form-group" id="groupFechaDevolucion" style="display: <?php echo (($old['tipo'] ?? '') === 'prestamo') ? 'block' : 'none'; ?>;">
                                <label style="color: var(--color-salida); font-weight: 800;">Fecha Prevista de Devolución</label>
                                <div class="input-with-icon">
                                    <i class="fa-regular fa-calendar-days" style="color: var(--color-salida);"></i>
                                    <input type="date" name="fecha_prevista_devolucion" id="inputFechaDevolucion" class="form-control" style="border-color: var(--color-salida);" min="<?php echo date('Y-m-d'); ?>" value="<?php echo old_val('fecha_prevista_devolucion'); ?>">
                                </div>
                            </div>

                            <div class="form-section-title"><i class="fa-solid fa-boxes-stacked"></i> Detalle de Productos a Despachar</div>

                            <!-- TABLA CON SELECTOR REAL Y DESPLEGABLE EN CADA FILA -->
                            <table class="prod-table" id="tablaProductos">
                                <thead>
                                    <tr>
                                        <th style="width: 55%;">Seleccionar Producto (Catálogo)</th>
                                        <th style="width: 30%;">Cantidad a Despachar</th>
                                        <th style="width: 15%; text-align: center;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="productos[]" class="form-control" style="padding-left: 12px;" required>
                                                <option value="">-- Seleccionar producto del catálogo --</option>
                                                <?php foreach ($productos_catalogo as $prod): ?>
                                                    <option value="<?php echo htmlspecialchars($prod['codigo']); ?>" data-tipo="<?php echo $prod['tipo']; ?>"
                                                        <?php echo ($prod['vencido'] || (int) $prod['stock_actual'] <= 0) ? 'disabled' : ''; ?>>
                                                        [<?php echo htmlspecialchars($prod['codigo']); ?>] <?php echo htmlspecialchars($prod['nombre']); ?> (<?php echo htmlspecialchars($prod['unidad']); ?>)
                                                        — <?php echo $prod['vencido'] ? 'VENCIDO' : 'Disp: ' . (int) $prod['stock_actual']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="cantidades[]" class="form-control" style="padding-left: 12px;" value="1" min="1" required>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn-remove-row" onclick="eliminarFila(this)" title="Quitar ítem">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <button type="button" class="btn-add-row" onclick="agregarFilaProducto()">
                                <i class="fa-solid fa-plus"></i> Agregar otro producto al despacho
                            </button>

                            <div class="form-section-title" style="margin-top: 24px;"><i class="fa-solid fa-comment-dots"></i> Notas Adicionales</div>

                            <div class="form-group">
                                <label>Observaciones (Opcional)</label>
                                <textarea name="observaciones" class="form-control" placeholder="Condiciones de salida o comentarios..."><?php echo old_val('observaciones'); ?></textarea>
                            </div>

                            <div class="form-actions">
                                <a href="Salidas.php" class="btn-cancel"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                                <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Registrar y Generar Vale</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Administrar lugares guardados -->
                <div id="modalLugares" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,23,42,.55);align-items:center;justify-content:center;">
                    <div style="background:#fff;width:92%;max-width:440px;max-height:80vh;overflow:auto;border-radius:14px;padding:20px;box-shadow:0 20px 50px rgba(0,0,0,.3);">
                        <h3 style="font-size:1rem;margin-bottom:4px;"><i class="fa-solid fa-location-dot"></i> Lugares guardados</h3>
                        <p style="font-size:0.78rem;color:var(--text-muted);margin-bottom:12px;">Quite los que ya no necesite. Las salidas anteriores conservan su lugar.</p>
                        <div id="listaLugares">
                            <?php foreach ($destinos_db as $dest): ?>
                                <div class="fila-lugar" data-id="<?php echo (int) $dest['id_destino']; ?>" style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F1F5F9;font-size:0.85rem;">
                                    <span><?php echo htmlspecialchars($dest['nombre']); ?></span>
                                    <button type="button" onclick="quitarLugar(<?php echo (int) $dest['id_destino']; ?>, this)" title="Quitar este lugar" style="background:#FEE2E2;color:#991B1B;border:none;border-radius:6px;padding:5px 9px;cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            <?php endforeach; ?>
                            <p id="sinLugares" style="display:<?php echo empty($destinos_db) ? 'block' : 'none'; ?>;font-size:0.8rem;color:var(--text-muted);">No hay lugares guardados.</p>
                        </div>
                        <div style="text-align:right;margin-top:14px;"><button type="button" onclick="cerrarLugares()" class="btn-cancel" style="cursor:pointer;">Cerrar</button></div>
                    </div>
                </div>

                <script>
                    function toggleDestinoNuevo() {
                        const nuevo = document.getElementById('selectDestino').value === 'nuevo';
                        document.getElementById('boxDestinoNuevo').style.display = nuevo ? 'block' : 'none';
                        document.getElementById('inputDestinoNuevo').required = nuevo;
                    }
                    document.addEventListener('DOMContentLoaded', toggleDestinoNuevo);

                    function abrirLugares() { document.getElementById('modalLugares').style.display = 'flex'; }
                    function cerrarLugares() { document.getElementById('modalLugares').style.display = 'none'; }
                    function quitarLugar(id, boton) {
                        if (!confirm('¿Quitar este lugar de la lista de guardados?')) return;
                        const datos = new FormData();
                        datos.append('accion', 'eliminar_destino');
                        datos.append('id_destino', id);
                        fetch('Salidas.php', { method: 'POST', body: datos, credentials: 'same-origin' })
                            .then(function (r) { return r.json(); })
                            .then(function (r) {
                                if (!r.ok) { alert('No se pudo quitar el lugar.'); return; }
                                boton.closest('.fila-lugar').remove();
                                const sel = document.getElementById('selectDestino');
                                Array.from(sel.options).forEach(function (op) { if (op.value === String(id)) { if (op.selected) sel.value = ''; op.remove(); } });
                                if (!document.querySelector('#listaLugares .fila-lugar')) document.getElementById('sinLugares').style.display = 'block';
                            })
                            .catch(function () { alert('No se pudo quitar el lugar.'); });
                    }

                    function togglePrestamoFields() {
                        const tipo = document.getElementById('tipoSalida').value;
                        const groupDev = document.getElementById('groupFechaDevolucion');
                        groupDev.style.display = (tipo === 'prestamo') ? 'block' : 'none';
                        document.getElementById('inputFechaDevolucion').required = (tipo === 'prestamo');
                        filtrarProductosPorTipo();
                    }

                    // Préstamo -> solo herramientas; Consumo -> solo consumibles
                    function filtrarProductosPorTipo() {
                        const esperado = (document.getElementById('tipoSalida').value === 'prestamo') ? 'herramienta' : 'consumible';
                        document.querySelectorAll('#tablaProductos select[name="productos[]"]').forEach(function (sel) {
                            Array.from(sel.options).forEach(function (op) {
                                if (!op.value) return;
                                const oculto = op.dataset.tipo !== esperado;
                                op.hidden = oculto;
                                if (oculto && op.selected) sel.value = '';
                            });
                        });
                    }
                    document.addEventListener('DOMContentLoaded', togglePrestamoFields);

                    function agregarFilaProducto() {
                        const tbody = document.querySelector('#tablaProductos tbody');
                        const nuevaFila = document.createElement('tr');
                        
                        const opciones = document.querySelector('#tablaProductos select[name="productos[]"]').innerHTML;


                        nuevaFila.innerHTML = `
                            <td>
                                <select name="productos[]" class="form-control" style="padding-left: 12px;" required>
                                    ${opciones}
                                </select>
                            </td>
                            <td>
                                <input type="number" name="cantidades[]" class="form-control" style="padding-left: 12px;" value="1" min="1" required>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-remove-row" onclick="eliminarFila(this)" title="Quitar ítem">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(nuevaFila);
                        filtrarProductosPorTipo();
                    }

                    function eliminarFila(btn) {
                        const tbody = document.querySelector('#tablaProductos tbody');
                        if (tbody.rows.length > 1) {
                            btn.closest('tr').remove();
                        } else {
                            alert("El despacho debe contener al menos un producto.");
                        }
                    }
                </script>

            <?php elseif ($action === 'voucher' && $voucher_actual): ?>
                <!-- VISTA 3: VALE OFICIAL DE SALIDA -->
                <div class="module-header" style="margin-bottom: 15px;">
                    <div class="header-left">
                        <a href="Salidas.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-file-contract"></i> Vale Oficial de <?php echo ($voucher_actual['tipo'] === 'prestamo') ? 'Préstamo' : 'Consumo'; ?></h1>
                            <p>Comprobante de salida e instrucciones de retorno para control de inventario.</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button onclick="window.print();" class="btn-print"><i class="fa-solid fa-print"></i> Imprimir Vale</button>
                    </div>
                </div>

                <div class="form-wrapper-scroll">
                    <div class="voucher-card">
                        <div class="voucher-header">
                            <h2>ALCALDÍA MUNICIPAL - SISTEMA DE GESTIÓN DE BODEGA (SIGEM)</h2>
                            <p>COMPROBANTE OFICIAL DE SALIDA POR <?php echo strtoupper($voucher_actual['tipo']); ?></p>
                        </div>

                        <?php if ($voucher_actual['tipo'] == 'consumo'): ?>
                            <div class="alert-consumo"><i class="fa-solid fa-circle-info"></i> Indicación: <strong>“No requiere devolución”</strong> (Gasto definitivo de inventario).</div>
                        <?php else: ?>
                            <div class="alert-devolucion"><i class="fa-solid fa-triangle-exclamation"></i> Indicación: <strong>“Debe regresar en la fecha indicada”</strong>. Fecha límite: <strong><?php echo date('d/m/Y', strtotime($voucher_actual['fecha_prevista_devolucion'])); ?></strong></div>
                        <?php endif; ?>

                        <div class="voucher-info-grid">
                            <div><strong>Nº de Documento:</strong> <code><?php echo htmlspecialchars($voucher_actual['numero_documento']); ?></code></div>
                            <div><strong>Fecha de Salida:</strong> <?php echo $voucher_actual['fecha_salida']; ?></div>
                            <div><strong>Motivo:</strong> <?php echo htmlspecialchars($voucher_actual['motivo_salida']); ?></div>
                            <div><strong>Destino Asignado:</strong> <?php echo htmlspecialchars($voucher_actual['nombre_destino']); ?></div>
                            <div><strong>Quien Entrega:</strong> <?php echo htmlspecialchars($voucher_actual['nombre_quien_entrega']); ?></div>
                            <div><strong>Quien Recibe:</strong> <?php echo htmlspecialchars($voucher_actual['nombre_quien_recibe']); ?></div>
                            <div><strong>Cédula:</strong> <?php echo htmlspecialchars($voucher_actual['cedula_quien_recibe'] ?: '—'); ?></div>
                            <div><strong>Celular:</strong> <?php echo htmlspecialchars($voucher_actual['celular_quien_recibe'] !== '' ? fmt_celular($voucher_actual['celular_quien_recibe']) : '—'); ?></div>
                        </div>

                        <table class="prod-table" style="margin-bottom: 20px;">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción del Producto</th>
                                    <th>Cantidad Despachada</th>
                                    <th>Stock en inventario (antes → después)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($voucher_actual['productos'] as $prod): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($prod['codigo']); ?></code></td>
                                    <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                    <td><strong><?php echo (int) $prod['cantidad']; ?> <?php echo htmlspecialchars($prod['unidad']); ?></strong></td>
                                    <td>
                                        <?php if ($prod['stock_anterior'] !== null): ?>
                                            <?php echo (int) $prod['stock_anterior']; ?> → <strong><?php echo (int) $prod['stock_posterior']; ?></strong>
                                            <i class="fa-solid fa-circle-check" style="color:#38A169;" title="Descontado del inventario"></i>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="font-size: 0.78rem; margin-bottom: 20px;">
                            <strong>Observaciones:</strong> <?php echo htmlspecialchars((string) $voucher_actual['observaciones']); ?>
                        </div>

                        <div class="voucher-footer-grid">
                            <div class="signature-box">
                                <div class="signature-line">
                                    <strong><?php echo htmlspecialchars($voucher_actual['nombre_quien_entrega']); ?></strong>
                                    <small>Firma de Quien Entregó (Bodega)</small>
                                </div>
                            </div>
                            <div class="stamp-box">
                                <i class="fa-solid fa-stamp" style="font-size: 1.2rem; margin-bottom: 4px;"></i>
                                Sello Oficial Destino / Bodega
                            </div>
                            <div class="signature-box">
                                <div class="signature-line">
                                    <strong><?php echo htmlspecialchars($voucher_actual['nombre_quien_recibe']); ?></strong>
                                    <small>Firma de Quien Recibió (Destino)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

<<<<<<< HEAD
=======
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
>>>>>>> dcbe80f0b00f2448b1923b4c126b94b010c240aa
</body>
</html>