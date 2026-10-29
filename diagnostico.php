<?php
// diagnostico.php - Abrir en http://localhost/Proyecto_Sigem/diagnostico.php
// Muestra desde qué carpeta se está ejecutando el sistema y si los archivos nuevos y la base están al día.
header('Content-Type: text/html; charset=utf-8');
$ok = function ($b) { return $b ? '<b style="color:green">SÍ</b>' : '<b style="color:red">NO</b>'; };
echo '<h2>Diagnóstico SIGEM</h2>';
echo '<p>Carpeta desde la que se ejecuta: <code>' . htmlspecialchars(__DIR__) . '</code></p>';
echo '<h3>Archivos de la versión nueva</h3><ul>';
foreach (['config/Auth.php', 'models/AlertaModel.php', 'models/SalidaModel.php', 'models/ReporteModel.php',
          'controllers/ApiController.php', 'controllers/SalidaController.php'] as $f) {
    echo '<li>' . $f . ': ' . $ok(file_exists(__DIR__ . '/' . $f)) . '</li>';
}
$dash = @file_get_contents(__DIR__ . '/views/Dashboard.php');
echo '<li>views/Dashboard.php es la versión nueva: ' . $ok($dash !== false && strpos($dash, 'Auth.php') !== false) . '</li>';
echo '</ul><h3>Base de datos</h3><ul>';
require __DIR__ . '/config/Conexion.php';
foreach ([['productos', 'stock_minimo'], ['productos', 'fecha_vencimiento'], ['detalle_salidas', 'stock_anterior'], ['bajas_productos', 'id_baja']] as $c) {
    $r = @mysqli_query($conexion, "SHOW COLUMNS FROM `{$c[0]}` LIKE '{$c[1]}'");
    echo '<li>' . $c[0] . '.' . $c[1] . ': ' . $ok($r && mysqli_num_rows($r) > 0) . '</li>';
}
echo '</ul><p>Si algo dice NO, ese es el problema. Borre este archivo cuando termine.</p>';
