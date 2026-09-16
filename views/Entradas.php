 <?php
// views/entradas.php
$action = $_GET['action'] ?? 'listar';
$id_voucher = $_GET['id'] ?? 1;

// Datos de ejemplo para el listado general
$entradas_db = [
    [
        "id_entrada" => 1,
        "numero_voucher" => "VCH-2026-890",
        "tipo_entrada" => "abastecimiento",
        "motivo_entrada" => "Compra trimestral de suministros de aseo y limpieza general para dependencias.",
        "nombre_quien_entrega" => "Carlos Mendoza (Representante Comercial)",
        "nombre_quien_recibe" => "María Entradas (Bodega)",
        "fecha" => "2026-09-10 08:30:00",
        "observaciones" => "Ingreso de productos mixtos (nuevos y existentes) sin novedad.",
        "productos" => [
            ["codigo" => "LIM-001", "nombre" => "Cloro", "detalle" => "Existente del catálogo", "cantidad" => 20, "unidad" => "galón"],
            ["codigo" => "LIM-005", "nombre" => "Gel desinfectante galón", "detalle" => "Nuevo producto registrado", "cantidad" => 10, "unidad" => "galón"]
        ]
    ],
    [
        "id_entrada" => 2,
        "numero_voucher" => "VCH-2026-045",
        "tipo_entrada" => "devolucion",
        "motivo_entrada" => "Retorno de herramientas de campo por finalización de la obra principal.",
        "nombre_quien_entrega" => "Ing. Carlos Ruiz",
        "nombre_quien_recibe" => "María Entradas (Bodega)",
        "fecha" => "2026-09-15 14:00:00",
        "observaciones" => "Devolución asociada al préstamo SAL-2026-012.",
        "productos" => [
            ["codigo" => "HER-001", "nombre" => "Pala redonda", "detalle" => "Estado: bueno (Devuelto: 5 / 5)", "cantidad" => 5, "unidad" => "unidad"],
            ["codigo" => "HER-002", "nombre" => "Pico", "detalle" => "Estado: dañado (Devuelto: 2 / 2)", "cantidad" => 2, "unidad" => "unidad"]
        ]
    ]
];

// Catálogo general de productos para Abastecimiento
$productos_catalogo = [
    ["codigo" => "LIM-001", "nombre" => "Cloro", "unidad" => "galón"],
    ["codigo" => "LIM-002", "nombre" => "Desinfectante multiusos", "unidad" => "galón"],
    ["codigo" => "HER-001", "nombre" => "Pala redonda", "unidad" => "unidad"],
    ["codigo" => "HER-002", "nombre" => "Pico", "unidad" => "unidad"],
    ["codigo" => "ELE-001", "nombre" => "Taladro percutor 1/2\"", "unidad" => "unidad"]
];

// Categorías oficiales para productos nuevos
$categorias_db = [
    "limpieza" => "Productos de aseo y limpieza",
    "herramientas_manuales" => "Herramientas de uso manual",
    "herramientas_electricas" => "Equipos eléctricos portátiles",
    "maquinaria_pesada" => "Equipos pesados para obras",
    "seguridad_higiene" => "Seguridad industrial y EPP"
];

// Préstamos pendientes activos
$prestamos_activos = [
    [
        "id_salida" => 2,
        "numero_documento" => "SAL-2026-012",
        "destino" => "Obra Calle Principal",
        "responsable" => "Ing. Carlos Ruiz",
        "productos" => [
            ["codigo" => "HER-001", "nombre" => "Pala redonda", "prestado" => 5, "unidad" => "unidad"],
            ["codigo" => "HER-002", "nombre" => "Pico", "prestado" => 2, "unidad" => "unidad"]
        ]
    ]
];

