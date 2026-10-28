<?php
// views/Reportes.php
require_once __DIR__ . '/../config/Auth.php';
auth_requerir('reportes');

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/ReporteModel.php';

// Tipos de reporte a los que tiene acceso el usuario según su rol
$tipos_permitidos = [];
foreach (['inventario', 'entradas', 'salidas'] as $t) {
    if (auth_puede('reporte_' . $t)) {
        $tipos_permitidos[] = $t;
    }
}
if (empty($tipos_permitidos)) {
    header('Location: Dashboard.php?msg=sin_permiso');
    exit;
}

$tipo_reporte = $_GET['tipo'] ?? $tipos_permitidos[0];
if (!in_array($tipo_reporte, $tipos_permitidos, true)) {
    $tipo_reporte = $tipos_permitidos[0];
}

// Subtipo del reporte: ambos o solo uno de los dos tipos de cada módulo
$subtipos = [
    'entradas' => ['todos' => 'Ambos (Abastecimiento y Préstamos)', 'abastecimiento' => 'Solo Abastecimiento', 'devolucion' => 'Solo Préstamos (devoluciones)'],
    'salidas'  => ['todos' => 'Ambos (Consumo y Préstamos)', 'consumo' => 'Solo Consumo', 'prestamo' => 'Solo Préstamos'],
];
$subtipo = (string) ($_GET['subtipo'] ?? 'todos');
if (!isset($subtipos[$tipo_reporte][$subtipo])) {
    $subtipo = 'todos';
}
$etiqueta_subtipo = $subtipos[$tipo_reporte][$subtipo] ?? '';

// Rango de fechas (por defecto: del primer día del mes a hoy)
$valida = function ($f) {
    $d = DateTime::createFromFormat('Y-m-d', (string) $f);
    return $d && $d->format('Y-m-d') === $f;
};
$hoy   = date('Y-m-d');
$desde = (isset($_GET['desde']) && $valida($_GET['desde'])) ? $_GET['desde'] : date('Y-m-01');
$hasta = (isset($_GET['hasta']) && $valida($_GET['hasta'])) ? $_GET['hasta'] : $hoy;
if ($desde > $hasta) {
    list($desde, $hasta) = [$hasta, $desde];
}

$modelo = new ReporteModel($conexion);
$datos_reporte = [];
$titulos = [
    'inventario' => 'Reporte de Inventario Actual y Stock Crítico',
    'entradas'   => 'Reporte Histórico de Entradas (Abastecimientos y Devoluciones)',
    'salidas'    => 'Reporte de Salidas y Préstamos',
];
$titulo_reporte = $titulos[$tipo_reporte];

