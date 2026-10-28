<?php
// views/Inventario.php
require_once __DIR__ . '/../config/Auth.php';
auth_requerir('inventario');
$puede_baja = auth_puede('baja');

$action = $_GET['action'] ?? 'listar';

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../controllers/Inventariocontroller.php';

$controller = new InventarioController($conexion);

$mensaje_ok = null;
$error_baja = null;

// Solo el Administrador puede dar de baja
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth_requerir('baja');
    $resultado = $controller->darDeBaja($_POST, auth_id());
    if ($resultado['ok']) {
        header('Location: Inventario.php?msg=baja_ok');
        exit;
    }
    $error_baja = $resultado['error'];
    $action = 'dar_baja';
    $_GET['id'] = $_POST['id_producto'] ?? 0;
}
if ($action === 'dar_baja' && !$puede_baja) {
    $action = 'listar';
}
if (($_GET['msg'] ?? '') === 'baja_ok') {
    $mensaje_ok = 'El producto fue dado de baja y quedó registrado en la bitácora.';
}

$producto_baja = null;
if ($action === 'dar_baja') {
    $producto_baja = $controller->obtener((int) ($_GET['id'] ?? 0));
    if (!$producto_baja || (int) $producto_baja['activo'] !== 1) {
        $action = 'listar';
        $producto_baja = null;
    }
}

