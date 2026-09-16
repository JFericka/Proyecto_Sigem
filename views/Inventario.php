<?php
// views/inventario.php
$action = $_GET['action'] ?? 'listar';

// Datos de inventario sincronizados estrictamente con las tablas `productos` y `categorias` de la BD
$inventario_db = [
    [
        "id_producto" => 1,
        "codigo" => "LIM-001",
        "nombre" => "Cloro",
        "categoria" => "limpieza",
        "tipo" => "consumible",
        "unidad" => "galón",
        "stock_actual" => 120,
        "activo" => 1
    ],
    [
        "id_producto" => 6,
        "codigo" => "HER-001",
        "nombre" => "Pala redonda",
        "categoria" => "herramientas_manuales",
        "tipo" => "herramienta",
        "unidad" => "unidad",
        "stock_actual" => 35,
        "activo" => 1
    ],
    [
        "id_producto" => 11,
        "codigo" => "ELE-001",
        "nombre" => "Taladro percutor 1/2\"",
        "categoria" => "herramientas_electricas",
        "tipo" => "herramienta",
        "unidad" => "unidad",
        "stock_actual" => 10,
        "activo" => 1
    ],
    [
        "id_producto" => 21,
        "codigo" => "SEG-001",
        "nombre" => "Casco de seguridad",
        "categoria" => "seguridad_higiene",
        "tipo" => "consumible",
        "unidad" => "unidad",
        "stock_actual" => 0,
        "activo" => 0 // Inhabilitado / Dado de baja
    ]
];

// Catálogo de categorías oficiales de la BD
$categorias_db = [
    "limpieza" => "Productos de aseo, desinfección y limpieza general",
    "herramientas_manuales" => "Herramientas de uso manual para construcción",
    "herramientas_electricas" => "Equipos eléctricos portátiles",
    "maquinaria_pesada" => "Equipos pesados para obras",
    "seguridad_higiene" => "Seguridad industrial y EPP"
];
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
                        <h1><i class="fa-solid fa-warehouse"></i> Inventario Actual de Bodega</h1>
                        <p>Control estricto de existencias físicas. El stock se actualiza exclusivamente mediante Entradas y Salidas oficiales.</p>
                    </div>
                </div>
            </div>

            <div class="filter-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" class="search-input" placeholder="Buscar producto por código o nombre...">
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
                            <th>Estado</th>
                            <th style="text-align: center;">Acciones de Control</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventario_db as $prod): ?>
                        <tr>
                            <td><code><?php echo $prod['codigo']; ?></code></td>
                            <td><strong><?php echo htmlspecialchars($prod['nombre']); ?></strong></td>
                            <td><span style="font-size: 0.78rem; color: var(--text-muted);"><?php echo ucfirst(str_replace('_', ' ', $prod['categoria'])); ?></span></td>
                            <td><?php echo ucfirst($prod['tipo']); ?></td>
                            <td><?php echo $prod['unidad']; ?></td>
                            <td>
                                <?php 
                                    $stock = $prod['stock_actual'];
                                    $clase_stock = 'stock-normal';
                                    $icono_stock = 'fa-circle-check';
                                    if ($stock == 0) {
                                        $clase_stock = 'stock-agotado';
                                        $icono_stock = 'fa-circle-xmark';
                                    } elseif ($stock <= 10) {
                                        $clase_stock = 'stock-bajo';
                                        $icono_stock = 'fa-triangle-exclamation';
                                    }
                                ?>
                                <span class="badge-stock <?php echo $clase_stock; ?>">
                                    <i class="fa-solid <?php echo $icono_stock; ?>"></i> <?php echo $stock; ?> <?php echo $prod['unidad']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($prod['activo'] == 1): ?>
                                    <span class="badge-estado estado-activo"><i class="fa-solid fa-check"></i> Activo</span>
                                <?php else: ?>
                                    <span class="badge-estado estado-inactivo"><i class="fa-solid fa-ban"></i> Dado de Baja</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($prod['activo'] == 1): ?>
                                    <a href="inventario.php?action=dar_baja&id=<?php echo $prod['id_producto']; ?>" class="btn-action btn-danger" title="Dar de baja por daño, pérdida u obsolescencia">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Dar de Baja
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">Sin operaciones</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- MODAL SIMULADO DE DAR DE BAJA -->
            <?php if ($action === 'dar_baja'): ?>
            <div class="modal-overlay">
                <div class="modal-card">
                    <div class="modal-title"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar Baja de Producto</div>
                    <div class="modal-desc">
                        Está a punto de dar de baja un artículo del inventario municipal por motivos de fuerza mayor (daño, pérdida o desuso). Esta acción inhabilitará el producto y dejará constancia en bitácora sin alterar los registros contables de entradas pasadas.
                    </div>
                    <form action="inventario.php" method="POST">
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
                            <a href="inventario.php" class="btn-action" style="background: #E2E8F0;"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                            <button type="submit" class="btn-action btn-danger" style="border: none;"><i class="fa-solid fa-floppy-disk"></i> Confirmar y Dar de Baja</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

</body>
</html>