<?php
// views/Dashboard.php
require_once __DIR__ . '/../config/Auth.php';
auth_requerir();

$rol_usuario = auth_nombre_rol();   // Lo que se muestra en "¡Hola, ...!" según el usuario que inició sesión

$hoy = date('Y-m-d');
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
    
        /* --- Campana de alertas y menú --- */
        .nav-right { position: relative; }
        .notification-btn.sin-alertas .notification-badge { display: none; }
        .logout-btn { background: #F1F5F9; border: none; height: 34px; padding: 0 12px; border-radius: 17px; color: var(--text-muted); font-size: 0.72rem; font-weight: 700; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .logout-btn:hover { background: #E2E8F0; color: #C53030; }
        .alert-panel { display: none; position: absolute; top: 44px; right: 0; width: 390px; max-height: 420px; overflow-y: auto; background: white; border: 1px solid var(--border-light); border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 50; }
        .alert-panel.open { display: block; }
        .alert-panel-title { padding: 10px 14px; font-size: 0.78rem; font-weight: 800; border-bottom: 1px solid var(--border-light); }
        .alert-item { display: flex; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #F1F5F9; text-decoration: none; color: inherit; }
        .alert-item:last-child { border-bottom: none; }
        a.alert-item:hover { background: #F8FAFC; }
        .alert-dot { flex: 0 0 8px; height: 8px; border-radius: 50%; margin-top: 5px; }
        .alert-dot.critica { background: #E53E3E; }
        .alert-dot.advertencia { background: #DD6B20; }
        .alert-item strong { display: block; font-size: 0.78rem; color: var(--text-main); }
        .alert-item span { display: block; font-size: 0.7rem; color: var(--text-muted); margin-top: 2px; line-height: 1.35; }
        .alert-empty { padding: 22px 14px; text-align: center; font-size: 0.78rem; color: var(--text-muted); }
        .date-input { border: none; outline: none; font-size: 0.78rem; font-weight: 700; color: var(--text-main); background: transparent; font-family: inherit; cursor: pointer; }
        .flash-aviso { background: #FFFAF0; border: 1px solid #FBD38D; color: #9C4221; padding: 8px 14px; border-radius: 8px; font-size: 0.8rem; margin-bottom: 8px; }
    
        /* --- Ajustes visuales --- */
        body { background: linear-gradient(180deg, #EEF5FD 0%, #E2EDF9 100%); background-attachment: fixed; }
        .navbar { background-color: #FFFFFF; }
        .nav-brand { font-size: 1.2rem; color: var(--header-black); }
        .nav-brand-icon { width: 38px; height: 38px; }
        .welcome-title h1 { font-size: 1.1rem; color: var(--header-black); }
        .nav-right { gap: 12px; }
        .notification-btn { width: 32px; height: 32px; font-size: 0.85rem; background: linear-gradient(135deg, #FFB347, #FF7A18); color: #fff; font-size: 1rem; box-shadow: 0 3px 10px rgba(255, 122, 24, 0.35); transition: transform .15s; }
        .notification-btn:hover { transform: scale(1.07); }
        .notification-btn.sin-alertas { background: linear-gradient(135deg, #FFD08A, #FFB056); box-shadow: 0 2px 6px rgba(255, 176, 86, 0.35); }
        .notification-btn:not(.sin-alertas) { background: linear-gradient(135deg, #FF5F6D, #E53E3E); box-shadow: 0 3px 12px rgba(229, 62, 62, 0.45); }
        .notification-btn:not(.sin-alertas) i { animation: campana 1.8s ease-in-out infinite; transform-origin: top center; }
        .notification-badge { top: -5px; right: -5px; width: 16px; height: 16px; font-size: 0.58rem; background: #1E3A5F; border: 2px solid #fff; }
        @keyframes campana { 0%, 60%, 100% { transform: rotate(0); } 10% { transform: rotate(16deg); } 20% { transform: rotate(-14deg); } 30% { transform: rotate(10deg); } 40% { transform: rotate(-8deg); } 50% { transform: rotate(4deg); } }
        .logout-btn { height: 38px; padding: 0 18px; border-radius: 19px; background: linear-gradient(135deg, #1E3A5F, #2B6CB0); color: #fff; font-size: 0.78rem; letter-spacing: .3px; box-shadow: 0 3px 10px rgba(30, 58, 95, 0.3); transition: transform .15s, box-shadow .15s; }
        .logout-btn:hover { background: linear-gradient(135deg, #2B6CB0, #3182CE); color: #fff; transform: translateY(-1px); box-shadow: 0 5px 14px rgba(30, 58, 95, 0.4); }
        .alert-panel { top: 44px; display: block; visibility: hidden; opacity: 0; filter: blur(8px);
                       transform: translate(30px, -24px) scale(0.25); transform-origin: 85% 0; pointer-events: none;
                       transition: transform 0.75s cubic-bezier(.22, .61, .36, 1), opacity 0.6s ease, filter 0.6s ease, visibility 0s linear 0.75s; }
        .alert-panel.open { visibility: visible; opacity: 1; filter: blur(0); transform: none; pointer-events: auto;
                            transition-delay: 0s; }
        /* Título "Sistema de Bodega Municipal" e icono con el mismo color del botón Salir */
        .nav-brand { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
        .nav-brand-icon { -webkit-text-fill-color: initial; }
        .nav-brand-icon i { background: linear-gradient(135deg, #1E3A5F, #2B6CB0); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }

    
        /* --- Accesos rápidos: botón, efecto humo y carrito --- */
        .metric-card[data-modulo] { cursor: pointer; }
        .quick-wrap { position: relative; }
        .accesos-btn { background: none; border: none; padding: 0; margin: 0; font: inherit; color: inherit; display: flex; align-items: center; gap: 6px; cursor: pointer; }
        .quick-grid.animando .quick-card {
            transition: transform 1.5s cubic-bezier(.22, .61, .36, 1), opacity 1.3s ease, filter 1.3s ease;
            transition-delay: calc(var(--i, 0) * 160ms);
        }
        .quick-grid.animando.collapsed .quick-card {
            transition-duration: 1.1s, 1s, 1s;
            transition-delay: calc((5 - var(--i, 0)) * 110ms);
        }
        .quick-grid.collapsed .quick-card {
            opacity: 0;
            filter: blur(10px);
            transform: translate(var(--dx, 0px), var(--dy, 0px)) scale(0.3);
            pointer-events: none;
        }
        .carrito { position: absolute; left: 0; top: 0; width: 150px; height: 58px; opacity: 0; pointer-events: none; z-index: 20;
                   filter: drop-shadow(0 3px 5px rgba(30, 58, 95, 0.3)); }
        .carrito svg { display: block; width: 100%; height: 100%; }
        .carrito.activo { pointer-events: auto; cursor: pointer; }
        .carretera { position: absolute; left: 0; width: 100%; height: 16px; opacity: 0; pointer-events: none; z-index: 10;
                     background: linear-gradient(180deg, rgba(15, 23, 42, 0) 0%, rgba(15, 23, 42, 0.38) 35%, rgba(15, 23, 42, 0.5) 65%, rgba(15, 23, 42, 0) 100%);
                     -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 6%, #000 80%, transparent 100%);
                     mask-image: linear-gradient(90deg, transparent 0%, #000 6%, #000 80%, transparent 100%); }
        .carretera::after { content: ''; position: absolute; left: 0; right: 0; top: 50%; height: 2px; margin-top: -1px;
                            background: repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.4) 0 14px, transparent 14px 30px); }
        .paquete { position: absolute; left: 0; top: 0; opacity: 0; pointer-events: none; z-index: 15;
                   filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3)); }
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
            <!-- Campana de alertas: préstamos sin devolver, vencimientos y stock mínimo -->
            <button class="notification-btn sin-alertas" id="btnAlertas" title="Alertas" type="button">
                <i class="fa-solid fa-bell"></i>
                <span class="notification-badge" id="badgeAlertas">0</span>
            </button>
            <div class="alert-panel" id="panelAlertas">
                <div class="alert-panel-title"><i class="fa-solid fa-bell"></i> Alertas del sistema</div>
                <div id="listaAlertas"><div class="alert-empty">Cargando...</div></div>
            </div>
            <a href="../index.php?route=logout" class="logout-btn" title="Cerrar sesión"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
        </div>
    </header>

    <!-- Contenido Principal Fijo -->
    <main class="main-container">
        
        <?php if (($_GET['msg'] ?? '') === 'sin_permiso'): ?>
            <div class="flash-aviso"><i class="fa-solid fa-lock"></i> Acceso no autorizado.</div>
        <?php endif; ?>

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
                        <span>Consultar movimientos del día</span>
                        <input type="date" id="fechaDia" class="date-input" value="<?php echo $hoy; ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Entradas y Salidas reubicadas más cerca del saludo -->
        <div class="metrics-grid">
            <!-- Entradas: Abastecimiento y Préstamos -->
            <div class="metric-card" data-permiso="<?php echo auth_puede('entradas') ? '1' : '0'; ?>" <?php echo auth_puede('entradas') ? 'data-modulo="Entradas.php" title="Ver las entradas de este día"' : ''; ?>>
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
                        <span id="cntEntAbast">0</span>
                        <small>Abastec.</small>
                    </div>
                    <div class="subtype-box">
                        <span id="cntEntPrest">0</span>
                        <small>Préstamo</small>
                    </div>
                </div>
            </div>

            <!-- Salidas: Consumo y Préstamos -->
            <div class="metric-card" data-permiso="<?php echo auth_puede('salidas') ? '1' : '0'; ?>" <?php echo auth_puede('salidas') ? 'data-modulo="Salidas.php" title="Ver las salidas de este día"' : ''; ?>>
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
                        <span id="cntSalCons">0</span>
                        <small>Consumo</small>
                    </div>
                    <div class="subtype-box">
                        <span id="cntSalPrest">0</span>
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
        <div class="quick-wrap" id="quickWrap">
            <div class="section-header">
                <h3><button type="button" id="btnAccesos" class="accesos-btn" aria-expanded="true" title="Mostrar u ocultar los módulos"><i class="fa-solid fa-grip"></i> Accesos rápidos</button></h3>
            </div>

            <div class="carretera" id="carretera"></div>
            <div class="carrito" id="carrito" title="Toca el tráiler para mostrar los módulos">
                <svg viewBox="0 0 150 58" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <linearGradient id="trBox" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F8FAFC"/><stop offset="1" stop-color="#CBD5E1"/></linearGradient>
                        <linearGradient id="trCab" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#3182CE"/><stop offset="1" stop-color="#1E3A5F"/></linearGradient>
                    </defs>
                    <ellipse cx="76" cy="55.5" rx="68" ry="2.2" fill="rgba(15,23,42,0.28)"/>
                    <!-- remolque -->
                    <rect x="3" y="6" width="94" height="35" rx="3" fill="url(#trBox)" stroke="#94A3B8" stroke-width="0.8"/>
                    <rect x="3" y="33" width="94" height="5" fill="#2B6CB0"/>
                    <rect x="3" y="6" width="4" height="35" rx="1.5" fill="#CBD5E1"/>
                    <path d="M10 9 V30 M90 9 V30" stroke="#E2E8F0" stroke-width="1"/>
                    <text x="50" y="26" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="800" fill="#1E3A5F" text-anchor="middle" letter-spacing="1">SIGEM</text>
                    <!-- chasis y enganche -->
                    <rect x="2" y="41" width="142" height="5" rx="2" fill="#1E293B"/>
                    <rect x="95" y="38" width="9" height="4" fill="#475569"/>
                    <!-- cabina -->
                    <rect x="106" y="9" width="3.4" height="15" rx="1" fill="#64748B"/>
                    <path d="M101 16 H126 L141 30 V42 H101 Z" fill="url(#trCab)"/>
                    <path d="M120 19.5 H126 L134.5 29 H120 Z" fill="#BEE3F8"/>
                    <rect x="136" y="37" width="9" height="6" rx="1.5" fill="#94A3B8"/>
                    <circle cx="140.5" cy="33" r="2.1" fill="#F6E05E"/>
                    <rect x="101" y="30" width="19" height="2" fill="#FFFFFF" opacity="0.25"/>
                    <!-- ruedas -->
                    <circle cx="20" cy="47" r="7" fill="#0F172A"/><circle cx="20" cy="47" r="3" fill="#94A3B8"/>
                    <circle cx="36" cy="47" r="7" fill="#0F172A"/><circle cx="36" cy="47" r="3" fill="#94A3B8"/>
                    <circle cx="112" cy="47" r="7" fill="#0F172A"/><circle cx="112" cy="47" r="3" fill="#94A3B8"/>
                    <circle cx="130" cy="47" r="7" fill="#0F172A"/><circle cx="130" cy="47" r="3" fill="#94A3B8"/>
                </svg>
            </div>

            <div class="quick-grid" id="quickGrid">
                <!-- Usuarios -->
                <a href="Usuarios.php" class="quick-card" data-permiso="<?php echo auth_puede('usuarios') ? '1' : '0'; ?>">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-usuarios">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid <?php echo auth_puede('usuarios') ? 'fa-arrow-right' : 'fa-lock'; ?>"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Usuarios</h4>
                        <p>Gestionar personal</p>
                    </div>
                </a>

                <!-- Entradas -->
                <a href="Entradas.php" class="quick-card" data-permiso="<?php echo auth_puede('entradas') ? '1' : '0'; ?>">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-entradas">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid <?php echo auth_puede('entradas') ? 'fa-arrow-right' : 'fa-lock'; ?>"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Entradas</h4>
                        <p>Abastec. y préstamos</p>
                    </div>
                </a>

                <!-- Salidas -->
                <a href="Salidas.php" class="quick-card" data-permiso="<?php echo auth_puede('salidas') ? '1' : '0'; ?>">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-salidas">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid <?php echo auth_puede('salidas') ? 'fa-arrow-right' : 'fa-lock'; ?>"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Salidas</h4>
                        <p>Consumo y préstamos</p>
                    </div>
                </a>

                <!-- Inventario -->
                <a href="Inventario.php" class="quick-card" data-permiso="<?php echo auth_puede('inventario') ? '1' : '0'; ?>">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-inventario">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid <?php echo auth_puede('inventario') ? 'fa-arrow-right' : 'fa-lock'; ?>"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Inventario</h4>
                        <p>Stock disponible</p>
                    </div>
                </a>

                <!-- Reportes -->
                <a href="Reportes.php" class="quick-card" data-permiso="<?php echo auth_puede('reportes') ? '1' : '0'; ?>">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-reportes">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid <?php echo auth_puede('reportes') ? 'fa-arrow-right' : 'fa-lock'; ?>"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Reportes</h4>
                        <p>Consultas y PDF</p>
                    </div>
                </a>

                <!-- Respaldo / Backup BD -->
                <a href="Respaldo.php" class="quick-card" data-permiso="<?php echo auth_puede('respaldo') ? '1' : '0'; ?>">
                    <div class="quick-card-top">
                        <div class="quick-card-icon ic-backup">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div class="arrow-action"><i class="fa-solid <?php echo auth_puede('respaldo') ? 'fa-arrow-right' : 'fa-lock'; ?>"></i></div>
                    </div>
                    <div class="quick-card-body">
                        <h4>Respaldo</h4>
                        <p>Copia de seguridad</p>
                    </div>
                </a>
            </div>
        </div>

    </main>

    <script>
    // Campana de alertas y resumen de movimientos por día
    (function () {
        const API = '../controllers/ApiController.php';
        const btn = document.getElementById('btnAlertas');
        const panel = document.getElementById('panelAlertas');
        const lista = document.getElementById('listaAlertas');
        const badge = document.getElementById('badgeAlertas');
        const inputFecha = document.getElementById('fechaDia');

        function esc(t) { const d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }

        function cargarAlertas() {
            fetch(API + '?action=alertas', { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    const alertas = data.alertas || [];
                    badge.textContent = alertas.length > 99 ? '99+' : alertas.length;
                    btn.classList.toggle('sin-alertas', alertas.length === 0);
                    if (alertas.length === 0) {
                        lista.innerHTML = '<div class="alert-empty"><i class="fa-regular fa-circle-check"></i> No hay alertas por el momento.</div>';
                        return;
                    }
                    lista.innerHTML = alertas.map(function (a) {
                        const cuerpo = '<div class="alert-dot ' + esc(a.nivel) + '"></div><div><strong>' + esc(a.titulo) + '</strong><span>' + esc(a.detalle) + '</span></div>';
                        return a.enlace ? '<a class="alert-item" href="' + esc(a.enlace) + '">' + cuerpo + '</a>'
                                        : '<div class="alert-item">' + cuerpo + '</div>';
                    }).join('');
                })
                .catch(function () { lista.innerHTML = '<div class="alert-empty">No se pudieron cargar las alertas.</div>'; });
        }

        function cargarResumen() {
            if (!inputFecha.value) return;
            fetch(API + '?action=resumen&fecha=' + encodeURIComponent(inputFecha.value), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (!d.entradas) return;
                    document.getElementById('cntEntAbast').textContent = d.entradas.abastecimiento;
                    document.getElementById('cntEntPrest').textContent = d.entradas.prestamo;
                    document.getElementById('cntSalCons').textContent = d.salidas.consumo;
                    document.getElementById('cntSalPrest').textContent = d.salidas.prestamo;
                });
        }

        btn.addEventListener('click', function (e) { e.stopPropagation(); panel.classList.toggle('open'); });
        document.addEventListener('click', function (e) { if (!panel.contains(e.target)) panel.classList.remove('open'); });
        inputFecha.addEventListener('change', cargarResumen);

        // Al hacer clic en Entradas/Salidas (si su rol lo permite) se abre el listado de ese día
        document.querySelectorAll('.metric-card[data-modulo]').forEach(function (c) {
            c.addEventListener('click', function () {
                window.location.href = c.dataset.modulo + '?fecha=' + encodeURIComponent(inputFecha.value);
            });
        });

        cargarAlertas();
        cargarResumen();
        setInterval(cargarAlertas, 60000);
    })();
    </script>

    <script>
    // Accesos rápidos: se pliegan solos tras 15 s sin actividad (efecto humo); sale un tráiler que repite cada 6 s;
    // cualquier movimiento o interacción lo quita y vuelve a desplegar los módulos.
    (function () {
        const INACTIVIDAD_MS = 15 * 1000;       // tiempo sin actividad antes de recoger los módulos
        const ESPERA_CARRITO_MS = 6 * 1000;     // espera entre cada salida del tráiler
        const RECORRIDO_MS = 9000;              // lo que tarda el carrito en cruzar el panel

        const wrap = document.getElementById('quickWrap');
        const grid = document.getElementById('quickGrid');
        const btn = document.getElementById('btnAccesos');
        const cart = document.getElementById('carrito');
        const road = document.getElementById('carretera');
        const cards = Array.from(grid.querySelectorAll('.quick-card'));
        const COLORES_PAQUETE = ['#E0A94A', '#C98A3A', '#D69E2E', '#B7791F'];

        let colapsado = false, animCarrito = null, animRoad = null;
        let tInactividad = null, tCarrito = null, tAnimando = null, tPaquetes = null;
        let ignorarHasta = 0, ultimaAuto = 0;

        cards.forEach(function (c, i) { c.style.setProperty('--i', i); });

        // Vector desde cada tarjeta hasta el botón "Accesos rápidos" (punto de origen del humo)
        function calcularVectores() {
            const bx = btn.offsetLeft + btn.offsetWidth / 2, by = btn.offsetTop + btn.offsetHeight / 2;
            cards.forEach(function (c) {
                const cx = c.offsetLeft + c.offsetWidth / 2, cy = c.offsetTop + c.offsetHeight / 2;
                c.style.setProperty('--dx', (bx - cx) + 'px');
                c.style.setProperty('--dy', (by - cy) + 'px');
            });
        }

        function animar() {
            grid.classList.add('animando');
            clearTimeout(tAnimando);
            tAnimando = setTimeout(function () { grid.classList.remove('animando'); }, 3200);
        }

        function colapsar() {
            if (colapsado) return;
            calcularVectores();
            animar();
            ignorarHasta = Date.now() + 800;     // evita que el roce del puntero deshaga el recogido al instante
            grid.classList.add('collapsed');
            colapsado = true;
            btn.setAttribute('aria-expanded', 'false');
            clearTimeout(tInactividad);
            programarCarrito(400);               // el carrito sale mientras los módulos se recogen
        }

        function expandir() {
            cancelarCarrito(false);
            clearTimeout(tCarrito);
            calcularVectores();
            animar();
            grid.classList.remove('collapsed');
            colapsado = false;
            btn.setAttribute('aria-expanded', 'true');
            reiniciarInactividad();
        }

        function reiniciarInactividad() {
            clearTimeout(tInactividad);
            if (!colapsado) tInactividad = setTimeout(colapsar, INACTIVIDAD_MS);
        }

        function programarCarrito(ms) {
            clearTimeout(tCarrito);
            if (colapsado) tCarrito = setTimeout(lanzarCarrito, ms);
        }

        // Suelta un paquete que cae del carrito, rebota en la carretera y se desvanece
        function soltarPaquete() {
            if (!animCarrito) return;
            const op = Math.min(1, (parseFloat(getComputedStyle(cart).opacity) || 0) * 1.5);
            if (op < 0.08) return;
            const rc = cart.getBoundingClientRect(), rw = wrap.getBoundingClientRect(), rr = road.getBoundingClientRect();
            const x = rc.left - rw.left + rc.width * 0.14;     // los paquetes caen por la parte trasera del tráiler
            const y = rc.top - rw.top + rc.height * 0.15;
            const suelo = rr.top - rw.top - 6;
            const atras = 30 + Math.random() * 40;
            const p = document.createElement('i');
            p.className = 'fa-solid fa-box paquete';
            p.style.color = COLORES_PAQUETE[Math.floor(Math.random() * COLORES_PAQUETE.length)];
            p.style.fontSize = (13 + Math.random() * 8) + 'px';
            wrap.appendChild(p);
            const giro = (Math.random() * 40 - 20);
            const anim = p.animate([
                { transform: 'translate(' + x + 'px,' + y + 'px) rotate(0deg)', opacity: 0, offset: 0 },
                { transform: 'translate(' + (x - 8) + 'px,' + (y - 24) + 'px) rotate(' + (giro / 2) + 'deg)', opacity: op, offset: 0.18 },
                { transform: 'translate(' + (x - atras) + 'px,' + suelo + 'px) rotate(' + giro + 'deg)', opacity: op, offset: 0.5 },
                { transform: 'translate(' + (x - atras - 8) + 'px,' + (suelo - 12) + 'px) rotate(' + (giro * -0.5) + 'deg)', opacity: op, offset: 0.64 },
                { transform: 'translate(' + (x - atras - 14) + 'px,' + suelo + 'px) rotate(' + (giro * 0.4) + 'deg)', opacity: op, offset: 0.78 },
                { transform: 'translate(' + (x - atras - 14) + 'px,' + suelo + 'px) rotate(' + (giro * 0.4) + 'deg)', opacity: 0, offset: 1 }
            ], { duration: 2400, easing: 'ease-out' });
            anim.onfinish = function () { p.remove(); };
        }

        function lanzarCarrito() {
            if (!colapsado || animCarrito) return;
            const w = cart.offsetWidth, h = cart.offsetHeight;
            const x0 = btn.offsetLeft + btn.offsetWidth / 2 - w / 2;
            const y0 = btn.offsetTop + btn.offsetHeight / 2 - h / 2;
            const yFila = grid.offsetTop + grid.offsetHeight / 2 - h / 2;
            const xFin = wrap.clientWidth - w;

            // La carretera queda a la altura de las ruedas del carrito
            road.style.top = (yFila + h * 0.86 - 8) + 'px';
            animRoad = road.animate([
                { opacity: 0, offset: 0 },
                { opacity: 0.95, offset: 0.08 },
                { opacity: 0.95, offset: 0.8 },
                { opacity: 0, offset: 1 }
            ], { duration: RECORRIDO_MS, easing: 'linear', fill: 'forwards' });

            cart.classList.add('activo');
            animCarrito = cart.animate([
                { transform: 'translate(' + x0 + 'px,' + y0 + 'px) scale(0.5)', opacity: 0, offset: 0 },
                { transform: 'translate(' + (x0 + 70) + 'px,' + yFila + 'px) scale(1)', opacity: 0.6, offset: 0.07 },
                { transform: 'translate(' + xFin + 'px,' + yFila + 'px) scale(1)', opacity: 0, offset: 1 }
            ], { duration: RECORRIDO_MS, easing: 'linear', fill: 'forwards' });

            clearInterval(tPaquetes);
            tPaquetes = setInterval(soltarPaquete, 700);

            const propia = animCarrito;
            propia.onfinish = function () {
                if (animCarrito !== propia) return;
                animCarrito = null;
                clearInterval(tPaquetes);
                if (animRoad) { animRoad.cancel(); animRoad = null; }
                cart.classList.remove('activo');
                programarCarrito(ESPERA_CARRITO_MS);    // vuelve a salir a los 6 segundos
            };
        }

        // Quita el carrito (hubo interacción): se desvanece junto con su carretera
        function cancelarCarrito(inmediato) {
            if (!animCarrito) return;
            const a = animCarrito;
            animCarrito = null;
            clearInterval(tPaquetes);
            const cs = getComputedStyle(cart);
            const op = parseFloat(cs.opacity) || 0, tf = cs.transform;
            const opRoad = parseFloat(getComputedStyle(road).opacity) || 0;
            a.onfinish = null;
            a.cancel();
            if (animRoad) { animRoad.cancel(); animRoad = null; }
            cart.classList.remove('activo');
            if (!inmediato) {
                cart.animate([{ transform: tf, opacity: op }, { transform: tf, opacity: 0 }], { duration: 250 });
                road.animate([{ opacity: opRoad }, { opacity: 0 }], { duration: 250 });
            }
        }

        btn.addEventListener('click', function () {
            if (Date.now() - ultimaAuto < 700) return;   // el movimiento del puntero ya los desplegó
            colapsado ? expandir() : colapsar();
        });
        cart.addEventListener('click', expandir);

        // Cualquier movimiento o interacción: el carrito desaparece (haya llegado al final o no) y los módulos salen
        function actividad() {
            if (!colapsado) { reiniciarInactividad(); return; }
            if (Date.now() < ignorarHasta) return;
            ultimaAuto = Date.now();
            expandir();
        }
        ['mousemove', 'mousedown', 'keydown', 'touchstart', 'wheel'].forEach(function (ev) {
            document.addEventListener(ev, actividad, { passive: true });
        });

        window.addEventListener('resize', calcularVectores);
        calcularVectores();
        reiniciarInactividad();
    })();
    </script>

    <script src="../assets/js/acceso.js"></script>
</body>
</html>