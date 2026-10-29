<?php
// controllers/ApiController.php
// Respuestas JSON para el Dashboard: alertas (campana) y resumen de movimientos por día.

require_once __DIR__ . '/../config/Auth.php';
auth_requerir(null, 'json');
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/AlertaModel.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $modelo = new AlertaModel($conexion);
    $accion = $_GET['action'] ?? '';

    if ($accion === 'alertas') {
        $alertas = $modelo->alertas();
        foreach ($alertas as &$a) {
            if ($a['tipo'] === 'prestamo') {
                $a['enlace'] = auth_puede('entradas') ? 'Entradas.php?action=agregar' : null;
            } else {
                $a['enlace'] = auth_puede('inventario') ? 'Inventario.php' : null;
            }
        }
        unset($a);
        echo json_encode(['alertas' => $alertas]);

    } elseif ($accion === 'resumen') {
        $fecha = (string) ($_GET['fecha'] ?? date('Y-m-d'));
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$d || $d->format('Y-m-d') !== $fecha) {
            http_response_code(400);
            echo json_encode(['error' => 'Fecha no válida.']);
            exit;
        }
        echo json_encode($modelo->resumenDia($fecha));

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida.']);
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo consultar la información.']);
}
