<?php
// views/dashboard.php
$rol_usuario = "Administrador"; 
$nombre_persona = "Carlos Mendoza"; // Nombre real inventado para la persona logueada
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Bodega Municipal</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-page: #F4F8FB;
            --text-main: #0F172A;       
            --text-muted: #64748B;
            --border-light: #D9E2EC;
            --card-bg: #FFFFFF;
            
            --color-entrada: #00875A;     
            --color-salida: #A0522D;      
            --badge-bell: #E53E3E;
            
            --brand-title-color: #0284C7; 
            --brand-subtitle-color: #0284C7; 
            --header-black: #000000;      
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

        /* --- NAVBAR SUPERIOR --- */
        .navbar {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-light);
            padding: 8px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--header-black);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .nav-brand-icon {
            background: #E2E8F0;
            color: var(--header-black);
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-btn {
            position: relative;
            background: #F1F5F9;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: var(--badge-bell);
            color: white;
            font-size: 0.55rem;
            font-weight: 700;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            background: #0284C7;
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
            color: var(--brand-title-color);
        }

        .user-meta small {
            font-size: 0.68rem;
            color: var(--text-muted);
        }

        /* --- CONTENEDOR PRINCIPAL SIMÉTRICO --- */
        .main-container {
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
            padding: 10px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
        }

        /* --- ENCABEZADO Y SALUDO (Subido ligeramente para acercarlo al saludo) --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .welcome-title h1 {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--brand-title-color);
            margin-bottom: 1px;
        }

        .welcome-title p {
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 500;
        }

        .date-dropdown {
            background: white;
            border: 1px solid var(--border-light);
            padding: 5px 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .date-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-info i {
            color: var(--brand-subtitle-color);
            font-size: 0.85rem;
        }

        .date-info div span {
            display: block;
            font-size: 0.55rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
        }

        .date-info div strong {
            font-size: 0.72rem;
            color: var(--text-main);
        }

        /* --- TARJETAS DE ENTRADAS Y SALIDAS (Subidas justo abajo del saludo) --- */
        .metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 6px;
        }

        .metric-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .metric-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .metric-icon.entrada { background-color: var(--color-entrada); }
        .metric-icon.salida { background-color: var(--color-salida); }

        .metric-title h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 2px;
        }

        .metric-title p {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .metric-subtypes {
            display: flex;
            gap: 14px;
            text-align: right;
        }

        .subtype-box {
            border-left: 1px solid #E2E8F0;
            padding-left: 12px;
        }
        .subtype-box:first-child {
            border-left: none;
            padding-left: 0;
        }

        .subtype-box span {
            display: block;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .subtype-box small {
            display: block;
            font-size: 0.52rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        /* --- BANNER DE BODEGA AMPLIADO EXACTAMENTE DESDE ENTRADAS HASTA ACCESOS RÁPIDOS --- */
        .promo-banner {
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 41, 59, 0.85) 60%, rgba(15, 23, 42, 0.35) 100%), 
                        url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            color: white;
            /* Altura expandida para rellenar perfectamente todo el espacio intermedio */
            padding: 34px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            box-shadow: 0 4px 15px rgba(15,23,42,0.15);
            flex-grow: 1;
        }

        .promo-text h2 {
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: 3px;
            letter-spacing: -0.2px;
        }

        .promo-text p {
            font-size: 0.82rem;
            opacity: 0.9;
            max-width: 480px;
        }

        /* INSIGNIA REAL CON MANITAS UNIDAS (SIGEM) */
        .sigem-emblem-badge {
            background: radial-gradient(circle, #ffffff 65%, #f1f5f9 100%);
            border: 2px solid rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            width: 86px;
            height: 86px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            padding: 6px;
            text-align: center;
            flex-shrink: 0;
        }

        .sigem-hands-icon {
            color: #0284C7;
            font-size: 1.3rem;
            margin-bottom: 1px;
        }

        .sigem-logo-text {
            font-size: 1.15rem;
            font-weight: 900;
            letter-spacing: 0.6px;
            color: #C25E00;
            line-height: 1;
        }

        .sigem-logo-sub {
            font-size: 0.38rem;
            color: #64748B;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.2px;
            border-top: 1px solid #CBD5E1;
            width: 100%;
            margin-top: 1px;
            padding-top: 1px;
        }

        /* --- ACCESOS RÁPIDOS SIMÉTRICOS --- */
        .section-header {
            margin-bottom: 4px;
        }

        .section-header h3 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-header h3 i {
            color: var(--brand-subtitle-color);
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 2px;
        }

        .quick-card {
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 10px 12px;
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 98px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
        }

        .quick-card:hover {
            transform: translateY(-2px);
            border-color: #0284C7;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.08);
        }

        .quick-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .quick-card-icon {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
        }

        .arrow-action {
            color: var(--text-muted);
            background: #F1F5F9;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.5rem;
        }

        .quick-card:hover .arrow-action {
            background: #0284C7;
            color: white;
        }

        /* Colores institucionales para iconos de accesos rápidos */
        .ic-usuarios { background-color: #0284C7; }
        .ic-entradas { background-color: var(--color-entrada); }
        .ic-salidas  { background-color: var(--color-salida); }
        .ic-inventario { background-color: #4F46E5; }
        .ic-reportes { background-color: #D97706; }
        .ic-backup { background-color: #7C3AED; }

        .quick-card-body h4 {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 1px;
        }

        .quick-card-body p {
            font-size: 0.58rem;
            color: var(--text-muted);
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>

    <!-- Navbar Superior -->
    <header class="navbar">
        <div class="nav-brand">
            <div class="nav-brand-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            Sistema de Bodega Municipal
        </div>
        <div class="nav-right">
            <!-- Campana para alertas de stock -->
            <button class="notification-btn" title="Alertas de Stock">
                <i class="fa-solid fa-bell"></i>
                <span class="notification-badge">2</span>
            </button>
            <div class="user-pill">
                <div class="user-avatar"><?php echo substr($rol_usuario, 0, 1); ?></div>
                <div class="user-meta">
                    <span><?php echo htmlspecialchars($rol_usuario); ?></span>
                    <small><?php echo htmlspecialchars($nombre_persona); ?></small>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido Principal Fijo -->
    <main class="main-container">
        
        <!-- Saludo y Selector de Fecha -->
        <div class="header-section">
            <div class="welcome-title">
                <h1>¡Hola, <?php echo htmlspecialchars($rol_usuario); ?>!</h1>
                <p>Bienvenido al Sistema de Gestión de Bodega Municipal.</p>
            </div>
            <div class="date-dropdown">
                <div class="date-info">
                    <i class="fa-regular fa-calendar-days"></i>
                    <div>
                        <span>Hoy</span>
                        <strong>15 de Septiembre de 2026</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Entradas y Salidas reubicadas más cerca del saludo -->
        <div class="metrics-grid">
            <!-- Entradas: Abastecimiento y Préstamos -->
            <div class="metric-card">
                <div class="metric-left">
                    <div class="metric-icon entrada">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                    <div class="metric-title">
                        <h3>Entradas</h3>
                        <p>Ingresos al inventario</p>
                    </div>
                </div>
                <div class="metric-subtypes">
                    <div class="subtype-box">
                        <span>35</span>
                        <small>Abastec.</small>
                    </div>
                    <div class="subtype-box">
                        <span>12</span>
                        <small>Préstamo</small>
                    </div>
                </div>
            </div>

            <!-- Salidas: Consumo y Préstamos -->
            <div class="metric-card">
                <div class="metric-left">
                    <div class="metric-icon salida">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                    <div class="metric-title">
                        <h3>Salidas</h3>
                        <p>Despachos de inventario</p>
                    </div>
                </div>
                <div class="metric-subtypes">
                    <div class="subtype-box">
                        <span>28</span>
                        <small>Consumo</small>
                    </div>
                    <div class="subtype-box">
                        <span>9</span>
                        <small>Préstamo</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner de Bodega extendido exactamente desde Entradas/Salidas hasta Accesos Rápidos -->
        <div class="promo-banner">
            <div class="promo-text">
                <h2>Control de inventario, al alcance de un clic</h2>
                <p>Un sistema eficiente para una mejor gestión de nuestros recursos.</p>
            </div>
            <div class="sigem-emblem-badge">
                <i class="fa-solid fa-handshake-angle sigem-hands-icon"></i>
                <div class="sigem-logo-text">SIGEM</div>
                <div class="sigem-logo-sub">Municipal</div>
            </div>
        </div>

        <!-- Accesos Rápidos Simétricos -->
        <div>
            <div class="section-header">
                <h3><i class="fa-solid fa-grip"></i> Accesos rápidos</h3>
            </div>

            <div class="quick-grid">
                <!-- Usuarios -->
                <a href="usuarios.php" class="quick-card">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-usuarios">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Usuarios</h4>
                        <p>Gestionar personal</p>
                    </div>
                </a>

                <!-- Entradas -->
                <a href="entradas.php" class="quick-card">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-entradas">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Entradas</h4>
                        <p>Abastec. y préstamos</p>
                    </div>
                </a>

                <!-- Salidas -->
                <a href="salidas.php" class="quick-card">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-salidas">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Salidas</h4>
                        <p>Consumo y préstamos</p>
                    </div>
                </a>

                <!-- Inventario -->
                <a href="inventario.php" class="quick-card">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-inventario">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Inventario</h4>
                        <p>Stock disponible</p>
                    </div>
                </a>

                <!-- Reportes -->
                <a href="reportes.php" class="quick-card">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-reportes">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Reportes</h4>
                        <p>Consultas y PDF</p>
                    </div>
                </a>

                <!-- Respaldo / Backup BD -->
                <a href="Respaldo.php" class="quick-card">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-backup">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Respaldo</h4>
                        <p>Copia de seguridad</p>
                    </div>
                </a>
            </div>
        </div>

    </main>

</body>
</html>