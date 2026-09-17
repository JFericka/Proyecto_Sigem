<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "bodega_municipal";

$conexion = mysqli_connect($host, $user, $password, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>