$inventario_db = $controller->listar();
$hoy = date('Y-m-d');
$limite_aviso = date('Y-m-d', strtotime('+30 days'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Bodega Municipal - SIGEM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-page: #F2F0EF;          
            --card-bg: #FFFFFF;
            --border-light: #BBBDBC;     
            --text-main: #1E293B;
            --text-muted: #64748B;
            --color-primary: #245F73;    
            --color-inventario: #0369A1; 
            --color-danger: #991B1B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-page); color: var(--text-main); height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        .navbar { background-color: var(--card-bg); border-bottom: 1px solid var(--border-light); padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.95rem; }
        .nav-brand-icon { background: #E2E8F0; color: var(--color-primary); width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; }

        .user-pill { display: flex; align-items: center; gap: 10px; }
        .user-avatar { background: var(--color-inventario); color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
        .user-meta span { display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-primary); }
        .user-meta small { font-size: 0.68rem; color: var(--text-muted); }

        .page-wrapper { flex-grow: 1; display: flex; align-items: center; justify-content: center; padding: 12px 20px; background: radial-gradient(circle, rgba(36, 95, 115, 0.08) 0%, rgba(187, 189, 188, 0.15) 100%); }
        .main-container { max-width: 1450px; width: 99%; height: 92vh; background: var(--card-bg); border: 1px solid var(--border-light); border-radius: 12px; padding: 20px 24px; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(36, 95, 115, 0.08); overflow: hidden; }

        .module-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--bg-page); padding-bottom: 14px; margin-bottom: 16px; flex-shrink: 0; }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .btn-back { background-color: #E2E8F0; color: var(--text-main); border: none; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; cursor: pointer; }
        .btn-back:hover { background-color: var(--color-primary); color: white; }
        .module-title h1 { font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .module-title h1 i { color: var(--color-inventario); }
        .module-title p { color: var(--text-muted); font-size: 0.8rem; }

        .filter-bar { display: flex; gap: 10px; margin-bottom: 14px; flex-shrink: 0; position: relative; align-items: center; }
        .filter-bar i { position: absolute; left: 14px; color: var(--text-muted); font-size: 0.85rem; }
        .search-input { flex-grow: 1; padding: 9px 14px 9px 38px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.85rem; outline: none; background: #FAFAFA; }
        .search-input:focus { border-color: var(--color-primary); background: #FFFFFF; box-shadow: 0 0 0 3px rgba(36, 95, 115, 0.1); }

        .table-responsive { flex-grow: 1; overflow-y: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #FAFAFA; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; font-weight: 700; padding: 12px 16px; border-bottom: 1px solid var(--border-light); position: sticky; top: 0; z-index: 10; }
        td { padding: 13px 16px; font-size: 0.87rem; border-bottom: 1px solid #F1F5F9; }
        tr:hover { background-color: #F8FAFC; }

        .badge-stock { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
        .stock-normal { background: #D1FAE5; color: #065F46; }
        .stock-bajo { background: #FEF3C7; color: #92400E; }
        .stock-agotado { background: #FEE2E2; color: #991B1B; }
        
        .badge-estado { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
        .estado-activo { background: #E0F2FE; color: #0369A1; }
        .estado-inactivo { background: #F1F5F9; color: var(--text-muted); text-decoration: line-through; }

        .btn-action { background: #E2E8F0; color: var(--text-main); border: 1px solid #CBD5E1; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
        .btn-action:hover { background: var(--color-primary); color: white; border-color: var(--color-primary); }
        .btn-danger { background: #FEE2E2; color: var(--color-danger); border-color: #FCA5A5; }
        .btn-danger:hover { background: var(--color-danger); color: white; }

        /* MODAL DE DAR DE BAJA */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; z-index: 100; }
        .modal-card { background: white; width: 450px; border-radius: 10px; padding: 24px; border: 1px solid var(--border-light); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .modal-title { font-size: 1rem; font-weight: 800; color: var(--color-danger); margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .modal-desc { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px; line-height: 1.4; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 9px 12px; border: 1px solid var(--border-light); border-radius: 6px; font-size: 0.85rem; outline: none; background: #FAFAFA; }
        .form-control:focus { border-color: var(--color-danger); background: white; box-shadow: 0 0 0 3px rgba(153, 27, 27, 0.1); }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    
        /* Ajuste para que la tabla quepa en el panel sin desplazamiento horizontal */
        .table-responsive { overflow-x: hidden; }
        table { table-layout: fixed; }
        th { padding: 10px 6px; font-size: 0.66rem; }
        td { padding: 10px 6px; font-size: 0.8rem; word-break: break-word; }
        .badge-stock, .badge-estado { padding: 3px 7px; font-size: 0.68rem; gap: 4px; white-space: normal; }
        .btn-action { padding: 5px 8px; font-size: 0.7rem; }
        th:nth-child(1) { width: 8%; } th:nth-child(2) { width: 20%; } th:nth-child(3) { width: 13%; }
        th:nth-child(4) { width: 8%; } th:nth-child(5) { width: 7%; } th:nth-child(6) { width: 9%; }
        th:nth-child(7) { width: 7%; } th:nth-child(8) { width: 10%; } th:nth-child(9) { width: 9%; }
        th:nth-child(10) { width: 9%; }
    
        /* Título "Sistema de Bodega Municipal" e icono con el mismo color del botón Salir */
        .nav-brand { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
        .nav-brand-icon { -webkit-text-fill-color: initial; }
        .nav-brand-icon i { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
        /* Fondo con tono suave del color distintivo del módulo */
        .page-wrapper { background: linear-gradient(180deg, #EEEEFC 0%, #E0E0F8 100%); }
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

            <div class="module-header">
                <div class="header-left">
                    <a href="Dashboard.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                    <div class="module-title">
                        <h1><i class="fa-solid fa-warehouse"></i> Inventario Actual de Bodega</h1>
                        <p>Control estricto de existencias físicas. El stock se actualiza exclusivamente mediante Entradas y Salidas oficiales.</p>
                    </div>
                </div>
            </div>

            <?php if ($mensaje_ok): ?>
                <div style="background:#F0FFF4;border:1px solid #9AE6B4;color:#276749;padding:9px 14px;border-radius:8px;font-size:0.82rem;margin-bottom:10px;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($mensaje_ok); ?>
                </div>
            <?php endif; ?>

            <div class="filter-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscarProducto" class="search-input" placeholder="Buscar producto por código o nombre...">
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre del Producto</th>
                            <th>Categoría</th>
                            <th>Tipo</th>
                            <th>Unidad</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <?php if ($puede_baja): ?><th style="text-align: center;">Acciones</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventario_db as $prod): ?>
                        <tr class="fila-producto">
                            <td><code><?php echo $prod['codigo']; ?></code></td>
                            <td><strong><?php echo htmlspecialchars($prod['nombre']); ?></strong></td>
                            <td><span style="font-size: 0.78rem; color: var(--text-muted);"><?php echo ucfirst(str_replace('_', ' ', $prod['categoria'])); ?></span></td>
                            <td><?php echo ucfirst($prod['tipo']); ?></td>
                            <td><?php echo $prod['unidad']; ?></td>
                            <td>
                                <?php 
                                    $stock = (int) $prod['stock_actual'];
                                    $clase_stock = 'stock-normal';
                                    $icono_stock = 'fa-circle-check';
                                    if ($stock == 0) {
                                        $clase_stock = 'stock-agotado';
                                        $icono_stock = 'fa-circle-xmark';
                                    } elseif ($stock <= (int) $prod['stock_minimo']) {
                                        $clase_stock = 'stock-bajo';
                                        $icono_stock = 'fa-triangle-exclamation';
                                    }
                                ?>
                                <span class="badge-stock <?php echo $clase_stock; ?>">
                                    <i class="fa-solid <?php echo $icono_stock; ?>"></i> <?php echo $stock; ?> <?php echo $prod['unidad']; ?>
                                </span>
                            </td>
                            <td><?php echo (int) $prod['stock_minimo']; ?></td>
                            <td>
                                <?php if ($prod['fecha_vencimiento']): ?>
                                    <?php
                                        $color_venc = 'inherit';
                                        if ($prod['fecha_vencimiento'] < $hoy) { $color_venc = '#C53030'; }
                                        elseif ($prod['fecha_vencimiento'] <= $limite_aviso) { $color_venc = '#B7791F'; }
                                    ?>
                                    <span style="color: <?php echo $color_venc; ?>; font-weight: <?php echo ($color_venc === 'inherit') ? '400' : '700'; ?>;">
                                        <?php echo date('d/m/Y', strtotime($prod['fecha_vencimiento'])); ?>
                                        <?php if ($prod['fecha_vencimiento'] < $hoy): ?>(vencido)<?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($prod['activo'] == 1): ?>
                                    <span class="badge-estado estado-activo"><i class="fa-solid fa-check"></i> Activo</span>
                                <?php else: ?>
                                    <span class="badge-estado estado-inactivo"><i class="fa-solid fa-ban"></i> Dado de Baja</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($puede_baja): ?>
                            <td style="text-align: center;">
                                <?php if ($prod['activo'] == 1): ?>
                                    <a href="Inventario.php?action=dar_baja&id=<?php echo $prod['id_producto']; ?>" class="btn-action btn-danger" title="Dar de baja por daño, pérdida u obsolescencia">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Dar de Baja
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">Sin operaciones</span>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inventario_db)): ?>
                        <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:30px;">No hay productos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- MODAL DE DAR DE BAJA (solo Administrador) -->
            <?php if ($action === 'dar_baja' && $producto_baja): ?>
            <div class="modal-overlay">
                <div class="modal-card">
                    <div class="modal-title"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar Baja de Producto</div>
                    <div class="modal-desc">
                        Está a punto de dar de baja un artículo del inventario municipal por motivos de fuerza mayor (daño, pérdida o desuso). Esta acción inhabilitará el producto y dejará constancia en bitácora sin alterar los registros contables de entradas pasadas.
                    </div>
                    <form action="Inventario.php" method="POST">
                        <input type="hidden" name="id_producto" value="<?php echo (int) $producto_baja['id_producto']; ?>">
                        <p style="font-size:0.85rem;margin-bottom:10px;">Producto: <strong>[<?php echo htmlspecialchars($producto_baja['codigo']); ?>] <?php echo htmlspecialchars($producto_baja['nombre']); ?></strong> · Stock actual: <?php echo (int) $producto_baja['stock_actual']; ?> <?php echo htmlspecialchars($producto_baja['unidad']); ?></p>
                        <?php if ($error_baja): ?>
                            <div style="background:#FFF5F5;border:1px solid #FEB2B2;color:#C53030;padding:8px 12px;border-radius:8px;font-size:0.8rem;margin-bottom:10px;"><?php echo htmlspecialchars($error_baja); ?></div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Motivo de la Baja</label>
                            <select name="motivo_baja" class="form-control" required>
                                <option value="danado">Dañado / Inutilizable</option>
                                <option value="perdido">Extraviado / Pérdida en campo</option>
                                <option value="obsoleto">Obsoleto / Fuera de norma</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Observaciones o Justificación</label>
                            <textarea name="observaciones_baja" class="form-control" placeholder="Escriba los detalles del acta o justificación..." style="height: 60px; resize: none;" required></textarea>
                        </div>
                        <div class="modal-actions">
                            <a href="Inventario.php" class="btn-action" style="background: #E2E8F0;"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                            <button type="submit" class="btn-action btn-danger" style="border: none;"><i class="fa-solid fa-floppy-disk"></i> Confirmar y Dar de Baja</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <script>
        // Búsqueda en vivo por código o nombre
        document.getElementById('buscarProducto').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.fila-producto').forEach(function (tr) {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>

</body>
</html>