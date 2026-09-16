<?php
// views/usuarios.php
$action = $_GET['action'] ?? 'listar';
$id_editar = $_GET['id'] ?? null;

// Datos de ejemplo basados en los registros de la base de datos
$usuarios_db = [
    [
        "id_usuario" => 3, 
        "cedula" => "001-987654-0003C", 
        "nombre_usuario" => "Juan Salidas", 
        "rol" => "Operador_Salidas", 
        "activo" => 1, 
        "fecha_creacion" => "2026-07-18 15:45:02"
    ],
    [
        "id_usuario" => 2, 
        "cedula" => "001-654321-0002B", 
        "nombre_usuario" => "María Entradas", 
        "rol" => "Operador_Entradas", 
        "activo" => 1, 
        "fecha_creacion" => "2026-07-18 15:45:02"
    ],
    [
        "id_usuario" => 1, 
        "cedula" => "001-123456-0001A", 
        "nombre_usuario" => "Carlos Admin", 
        "rol" => "Administrador", 
        "activo" => 1, 
        "fecha_creacion" => "2026-07-18 15:45:02"
    ]
];

$usuario_actual = null;
if ($action === 'editar' && $id_editar) {
    foreach ($usuarios_db as $u) {
        if ($u['id_usuario'] == $id_editar) {
            $usuario_actual = $u;
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
    <title>Gestión de Usuarios - SIGEM</title>
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
            --color-accent: #733E24;     
            --color-success: #00875A;    
            --color-danger: #E53E3E;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        /* Bloqueo de scroll para mantener la vista en un solo plano exacto de 14" */
        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* NAVBAR SUPERIOR */
        .navbar {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-light);
            padding: 10px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #000000;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .nav-brand-icon {
            background: #E2E8F0;
            color: var(--color-primary);
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            background: var(--color-primary);
            color: white;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .user-meta span {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .user-meta small {
            font-size: 0.68rem;
            color: var(--text-muted);
        }

        /* CONTENEDOR EXTERNO CON TRASFONDO AZULADO TRANSPARENTE */
        .page-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px; /* Reducido para dar justa separación con los bordes */
            background: radial-gradient(circle, rgba(36, 95, 115, 0.08) 0%, rgba(187, 189, 188, 0.15) 100%);
        }

        /* CONTENEDOR PRINCIPAL AMPLIO */
        .main-container {
            max-width: 1420px;
            width: 98%;
            height: 90vh;
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 20px 24px; /* Ajuste interno equilibrado */
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 15px rgba(36, 95, 115, 0.08);
            overflow: hidden;
        }

        /* CABECERA DEL MÓDULO */
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--bg-page);
            padding-bottom: 14px;
            margin-bottom: 16px;
            flex-shrink: 0;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        /* BOTÓN DE RETORNO AL DASHBOARD */
        .btn-back {
            background-color: #E2E8F0;
            color: var(--text-main);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: var(--color-primary);
            color: white;
        }

        .module-title h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2px;
        }

        .module-title h1 i {
            color: var(--color-primary);
        }

        .module-title p {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-add {
            background-color: var(--color-success);
            color: white;
            border: none;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 0.83rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
            text-decoration: none;
        }

        .btn-add:hover { opacity: 0.9; }

        /* TABLA DE DATOS */
        .table-responsive {
            flex-grow: 1;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background-color: #FAFAFA;
            color: var(--text-muted);
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-light);
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 13px 16px;
            font-size: 0.87rem;
            color: var(--text-main);
            border-bottom: 1px solid #F1F5F9;
        }

        tr:hover { background-color: #F8FAFC; }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .role-admin { background: #FFEDD5; color: var(--color-accent); }
        .role-entradas { background: #E0F2FE; color: #0369A1; }
        .role-salidas { background: #FEF3C7; color: #D97706; }

        .status-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-active { background-color: var(--color-success); }
        .status-inactive { background-color: var(--color-danger); }

        .action-btns {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid var(--border-light);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-edit { color: var(--color-primary); }
        .btn-edit:hover { background: var(--color-primary); color: white; border-color: var(--color-primary); }

        .btn-delete { color: var(--color-danger); }
        .btn-delete:hover { background: var(--color-danger); color: white; border-color: var(--color-danger); }

        /* ESTILOS AVANZADOS Y ORDENADOS PARA LOS FORMULARIOS */
        .form-wrapper-scroll {
            flex-grow: 1;
            overflow-y: auto;
            padding: 5px 10px;
        }

        .form-container-structured {
            max-width: 850px;
            margin: 0 auto;
            background: #FFFFFF;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 24px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .form-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--color-primary);
            text-transform: uppercase;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 6px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-title:not(:first-child) {
            margin-top: 24px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-with-icon i {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-size: 0.88rem;
            outline: none;
            background-color: #FAFAFA;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--color-primary);
            background-color: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(36, 95, 115, 0.1);
        }

        select.form-control {
            padding-left: 38px;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            border-top: 1px solid var(--bg-page);
            padding-top: 16px;
        }

        .btn-cancel {
            background: #E2E8F0;
            color: var(--text-main);
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 0.83rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-cancel:hover { background: #CBD5E1; }

        .btn-save { background: var(--color-success); color: white; border: none; padding: 9px 20px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-save:hover { opacity: 0.9; }

        .btn-update { background: var(--color-primary); color: white; border: none; padding: 9px 20px; border-radius: 8px; font-size: 0.83rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-update:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            Sistema de Bodega Municipal
        </div>
        <div class="user-pill">
            <div class="user-avatar">C</div>
            <div class="user-meta">
                <span>Carlos Admin</span>
                <small>Administrador</small>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <main class="main-container">

            <?php if ($action === 'listar'): ?>
                <!-- VISTA 1: LISTADO PRINCIPAL -->
                <div class="module-header">
                    <div class="header-left">
                        <!-- Botón para volver al Dashboard -->
                        <a href="dashboard.php" class="btn-back" title="Volver al Dashboard">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-user-gear"></i> Gestión de Usuarios</h1>
                            <p>Administra los usuarios, cédulas, roles y permisos de acceso al sistema municipal (Máximo 5).</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="usuarios.php?action=agregar" class="btn-add">
                            <i class="fa-solid fa-user-plus"></i> Agregar Usuario
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cédula</th>
                                <th>Nombre de Usuario</th>
                                <th>Rol / Permisos</th>
                                <th>Estado</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios_db as $usr): ?>
                            <tr>
                                <td><strong><?php echo $usr['id_usuario']; ?></strong></td>
                                <td><code><?php echo htmlspecialchars($usr['cedula']); ?></code></td>
                                <td><?php echo htmlspecialchars($usr['nombre_usuario']); ?></td>
                                <td>
                                    <?php 
                                        $clase_rol = 'role-admin';
                                        if($usr['rol'] == 'Operador_Entradas') $clase_rol = 'role-entradas';
                                        elseif($usr['rol'] == 'Operador_Salidas') $clase_rol = 'role-salidas';
                                    ?>
                                    <span class="role-badge <?php echo $clase_rol; ?>">
                                        <i class="fa-solid fa-shield-halved"></i> <?php echo str_replace('_', ' ', $usr['rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($usr['activo'] == 1): ?>
                                        <span class="status-badge status-active"></span> Activo
                                    <?php else: ?>
                                        <span class="status-badge status-inactive"></span> Inactivo
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $usr['fecha_creacion']; ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="usuarios.php?action=editar&id=<?php echo $usr['id_usuario']; ?>" class="btn-action btn-edit" title="Editar usuario">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="#" class="btn-action btn-delete" title="Eliminar usuario" onclick="return confirm('¿Está seguro de eliminar este usuario?');">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($action === 'agregar'): ?>
                <!-- VISTA 2: FORMULARIO AGREGAR USUARIO (ESTRUCTURADO Y ORDENADO) -->
                <div class="module-header" style="margin-bottom: 15px;">
                    <div class="header-left">
                        <a href="usuarios.php" class="btn-back" title="Volver al listado">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-user-plus"></i> Registrar Nuevo Usuario</h1>
                            <p>Complete los campos estructurados para dar de alta un nuevo miembro en el sistema.</p>
                        </div>
                    </div>
                </div>

                <div class="form-wrapper-scroll">
                    <div class="form-container-structured">
                        <form action="usuarios.php" method="POST">
                            
                            <div class="form-section-title">
                                <i class="fa-solid fa-id-card"></i> Información de Identificación
                            </div>
                            
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Número de Cédula</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-address-card"></i>
                                        <input type="text" class="form-control" placeholder="Ej. 001-XXXXXX-XXXXX" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Usuario</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-user"></i>
                                        <input type="text" class="form-control" placeholder="Ej. operador_2" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-title">
                                <i class="fa-solid fa-lock"></i> Seguridad y Permisos de Acceso
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Contraseña de Acceso</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-key"></i>
                                        <input type="password" class="form-control" placeholder="••••••••" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Rol Asignado</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-shield-halved"></i>
                                        <select class="form-control" required>
                                            <option value="">Seleccione un rol...</option>
                                            <option value="Administrador">Administrador (Máx. 1)</option>
                                            <option value="Operador_Entradas">Operador de Entradas</option>
                                            <option value="Operador_Salidas">Operador de Salidas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="usuarios.php" class="btn-cancel"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                                <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Guardar Usuario</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($action === 'editar' && $usuario_actual): ?>
                <!-- VISTA 3: FORMULARIO EDITAR USUARIO (ESTRUCTURADO Y ORDENADO) -->
                <div class="module-header" style="margin-bottom: 15px;">
                    <div class="header-left">
                        <a href="usuarios.php" class="btn-back" title="Volver al listado">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <div class="module-title">
                            <h1><i class="fa-solid fa-user-pen"></i> Editar Usuario (ID: <?php echo $usuario_actual['id_usuario']; ?>)</h1>
                            <p>Modifique de forma segura los datos y permisos de acceso para este usuario.</p>
                        </div>
                    </div>
                </div>

                <div class="form-wrapper-scroll">
                    <div class="form-container-structured">
                        <form action="usuarios.php" method="POST">
                            
                            <div class="form-section-title">
                                <i class="fa-solid fa-id-card"></i> Información de Identificación
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Número de Cédula</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-address-card"></i>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario_actual['cedula']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Usuario</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-user"></i>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario_actual['nombre_usuario']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-title">
                                <i class="fa-solid fa-lock"></i> Seguridad y Permisos de Acceso
                            </div>

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Nueva Contraseña (Opcional)</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-key"></i>
                                        <input type="password" class="form-control" placeholder="Dejar en blanco para conservar">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Rol Asignado</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-shield-halved"></i>
                                        <select class="form-control" required>
                                            <option value="Administrador" <?php if($usuario_actual['rol']=='Administrador') echo 'selected'; ?>>Administrador</option>
                                            <option value="Operador_Entradas" <?php if($usuario_actual['rol']=='Operador_Entradas') echo 'selected'; ?>>Operador de Entradas</option>
                                            <option value="Operador_Salidas" <?php if($usuario_actual['rol']=='Operador_Salidas') echo 'selected'; ?>>Operador de Salidas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="usuarios.php" class="btn-cancel"><i class="fa-solid fa-xmark"></i> Cancelar</a>
                                <button type="submit" class="btn-update"><i class="fa-solid fa-check"></i> Actualizar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

</body>
</html>