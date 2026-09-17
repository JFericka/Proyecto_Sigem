<?php
// views/reportes.php
$action = $_GET['action'] ?? 'listar';
$tipo_reporte = $_GET['tipo'] ?? 'inventario';

// Datos de ejemplo para simular la generación de reportes
$datos_reporte = [];
$titulo_reporte = "";

if ($tipo_reporte === 'inventario') {
    $titulo_reporte = "Reporte de Inventario Actual y Stock Crítico";
    $datos_reporte = [
        ["codigo" => "LIM-001", "nombre" => "Cloro", "categoria" => "Limpieza", "stock" => 120, "unidad" => "Galón", "estado" => "Óptimo"],
        ["codigo" => "HER-001", "nombre" => "Pala redonda", "categoria" => "Herramientas", "stock" => 35, "unidad" => "Unidad", "estado" => "Óptimo"],
        ["codigo" => "ELE-001", "nombre" => "Taladro percutor 1/2\"", "categoria" => "Eléctricos", "stock" => 10, "unidad" => "Unidad", "estado" => "Stock Bajo"],
        ["codigo" => "SEG-001", "nombre" => "Casco de seguridad", "categoria" => "Seguridad", "stock" => 0, "unidad" => "Unidad", "estado" => "Agotado / Baja"]
    ];
} elseif ($tipo_reporte === 'entradas') {
    $titulo_reporte = "Reporte Histórico de Entradas (Abastecimientos y Devoluciones)";
    $datos_reporte = [
        ["voucher" => "VCH-2026-890", "tipo" => "Abastecimiento", "entregante" => "Carlos Mendoza", "receptor" => "María Entradas", "fecha" => "2026-09-10", "motivo" => "Compra trimestral de suministros"],
        ["voucher" => "VCH-2026-045", "tipo" => "Devolución", "entregante" => "Ing. Carlos Ruiz", "receptor" => "María Entradas", "fecha" => "2026-09-15", "motivo" => "Finalización de obra principal"]
    ];
} elseif ($tipo_reporte === 'salidas') {
    $titulo_reporte = "Reporte de Salidas y Préstamos Activos";
    $datos_reporte = [
        ["documento" => "SAL-2026-012", "destino" => "Obra Calle Principal", "responsable" => "Ing. Carlos Ruiz", "fecha" => "2026-09-01", "estado" => "Pendiente de Retorno"],
        ["documento" => "SAL-2026-008", "destino" => "Mantenimiento Parque Central", "responsable" => "Ana Gómez", "fecha" => "2026-08-20", "estado" => "Completado / Devuelto"]
    ];
}
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
        @media print {
            body, html { height: auto !important; overflow: visible !important; background: white !important; }
            .navbar, .report-selectors, .filter-toolbar, .btn-back, .module-header { display: none !important; }
            .page-wrapper { padding: 0 !important; background: none !important; display: block !important; }
            .main-container { max-width: 100% !important; height: auto !important; border: none !important; box-shadow: none !important; padding: 0 !important; overflow: visible !important; }
            .table-responsive { overflow: visible !important; height: auto !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th { position: static !important; background-color: #f1f5f9 !important; color: #000 !important; }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
            Sistema de Bodega Municipal
        </div>
        <div class="user-pill">
            <div class="user-avatar">C</div>
            <div class="user-meta">
                <span>Carlos Admin</span>
                <small>Administrador General</small>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <main class="main-container">

            <div class="module-header">
                <div class="header-left">
                    <a href="dashboard.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                    <div class="module-title">
                        <h1><i class="fa-solid fa-chart-line"></i> Reportes y Estadísticas de Bodega</h1>
                        <p>Generación de informes oficiales para auditoría, control de inventario y trazabilidad municipal.</p>
                    </div>
                </div>
            </div>

            <!-- SELECTORES DE TIPO DE REPORTE (Se ocultan automáticamente al imprimir) -->
            <div class="report-selectors">
                <a href="reportes.php?tipo=inventario" class="report-card-option <?php echo ($tipo_reporte === 'inventario') ? 'active' : ''; ?>">
                    <div class="report-card-icon"><i class="fa-solid fa-warehouse"></i></div>
                    <div class="report-card-info">
                        <h3>Inventario Actual</h3>
                        <p>Stock físico, categorías y niveles críticos.</p>
                    </div>
                </a>
                <a href="reportes.php?tipo=entradas" class="report-card-option <?php echo ($tipo_reporte === 'entradas') ? 'active' : ''; ?>">
                    <div class="report-card-icon"><i class="fa-solid fa-file-arrow-down"></i></div>
                    <div class="report-card-info">
                        <h3>Historial de Entradas</h3>
                        <p>Abastecimientos y devoluciones con motivo.</p>
                    </div>
                </a>
                <a href="reportes.php?tipo=salidas" class="report-card-option <?php echo ($tipo_reporte === 'salidas') ? 'active' : ''; ?>">
                    <div class="report-card-icon"><i class="fa-solid fa-file-arrow-up"></i></div>
                    <div class="report-card-info">
                        <h3>Salidas y Préstamos</h3>
                        <p>Trazabilidad de entregas a proyectos.</p>
                    </div>
                </a>
            </div>

            <!-- BARRA DE FILTROS Y ACCIONES (Se oculta al imprimir) -->
            <div class="filter-toolbar">
                <div class="filter-group">
                    <i class="fa-solid fa-filter"></i> Filtrar por fecha:
                    <input type="date" class="form-control-sm" value="2026-09-01">
                    <span>hasta</span>
                    <input type="date" class="form-control-sm" value="2026-09-30">
                    <button class="btn-action" style="padding: 6px 10px;"><i class="fa-solid fa-magnifying-glass"></i> Aplicar</button>
                </div>
                <div>
                    <button onclick="window.print();" class="btn-action btn-print">
                        <i class="fa-solid fa-print"></i> Imprimir / Exportar PDF
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
                                <th>Unidad</th>
                                <th>Estado de Existencia</th>
                            </tr>
                        <?php elseif ($tipo_reporte === 'entradas'): ?>
                            <tr>
                                <th>Nº Voucher</th>
                                <th>Tipo de Entrada</th>
                                <th>Quien Entrega</th>
                                <th>Quien Recibe</th>
                                <th>Fecha</th>
                                <th>Motivo Registrado</th>
                            </tr>
                        <?php elseif ($tipo_reporte === 'salidas'): ?>
                            <tr>
                                <th>Nº Documento</th>
                                <th>Destino de Obra</th>
                                <th>Responsable</th>
                                <th>Fecha de Salida</th>
                                <th>Estado del Préstamo</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php if ($tipo_reporte === 'inventario'): ?>
                            <?php foreach ($datos_reporte as $row): ?>
                            <tr>
                                <td><code><?php echo $row['codigo']; ?></code></td>
                                <td><strong><?php echo $row['nombre']; ?></strong></td>
                                <td><?php echo $row['categoria']; ?></td>
                                <td><strong><?php echo $row['stock']; ?></strong></td>
                                <td><?php echo $row['unidad']; ?></td>
                                <td>
                                    <?php 
                                        $clase = 'status-optimo';
                                        if ($row['stock'] == 0) $clase = 'status-agotado';
                                        elseif ($row['stock'] <= 10) $clase = 'status-bajo';
                                    ?>
                                    <span class="badge-status <?php echo $clase; ?>"><?php echo $row['estado']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php elseif ($tipo_reporte === 'entradas'): ?>
                            <?php foreach ($datos_reporte as $row): ?>
                            <tr>
                                <td><code><?php echo $row['voucher']; ?></code></td>
                                <td><strong><?php echo $row['tipo']; ?></strong></td>
                                <td><?php echo $row['entregante']; ?></td>
                                <td><?php echo $row['receptor']; ?></td>
                                <td><?php echo $row['fecha']; ?></td>
                                <td style="color: var(--text-muted); font-size: 0.82rem;"><?php echo $row['motivo']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php elseif ($tipo_reporte === 'salidas'): ?>
                            <?php foreach ($datos_reporte as $row): ?>
                            <tr>
                                <td><code><?php echo $row['documento']; ?></code></td>
                                <td><strong><?php echo $row['destino']; ?></strong></td>
                                <td><?php echo $row['responsable']; ?></td>
                                <td><?php echo $row['fecha']; ?></td>
                                <td><span class="badge-status status-optimo"><?php echo $row['estado']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>