if ($tipo_reporte === 'inventario') {
    $datos_reporte = $modelo->inventario();
} elseif ($tipo_reporte === 'entradas') {
    $datos_reporte = $modelo->entradas($desde, $hasta, $subtipo);
} else {
    $datos_reporte = $modelo->salidas($desde, $hasta, $subtipo);
}
$limite_aviso = date('Y-m-d', strtotime('+30 days'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas - SIGEM</title>
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
            --color-reportes: #0F766E;   
            --color-danger: #991B1B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-page); color: var(--text-main); height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        .navbar { background-color: var(--card-bg); border-bottom: 1px solid var(--border-light); padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.95rem; }
        .nav-brand-icon { background: #E2E8F0; color: var(--color-primary); width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; }

        .user-pill { display: flex; align-items: center; gap: 10px; }
        .user-avatar { background: var(--color-reportes); color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
        .user-meta span { display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-primary); }
        .user-meta small { font-size: 0.68rem; color: var(--text-muted); }

        .page-wrapper { flex-grow: 1; display: flex; align-items: center; justify-content: center; padding: 12px 20px; background: radial-gradient(circle, rgba(36, 95, 115, 0.08) 0%, rgba(187, 189, 188, 0.15) 100%); }
        .main-container { max-width: 1450px; width: 99%; height: 92vh; background: var(--card-bg); border: 1px solid var(--border-light); border-radius: 12px; padding: 20px 24px; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(36, 95, 115, 0.08); overflow: hidden; }

        .module-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--bg-page); padding-bottom: 14px; margin-bottom: 16px; flex-shrink: 0; }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .btn-back { background-color: #E2E8F0; color: var(--text-main); border: none; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; cursor: pointer; }
        .btn-back:hover { background-color: var(--color-primary); color: white; }
        .module-title h1 { font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .module-title h1 i { color: var(--color-reportes); }
        .module-title p { color: var(--text-muted); font-size: 0.8rem; }

        .report-selectors { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; flex-shrink: 0; }
        .report-card-option { background: #FAFAFA; border: 1px solid var(--border-light); border-radius: 8px; padding: 14px; text-decoration: none; color: var(--text-main); display: flex; align-items: center; gap: 12px; transition: all 0.2s; }
        .report-card-option:hover, .report-card-option.active { background: #F0FDFA; border-color: var(--color-reportes); box-shadow: 0 2px 8px rgba(15, 118, 110, 0.1); }
        .report-card-icon { width: 38px; height: 38px; border-radius: 6px; background: #CCFBF1; color: var(--color-reportes); display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .report-card-info h3 { font-size: 0.85rem; font-weight: 700; margin-bottom: 2px; }
        .report-card-info p { font-size: 0.72rem; color: var(--text-muted); }

        .filter-toolbar { display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; padding: 10px 14px; border-radius: 8px; border: 1px solid #E2E8F0; margin-bottom: 14px; flex-shrink: 0; }
        .filter-group { display: flex; align-items: center; gap: 10px; font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }
        .form-control-sm { padding: 6px 10px; border: 1px solid var(--border-light); border-radius: 6px; font-size: 0.8rem; outline: none; background: white; }
        
        .btn-action { background: var(--color-primary); color: white; border: none; padding: 7px 14px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-action:hover { opacity: 0.9; }
        .btn-print { background: #0F766E; }

        .table-responsive { flex-grow: 1; overflow-y: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #FAFAFA; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; font-weight: 700; padding: 12px 16px; border-bottom: 1px solid var(--border-light); position: sticky; top: 0; z-index: 10; }
        td { padding: 13px 16px; font-size: 0.87rem; border-bottom: 1px solid #F1F5F9; }
        tr:hover { background-color: #F8FAFC; }

        .badge-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; }
        .status-optimo { background: #D1FAE5; color: #065F46; }
        .status-bajo { background: #FEF3C7; color: #92400E; }
        .status-agotado { background: #FEE2E2; color: #991B1B; }

        /* REGLAS ESTRICTAS PARA OCULTAR SELECTORES Y MOSTRAR SOLO DATOS AL IMPRIMIR */
        .print-header { display: none; }
        @page { size: A4 landscape; margin: 12mm; }
        @media print {
            .print-header { display: block !important; margin-bottom: 10px; border-bottom: 2px solid #1E3A5F; padding-bottom: 6px; }
            .print-header h2 { font-size: 15pt; color: #1E3A5F; margin: 0; }
            .print-header h3 { font-size: 12pt; margin: 2px 0; }
            .print-header p { font-size: 9pt; color: #475569; margin: 0; }
            table { font-size: 8.5pt; }
            body, html { height: auto !important; overflow: visible !important; background: white !important; }
            .navbar, .report-selectors, .filter-toolbar, .btn-back, .module-header { display: none !important; }
            .page-wrapper { padding: 0 !important; background: none !important; display: block !important; }
            .main-container { max-width: 100% !important; height: auto !important; border: none !important; box-shadow: none !important; padding: 0 !important; overflow: visible !important; }
            .table-responsive { overflow: visible !important; height: auto !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th { position: static !important; background-color: #f1f5f9 !important; color: #000 !important; }
        }
    
        /* Título "Sistema de Bodega Municipal" e icono con el mismo color del botón Salir */
        .nav-brand { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
        .nav-brand-icon { -webkit-text-fill-color: initial; }
        .nav-brand-icon i { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
        /* Fondo con tono suave del color distintivo del módulo */
        .page-wrapper { background: linear-gradient(180deg, #FDF3E6 0%, #F9E3C8 100%); }
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
                        <h1><i class="fa-solid fa-chart-line"></i> Reportes y Estadísticas de Bodega</h1>
                        <p>Generación de informes oficiales para auditoría, control de inventario y trazabilidad municipal.</p>
                    </div>
                </div>
            </div>

            <!-- SELECTORES DE TIPO DE REPORTE (Se ocultan automáticamente al imprimir) -->
            <div class="report-selectors">
                <a href="<?php echo auth_puede('reporte_inventario') ? 'Reportes.php?tipo=inventario' : '#'; ?>" class="report-card-option <?php echo ($tipo_reporte === 'inventario') ? 'active' : ''; ?>" data-permiso="<?php echo auth_puede('reporte_inventario') ? '1' : '0'; ?>">
                    <div class="report-card-icon"><i class="fa-solid fa-warehouse"></i></div>
                    <div class="report-card-info">
                        <h3>Inventario Actual</h3>
                        <p>Stock físico, mínimos y vencimientos.</p>
                    </div>
                </a>
                <a href="<?php echo auth_puede('reporte_entradas') ? 'Reportes.php?tipo=entradas&desde=' . $desde . '&hasta=' . $hasta : '#'; ?>" class="report-card-option <?php echo ($tipo_reporte === 'entradas') ? 'active' : ''; ?>" data-permiso="<?php echo auth_puede('reporte_entradas') ? '1' : '0'; ?>">
                    <div class="report-card-icon"><i class="fa-solid fa-file-arrow-down"></i></div>
                    <div class="report-card-info">
                        <h3>Historial de Entradas</h3>
                        <p>Qué productos entraron, cuándo y por qué.</p>
                    </div>
                </a>
                <a href="<?php echo auth_puede('reporte_salidas') ? 'Reportes.php?tipo=salidas&desde=' . $desde . '&hasta=' . $hasta : '#'; ?>" class="report-card-option <?php echo ($tipo_reporte === 'salidas') ? 'active' : ''; ?>" data-permiso="<?php echo auth_puede('reporte_salidas') ? '1' : '0'; ?>">
                    <div class="report-card-icon"><i class="fa-solid fa-file-arrow-up"></i></div>
                    <div class="report-card-info">
                        <h3>Salidas y Préstamos</h3>
                        <p>Qué productos salieron, a dónde y su estado.</p>
                    </div>
                </a>
            </div>

            <!-- BARRA DE FILTROS Y ACCIONES (Se oculta al imprimir) -->
            <div class="print-header">
                <h2>SIGEM - Sistema de Bodega Municipal</h2>
                <h3><?php echo htmlspecialchars($titulo_reporte); ?></h3>
                <p>
                    <?php if ($tipo_reporte !== 'inventario'): ?>
                        Tipo: <strong><?php echo htmlspecialchars($etiqueta_subtipo); ?></strong> ·
                        Período: <strong><?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></strong> ·
                    <?php endif; ?>
                    Generado el <?php echo date('d/m/Y H:i'); ?> por <?php echo htmlspecialchars(auth_nombre()); ?>
                </p>
            </div>

            <div class="filter-toolbar">
                <form method="GET" action="Reportes.php" class="filter-group" style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo_reporte); ?>">
                    <?php if ($tipo_reporte === 'inventario'): ?>
                        <i class="fa-solid fa-circle-info"></i> Muestra el estado del inventario al día de hoy (<?php echo date('d/m/Y'); ?>).
                    <?php else: ?>
                        <i class="fa-solid fa-layer-group"></i>
                        <select name="subtipo" class="form-control-sm" onchange="this.form.submit()" title="Tipo de movimiento a incluir">
                            <?php foreach ($subtipos[$tipo_reporte] as $clave => $etq): ?>
                                <option value="<?php echo $clave; ?>" <?php echo ($subtipo === $clave) ? 'selected' : ''; ?>><?php echo $etq; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fa-solid fa-filter"></i> Fecha:
                        <input type="date" name="desde" class="form-control-sm" value="<?php echo $desde; ?>" max="<?php echo $hoy; ?>">
                        <span>hasta</span>
                        <input type="date" name="hasta" class="form-control-sm" value="<?php echo $hasta; ?>" max="<?php echo $hoy; ?>">
                        <button type="submit" class="btn-action" style="padding: 6px 10px;"><i class="fa-solid fa-magnifying-glass"></i> Aplicar</button>
                    <?php endif; ?>
                </form>
                <div>
                    <button type="button" onclick="generarPDF();" class="btn-action btn-print" title="Se abre la ventana de impresión: elija 'Guardar como PDF'">
                        <i class="fa-solid fa-file-pdf"></i> Generar PDF
                    </button>
                </div>
            </div>

            <!-- TABLA DE RESULTADOS DEL REPORTE -->
            <div class="table-responsive">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: var(--color-primary); margin-bottom: 10px;">
                    <i class="fa-solid fa-table-list"></i> <?php echo htmlspecialchars($titulo_reporte); ?>
                </h3>
                <table>
                    <thead>
                        <?php if ($tipo_reporte === 'inventario'): ?>
                            <tr>
                                <th>Código</th>
                                <th>Nombre del Artículo</th>
                                <th>Categoría</th>
                                <th>Stock Físico</th>
                                <th>Stock Mínimo</th>
                                <th>Unidad</th>
                                <th>Vencimiento</th>
                                <th>Estado de Existencia</th>
                            </tr>
                        <?php elseif ($tipo_reporte === 'entradas'): ?>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Nº Voucher</th>
                                <th>Tipo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Quien Entrega</th>
                                <th>Quien Recibe</th>
                                <th>Motivo</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Nº Documento</th>
                                <th>Tipo</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Destino</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php foreach ($datos_reporte as $row): ?>
                            <?php if ($tipo_reporte === 'inventario'): ?>
                                <?php
                                    $stock = (int) $row['stock_actual'];
                                    if ((int) $row['activo'] !== 1) { $clase = 'status-agotado'; $estado = 'Dado de baja'; }
                                    elseif ($stock === 0) { $clase = 'status-agotado'; $estado = 'Agotado'; }
                                    elseif ($stock <= (int) $row['stock_minimo']) { $clase = 'status-bajo'; $estado = 'Stock mínimo'; }
                                    else { $clase = 'status-optimo'; $estado = 'Óptimo'; }
                                ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($row['codigo']); ?></code></td>
                                    <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                    <td><strong><?php echo $stock; ?></strong></td>
                                    <td><?php echo (int) $row['stock_minimo']; ?></td>
                                    <td><?php echo htmlspecialchars($row['unidad']); ?></td>
                                    <td>
                                        <?php if ($row['fecha_vencimiento']): ?>
                                            <?php echo date('d/m/Y', strtotime($row['fecha_vencimiento'])); ?>
                                            <?php if ($row['fecha_vencimiento'] < $hoy): ?><span class="badge-status status-agotado">Vencido</span>
                                            <?php elseif ($row['fecha_vencimiento'] <= $limite_aviso): ?><span class="badge-status status-bajo">Por vencer</span><?php endif; ?>
                                        <?php else: ?>—<?php endif; ?>
                                    </td>
                                    <td><span class="badge-status <?php echo $clase; ?>"><?php echo $estado; ?></span></td>
                                </tr>
                            <?php elseif ($tipo_reporte === 'entradas'): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                                    <td><code><?php echo htmlspecialchars($row['numero_voucher']); ?></code></td>
                                    <td><strong><?php echo ($row['tipo_entrada'] === 'devolucion') ? 'Préstamo (devolución)' : 'Abastecimiento'; ?></strong></td>
                                    <td>[<?php echo htmlspecialchars($row['codigo']); ?>] <?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td><strong><?php echo (int) $row['cantidad']; ?></strong> <?php echo htmlspecialchars($row['unidad']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nombre_quien_entrega']); ?></td>
                                    <td><?php echo htmlspecialchars($row['recibe']); ?></td>
                                    <td style="color: var(--text-muted); font-size: 0.82rem;"><?php echo htmlspecialchars($row['motivo']); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php
                                    if ($row['tipo'] === 'consumo') { $est_txt = 'No retorna'; $clase = 'status-optimo'; }
                                    elseif ($row['estado'] === 'devuelto') { $est_txt = 'Devuelto'; $clase = 'status-optimo'; }
                                    elseif ($row['estado'] === 'vencido') { $est_txt = 'Vencido (sin devolver)'; $clase = 'status-agotado'; }
                                    elseif ($row['estado'] === 'parcial') { $est_txt = 'Devolución parcial'; $clase = 'status-bajo'; }
                                    else { $est_txt = 'Pendiente de retorno'; $clase = 'status-bajo'; }
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_salida'])); ?></td>
                                    <td><code><?php echo htmlspecialchars($row['numero_documento']); ?></code></td>
                                    <td><strong><?php echo ($row['tipo'] === 'prestamo') ? 'Préstamo' : 'Consumo'; ?></strong></td>
                                    <td>[<?php echo htmlspecialchars($row['codigo']); ?>] <?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td><strong><?php echo (int) $row['cantidad']; ?></strong> <?php echo htmlspecialchars($row['unidad']); ?></td>
                                    <td><?php echo htmlspecialchars($row['destino']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['nombre_quien_recibe']); ?>
                                        <?php if ($row['cedula_quien_recibe'] !== '' || $row['celular_quien_recibe'] !== ''): ?>
                                            <small style="display:block;color:var(--text-muted);font-size:0.7rem;">
                                                <?php echo htmlspecialchars($row['cedula_quien_recibe']); ?>
                                                <?php echo $row['celular_quien_recibe'] !== '' ? ' · Cel. ' . htmlspecialchars(fmt_celular($row['celular_quien_recibe'])) : ''; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo $clase; ?>"><?php echo $est_txt; ?></span>
                                        <?php if ($row['tipo'] === 'prestamo' && $row['fecha_prevista_devolucion']): ?>
                                            <small style="display:block;color:var(--text-muted);font-size:0.7rem;">Devolver antes del <?php echo date('d/m/Y', strtotime($row['fecha_prevista_devolucion'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (empty($datos_reporte)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center;color:var(--text-muted);padding:30px;">
                                    No hay registros para mostrar<?php echo ($tipo_reporte !== 'inventario') ? ' en el rango de fechas seleccionado' : ''; ?>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <script src="../assets/js/acceso.js"></script>
    <script>
        // El PDF se genera con la ventana de impresión del navegador ("Guardar como PDF"); el nombre del archivo sale del título
        function generarPDF() {
            const anterior = document.title;
            document.title = 'Reporte_<?php echo $tipo_reporte; ?>_<?php echo $tipo_reporte === 'inventario' ? date('Y-m-d') : $desde . '_a_' . $hasta; ?>';
            window.print();
            document.title = anterior;
        }
    </script>
</body>
</html>