$voucher_actual = null;
if ($action === 'voucher') {
    foreach ($entradas_db as $ent) {
        if ($ent['id_entrada'] == $id_voucher) {
            $voucher_actual = $ent;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Entradas y Recepciones - SIGEM</title>
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
            --color-entrada: #00875A;    
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-page); color: var(--text-main); height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        .navbar { background-color: var(--card-bg); border-bottom: 1px solid var(--border-light); padding: 10px 30px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.95rem; }
        .nav-brand-icon { background: #E2E8F0; color: var(--color-primary); width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; }

        .user-pill { display: flex; align-items: center; gap: 10px; }
        .user-avatar { background: var(--color-entrada); color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
        .user-meta span { display: block; font-size: 0.82rem; font-weight: 700; color: var(--color-primary); }
        .user-meta small { font-size: 0.68rem; color: var(--text-muted); }

        .page-wrapper { flex-grow: 1; display: flex; align-items: center; justify-content: center; padding: 12px 20px; background: radial-gradient(circle, rgba(36, 95, 115, 0.08) 0%, rgba(187, 189, 188, 0.15) 100%); }
        .main-container { max-width: 1450px; width: 99%; height: 92vh; background: var(--card-bg); border: 1px solid var(--border-light); border-radius: 12px; padding: 20px 24px; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(36, 95, 115, 0.08); overflow: hidden; }

        .module-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--bg-page); padding-bottom: 14px; margin-bottom: 16px; flex-shrink: 0; }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .btn-back { background-color: #E2E8F0; color: var(--text-main); border: none; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; cursor: pointer; }
        .btn-back:hover { background-color: var(--color-primary); color: white; }
        .module-title h1 { font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .module-title h1 i { color: var(--color-entrada); }
        .module-title p { color: var(--text-muted); font-size: 0.8rem; }

        .btn-add { background-color: var(--color-entrada); color: white; border: none; padding: 9px 16px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        
        .table-responsive { flex-grow: 1; overflow-y: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #FAFAFA; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; font-weight: 700; padding: 12px 16px; border-bottom: 1px solid var(--border-light); position: sticky; top: 0; z-index: 10; }
        td { padding: 13px 16px; font-size: 0.87rem; border-bottom: 1px solid #F1F5F9; }
        tr:hover { background-color: #F8FAFC; }

        .type-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
        .type-abastecimiento { background: #D1FAE5; color: #065F46; }
        .type-devolucion { background: #E0F2FE; color: #0369A1; }
        
        .btn-voucher { background: #E2E8F0; color: var(--text-main); border: 1px solid #CBD5E1; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-voucher:hover { background: var(--color-primary); color: white; border-color: var(--color-primary); }

        .form-wrapper-scroll { flex-grow: 1; overflow-y: auto; padding: 5px 10px; }
        .form-container-structured { max-width: 1100px; margin: 0 auto; background: #FFFFFF; border: 1px solid var(--border-light); border-radius: 10px; padding: 24px 30px; }
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

        /* TABLAS DINÁMICAS */
        .prod-table { width: 100%; border: 1px solid var(--border-light); border-radius: 8px; overflow: hidden; font-size: 0.82rem; background: #FFFFFF; margin-top: 10px; }
        .prod-table th { background: #F1F5F9; padding: 10px 12px; font-size: 0.7rem; color: var(--text-muted); }
        .prod-table td { padding: 10px 12px; border-bottom: 1px solid #E2E8F0; vertical-align: middle; }

        .btn-add-row { background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD; padding: 8px 16px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; }
        .btn-add-row:hover { background: #BAE6FD; }

        .btn-remove-row { background: #FEE2E2; color: #991B1B; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .btn-remove-row:hover { background: #FCA5A5; }

        .form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; border-top: 1px solid var(--bg-page); padding-top: 16px; }
        .btn-cancel { background: #E2E8F0; color: var(--text-main); padding: 9px 18px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-cancel:hover { background: #CBD5E1; }
        .btn-save { background: var(--color-entrada); color: white; border: none; padding: 9px 20px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-save:hover { opacity: 0.9; }

        /* VOUCHER / COMPROBANTE OFICIAL */
        .voucher-card { max-width: 850px; margin: 0 auto; background: white; border: 1px solid var(--border-light); border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
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
        .btn-print:hover { opacity: 0.9; }
        .alert-entrada { background: #D1FAE5; color: #065F46; padding: 10px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border: 1px solid #A7F3D0; }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
            Sistema de Bodega Municipal
        </div>
        <div class="user-pill">
            <div class="user-avatar">M</div>
            <div class="user-meta">
                <span>María Entradas</span>
                <small>Operador de Entradas</small>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <main class="main-container">

            <?php if ($action === 'listar'): ?>
                <!-- VISTA 1: LISTADO DE ENTRADAS -->
                <div class="module-header">
                    <div class="header-left">
                        <a href="dashboard.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-file-arrow-down"></i> Control de Entradas</h1>
                            <p>Registro de abastecimientos y devoluciones de inventario municipal.</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="entradas.php?action=agregar" class="btn-add"><i class="fa-solid fa-plus"></i> Registrar Entrada</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nº Voucher</th>
                                <th>Tipo de Entrada</th>
                                <th>Quien Entrega</th>
                                <th>Quien Recibe</th>
                                <th>Fecha y Hora</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entradas_db as $ent): ?>
                            <tr>
                                <td><strong><?php echo $ent['id_entrada']; ?></strong></td>
                                <td><code><?php echo htmlspecialchars($ent['numero_voucher']); ?></code></td>
                                <td>
                                    <?php $clase_tipo = ($ent['tipo_entrada'] == 'abastecimiento') ? 'type-abastecimiento' : 'type-devolucion'; ?>
                                    <span class="type-badge <?php echo $clase_tipo; ?>"><i class="fa-solid fa-tag"></i> <?php echo ucfirst($ent['tipo_entrada']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($ent['nombre_quien_entrega']); ?></td>
                                <td><?php echo htmlspecialchars($ent['nombre_quien_recibe']); ?></td>
                                <td><?php echo $ent['fecha']; ?></td>
                                <td>
                                    <a href="entradas.php?action=voucher&id=<?php echo $ent['id_entrada']; ?>" class="btn-voucher">
                                        <i class="fa-solid fa-print"></i> Ver Comprobante
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($action === 'agregar'): ?>
                <!-- VISTA 2: FORMULARIO DINÁMICO CON CRONÓMETRO Y MOTIVO SEPARADO -->
                <div class="module-header" style="margin-bottom: 15px;">
                    <div class="header-left">
                        <a href="entradas.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-plus-circle"></i> Registrar Nueva Entrada</h1>
                            <p>Complete la información general, el motivo del ingreso y los detalles de los productos.</p>
                        </div>
                    </div>
                </div>

                <div class="form-wrapper-scroll">
                    <div class="form-container-structured">
                        <form action="entradas.php" method="POST">
                            
                            <div class="form-section-title"><i class="fa-solid fa-file-lines"></i> Información General de Recepción</div>
                            
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Tipo de Entrada</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                        <select class="form-control" id="tipoEntrada" name="tipo_entrada" onchange="alternarModoEntrada()" required>
                                            <option value="abastecimiento">Abastecimiento (Catálogo o Nuevos Artículos)</option>
                                            <option value="devolucion">Devolución (Retorno de Préstamo Activo)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Número de Voucher / Comprobante</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-hashtag"></i>
                                        <input type="text" name="numero_voucher" class="form-control" placeholder="Ej. VCH-2026-901" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label id="labelQuienEntrega">Nombre de Quien Entrega</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-user-tie" id="iconQuienEntrega"></i>
                                        <input type="text" name="nombre_quien_entrega" class="form-control" placeholder="Proveedor o responsable que devuelve" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Fecha y Hora de Recepción (En Vivo)</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-clock"></i>
                                        <input type="text" id="relojRecepcion" class="form-control" value="" readonly style="background: #E2E8F0; font-weight: 700; color: var(--color-primary);">
                                    </div>
                                </div>
                            </div>

                            <!-- MÓDULO DE MOTIVO SEPARADO -->
                            <div class="form-group">
                                <label style="color: var(--color-primary); font-weight: 800;" id="labelMotivo">Motivo de la Entrada (Obligatorio para el Comprobante)</label>
                                <div class="input-with-icon" style="align-items: flex-start;">
                                    <i class="fa-solid fa-pen-to-square" style="top: 14px;"></i>
                                    <textarea name="motivo_entrada" id="inputMotivo" class="form-control" placeholder="Escriba detalladamente el motivo de la recepción (Ej. Compra trimestral de suministros de aseo o retorno de herramientas de campo)..." required></textarea>
                                </div>
                            </div>

                            <!-- Selector dinámico de Préstamo origen para Devoluciones -->
                            <div class="form-group" id="groupSalidaOrigen" style="display: none; background: #F8FAFC; padding: 12px; border-radius: 8px; border: 1px solid #CBD5E1;">
                                <label style="color: var(--color-entrada); font-weight: 800; margin-bottom: 8px;">
                                    <i class="fa-solid fa-link"></i> Seleccionar Préstamo Pendiente (Salida de Origen)
                                </label>
                                <select id="selectPrestamo" name="id_salida_origen" class="form-control" style="background: white;" onchange="cargarProductosPrestamo()">
                                    <option value="">-- Seleccionar salida de préstamo anterior --</option>
                                    <?php foreach ($prestamos_activos as $prest): ?>
                                        <option value="<?php echo $prest['id_salida']; ?>" data-info='<?php echo json_encode($prest['productos']); ?>'>
                                            [<?php echo $prest['numero_documento']; ?>] Destino: <?php echo $prest['destino']; ?> (Resp: <?php echo $prest['responsable']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-top: 10px;">
                                <label>Operador que Recibe (Bodega)</label>
                                <div class="input-with-icon">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <input type="text" class="form-control" value="María Entradas (Bodega)" readonly>
                                </div>
                            </div>

                            <div class="form-section-title"><i class="fa-solid fa-boxes-stacked"></i> Detalle de los Productos Recibidos</div>

                            <!-- TABLA 1: ABASTECIMIENTO -->
                            <div id="tablaAbastecimientoContainer">
                                <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 8px;">
                                    En cada línea elija si desea reabastecer un producto existente del catálogo o registrar uno totalmente nuevo.
                                </p>
                                <table class="prod-table" id="tablaAbastecimiento">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%;">Tipo de Ingreso</th>
                                            <th style="width: 40%;">Selección / Datos del Producto</th>
                                            <th style="width: 20%;">Cantidad (> 0)</th>
                                            <th style="width: 15%; text-align: center;">Quitar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="ab_modo[]" class="form-control" style="padding-left: 8px;" onchange="cambiarModoAbastecimientoRow(this)" required>
                                                    <option value="catalogo">Producto del Catálogo</option>
                                                    <option value="nuevo">Producto Nuevo</option>
                                                </select>
                                            </td>
                                            <td class="col-producto-data">
                                                <select name="ab_producto_catalogo[]" class="form-control" style="padding-left: 8px;" required>
                                                    <option value="">-- Seleccionar del catálogo --</option>
                                                    <?php foreach ($productos_catalogo as $prod): ?>
                                                        <option value="<?php echo $prod['codigo']; ?>">[<?php echo $prod['codigo']; ?>] <?php echo $prod['nombre']; ?> (<?php echo $prod['unidad']; ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="number" name="ab_cantidad[]" class="form-control" style="padding-left: 8px;" value="1" min="1" required></td>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn-remove-row" onclick="eliminarFila(this)" title="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn-add-row" onclick="agregarFilaAbastecimiento()">
                                    <i class="fa-solid fa-plus"></i> Agregar otra línea de abastecimiento
                                </button>
                            </div>

                            <!-- TABLA 2: DEVOLUCIÓN -->
                            <div id="tablaDevolucionContainer" style="display: none;">
                                <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 8px;">
                                    Los productos se cargarán automáticamente al seleccionar el préstamo arriba. Indique la cantidad devuelta y su estado físico.
                                </p>
                                <table class="prod-table" id="tablaDevolucion">
                                    <thead>
                                        <tr>
                                            <th style="width: 45%;">Producto del Préstamo</th>
                                            <th style="width: 20%;">Cantidad a Devolver</th>
                                            <th style="width: 25%;">Estado del Ítem</th>
                                            <th style="width: 10%; text-align: center;">Quitar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyDevolucion">
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 15px;">
                                                Por favor seleccione primero un préstamo en el selector superior.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- MÓDULO DE OBSERVACIONES ADICIONALES -->
                            <div class="form-section-title" style="margin-top: 24px;"><i class="fa-solid fa-comment-dots"></i> Observaciones Adicionales</div>

                            <div class="form-group">
                                <label>Observaciones del Inventario (Opcional)</label>
                                <textarea name="observaciones" class="form-control" placeholder="Cualquier novedad o comentario adicional sobre las condiciones físicas..."></textarea>
                            </div>

                            <div class="form-actions">
                                <a href="entradas.php" class="btn-cancel"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                                <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Registrar y Actualizar Inventario</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function actualizarReloj() {
                        const ahora = new Date();
                        const anio = ahora.getFullYear();
                        const mes = String(ahora.getMonth() + 1).padStart(2, '0');
                        const dia = String(ahora.getDate()).padStart(2, '0');
                        const horas = String(ahora.getHours()).padStart(2, '0');
                        const minutos = String(ahora.getMinutes()).padStart(2, '0');
                        const segundos = String(ahora.getSeconds()).padStart(2, '0');
                        
                        const formatoFechaHora = `${anio}-${mes}-${dia} ${horas}:${minutos}:${segundos}`;
                        const campoReloj = document.getElementById('relojRecepcion');
                        if (campoReloj) {
                            campoReloj.value = formatoFechaHora;
                        }
                    }
                    setInterval(actualizarReloj, 1000);
                    window.onload = actualizarReloj;

                    const catsOptions = `
                        <?php foreach ($categorias_db as $key => $desc): ?>
                            <option value="<?php echo $key; ?>"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></option>
                        <?php endforeach; ?>
                    `;
                    const catProductsOptions = `
                        <?php foreach ($productos_catalogo as $prod): ?>
                            <option value="<?php echo $prod['codigo']; ?>">[<?php echo $prod['codigo']; ?>] <?php echo $prod['nombre']; ?> (<?php echo $prod['unidad']; ?>)</option>
                        <?php endforeach; ?>
                    `;

                    function alternarModoEntrada() {
                        const tipo = document.getElementById('tipoEntrada').value;
                        const labelQE = document.getElementById('labelQuienEntrega');
                        const iconQE = document.getElementById('iconQuienEntrega');
                        const labelMotivo = document.getElementById('labelMotivo');
                        const inputMotivo = document.getElementById('inputMotivo');
                        const groupSalida = document.getElementById('groupSalidaOrigen');
                        const tabAbast = document.getElementById('tablaAbastecimientoContainer');
                        const tabDev = document.getElementById('tablaDevolucionContainer');

                        if (tipo === 'abastecimiento') {
                            labelQE.innerText = "Nombre de Quien Entrega (Proveedor)";
                            iconQE.className = "fa-solid fa-building";
                            labelMotivo.innerText = "Motivo del Abastecimiento (Obligatorio para el Comprobante)";
                            inputMotivo.placeholder = "Escriba el motivo de la compra o abastecimiento de suministros...";
                            groupSalida.style.display = 'none';
                            tabAbast.style.display = 'block';
                            tabDev.style.display = 'none';
                        } else {
                            labelQE.innerText = "Responsable que devuelve el material";
                            iconQE.className = "fa-solid fa-hard-hat";
                            labelMotivo.innerText = "Motivo de la Devolución (Obligatorio para el Comprobante)";
                            inputMotivo.placeholder = "Escriba el motivo del retorno (Ej. Finalización de obra, cambio de herramientas, etc.)...";
                            groupSalida.style.display = 'block';
                            tabAbast.style.display = 'none';
                            tabDev.style.display = 'block';
                        }
                    }

                    function cambiarModoAbastecimientoRow(selectElem) {
                        const row = selectElem.closest('tr');
                        const cellData = row.querySelector('.col-producto-data');
                        const modo = selectElem.value;

                        if (modo === 'catalogo') {
                            cellData.innerHTML = `
                                <select name="ab_producto_catalogo[]" class="form-control" style="padding-left: 8px;" required>
                                    <option value="">-- Seleccionar del catálogo --</option>
                                    ${catProductsOptions}
                                </select>
                            `;
                        } else {
                            cellData.innerHTML = `
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                                    <input type="text" name="ab_codigo[]" class="form-control" style="padding-left: 6px;" placeholder="Código (ej. LIM-006)" required>
                                    <input type="text" name="ab_nombre[]" class="form-control" style="padding-left: 6px;" placeholder="Nombre del producto" required>
                                    <select name="ab_codigo_categoria[]" class="form-control" style="padding-left: 6px;" required>
                                        <option value="">-- Categoría --</option>
                                        ${catsOptions}
                                    </select>
                                    <div style="display: flex; gap: 4px;">
                                        <select name="ab_tipo[]" class="form-control" style="padding-left: 4px; font-size: 0.8rem;" required>
                                            <option value="consumible">Consumible</option>
                                            <option value="herramienta">Herramienta</option>
                                        </select>
                                        <input type="text" name="ab_unidad[]" class="form-control" style="padding-left: 6px;" placeholder="Unidad (ej. galón)" required>
                                    </div>
                                </div>
                            `;
                        }
                    }

                    function agregarFilaAbastecimiento() {
                        const tbody = document.querySelector('#tablaAbastecimiento tbody');
                        const nuevaFila = document.createElement('tr');
                        
                        nuevaFila.innerHTML = `
                            <td>
                                <select name="ab_modo[]" class="form-control" style="padding-left: 8px;" onchange="cambiarModoAbastecimientoRow(this)" required>
                                    <option value="catalogo">Producto del Catálogo</option>
                                    <option value="nuevo">Producto Nuevo</option>
                                </select>
                            </td>
                            <td class="col-producto-data">
                                <select name="ab_producto_catalogo[]" class="form-control" style="padding-left: 8px;" required>
                                    <option value="">-- Seleccionar del catálogo --</option>
                                    ${catProductsOptions}
                                </select>
                            </td>
                            <td><input type="number" name="ab_cantidad[]" class="form-control" style="padding-left: 8px;" value="1" min="1" required></td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-remove-row" onclick="eliminarFila(this)" title="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                            </td>
                        `;
                        tbody.appendChild(nuevaFila);
                    }

                    function cargarProductosPrestamo() {
                        const select = document.getElementById('selectPrestamo');
                        const tbody = document.getElementById('tbodyDevolucion');
                        
                        if (!select.value) {
                            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 15px;">Por favor seleccione primero un préstamo en el selector superior.</td></tr>`;
                            return;
                        }

                        const optionSelected = select.options[select.selectedIndex];
                        const productos = JSON.parse(optionSelected.getAttribute('data-info'));

                        tbody.innerHTML = '';
                        productos.forEach(prod => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>
                                    <strong>[${prod.codigo}]</strong> ${prod.nombre} 
                                    <small style="color: var(--text-muted); display: block;">Cantidad prestada: ${prod.prestado} ${prod.unidad}</small>
                                    <input type="hidden" name="dev_codigo[]" value="${prod.codigo}">
                                </td>
                                <td>
                                    <input type="number" name="dev_cantidad[]" class="form-control" style="padding-left: 8px;" value="${prod.prestado}" min="1" max="${prod.prestado}" oninput="validarMaximoDev(this, ${prod.prestado})" required>
                                    <small style="color: var(--text-muted); font-size: 0.65rem;">Máx: ${prod.prestado}</small>
                                </td>
                                <td>
                                    <select name="dev_estado_item[]" class="form-control" style="padding-left: 8px;" required>
                                        <option value="bueno">Bueno / Operativo</option>
                                        <option value="dañado">Dañado</option>
                                        <option value="perdido">Perdido</option>
                                    </select>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-remove-row" onclick="this.closest('tr').remove()" title="Quitar"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }

                    function validarMaximoDev(input, maxVal) {
                        let val = parseInt(input.value);
                        if (val > maxVal) {
                            alert("No puede devolver más cantidad de la que fue prestada (" + maxVal + ").");
                            input.value = maxVal;
                        }
                        if (val <= 0 || isNaN(val)) {
                            input.value = 1;
                        }
                    }

                    function eliminarFila(btn) {
                        const tabla = btn.closest('table');
                        if (tabla.rows.length > 2) {
                            btn.closest('tr').remove();
                        } else {
                            alert("Debe mantener al menos una línea en el detalle.");
                        }
                    }
                </script>

            <?php elseif ($action === 'voucher' && $voucher_actual): ?>
                <!-- VISTA 3: COMPROBANTE / VOUCHER OFICIAL -->
                <div class="module-header" style="margin-bottom: 15px;">
                    <div class="header-left">
                        <a href="entradas.php" class="btn-back" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-file-invoice"></i> Voucher Oficial de <?php echo ucfirst($voucher_actual['tipo_entrada']); ?></h1>
                            <p>Comprobante de recepción registrado en el sistema.</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button onclick="window.print();" class="btn-print"><i class="fa-solid fa-print"></i> Imprimir Voucher</button>
                    </div>
                </div>

                <div class="form-wrapper-scroll">
                    <div class="voucher-card">
                        <div class="voucher-header">
                            <h2>ALCALDÍA MUNICIPAL - SISTEMA DE GESTIÓN DE BODEGA (SIGEM)</h2>
                            <p>COMPROBANTE OFICIAL DE ENTRADA POR <?php echo strtoupper($voucher_actual['tipo_entrada']); ?></p>
                        </div>

                        <div class="alert-entrada"><i class="fa-solid fa-circle-check"></i> Ingreso registrado satisfactoriamente en la base de datos municipal.</div>

                        <div class="voucher-info-grid">
                            <div><strong>Nº de Voucher:</strong> <code><?php echo $voucher_actual['numero_voucher']; ?></code></div>
                            <div><strong>Fecha y Hora:</strong> <?php echo $voucher_actual['fecha']; ?></div>
                            <div style="grid-column: span 2;"><strong>Motivo de la <?php echo ucfirst($voucher_actual['tipo_entrada']); ?>:</strong> <?php echo htmlspecialchars($voucher_actual['motivo_entrada']); ?></div>
                            <div><strong>Quien Entrega:</strong> <?php echo htmlspecialchars($voucher_actual['nombre_quien_entrega']); ?></div>
                            <div><strong>Quien Recibe (Bodega):</strong> <?php echo htmlspecialchars($voucher_actual['nombre_quien_recibe']); ?></div>
                        </div>

                        <table class="prod-table" style="margin-bottom: 20px;">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción del Producto</th>
                                    <th>Detalle / Estado</th>
                                    <th>Cantidad Ingresada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($voucher_actual['productos'] as $prod): ?>
                                <tr>
                                    <td><code><?php echo $prod['codigo']; ?></code></td>
                                    <td><?php echo $prod['nombre']; ?></td>
                                    <td><span class="type-badge type-abastecimiento"><?php echo $prod['detalle']; ?></span></td>
                                    <td><strong><?php echo $prod['cantidad']; ?> <?php echo $prod['unidad']; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="font-size: 0.78rem; margin-bottom: 20px;">
                            <strong>Observaciones adicionales:</strong> <?php echo htmlspecialchars($voucher_actual['observaciones']); ?>
                        </div>

                        <!-- Firmas y Sello Oficial -->
                        <div class="voucher-footer-grid">
                            <div class="signature-box">
                                <div class="signature-line">
                                    <strong><?php echo htmlspecialchars($voucher_actual['nombre_quien_recibe']); ?></strong>
                                    <small>Firma de Bodega (Recibió)</small>
                                </div>
                            </div>
                            <div class="stamp-box">
                                <i class="fa-solid fa-certificate" style="font-size: 1.2rem; margin-bottom: 4px;"></i><br>
                                Sello Oficial de Bodega
                            </div>
                            <div class="signature-box">
                                <div class="signature-line">
                                    <strong><?php echo htmlspecialchars($voucher_actual['nombre_quien_entrega']); ?></strong>
                                    <small>Firma de Quien Entrega</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

</body>
</html>