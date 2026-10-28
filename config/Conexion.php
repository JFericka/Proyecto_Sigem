<?php
// MODO PRUEBAS: true = las contraseñas se guardan y se ven en texto plano en la base de datos.
// Antes de usar el sistema en serio, cámbielo a false (se guardarán cifradas).
if (!defined('SIGEM_PASSWORD_PLANO')) { define('SIGEM_PASSWORD_PLANO', true); }

$host = "localhost";
$user = "root";
$password = "";
$db = "bodega_municipal";

$conexion = mysqli_connect($host, $user, $password, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>