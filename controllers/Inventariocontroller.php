<?php
// controllers/Inventariocontroller.php

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/InventarioModel.php';

class InventarioController
{
    private $modelo;

    public function __construct($conexion)
    {
        $this->modelo = new InventarioModel($conexion);
    }

    public function listar()
    {
        return $this->modelo->listarProductos();
    }

    public function obtener(int $id)
    {
        return $this->modelo->obtenerProducto($id);
    }

    // Devuelve ['ok' => bool, 'error' => ?string]
    public function darDeBaja(array $post, int $idUsuario): array
    {
        $id     = (int) ($post['id_producto'] ?? 0);
        $motivo = (string) ($post['motivo_baja'] ?? '');
        $obs    = trim((string) ($post['observaciones_baja'] ?? ''));

        if ($id <= 0) {
            return ['ok' => false, 'error' => 'Producto no válido.'];
        }
        if (!in_array($motivo, ['danado', 'perdido', 'obsoleto'], true)) {
            return ['ok' => false, 'error' => 'Motivo de baja no válido.'];
        }
        if ($obs === '') {
            return ['ok' => false, 'error' => 'Escriba la justificación de la baja.'];
        }

        try {
            $this->modelo->darDeBaja($id, $motivo, $obs, $idUsuario);
            return ['ok' => true, 'error' => null];
        } catch (RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return ['ok' => false, 'error' => 'No se pudo registrar la baja.'];
        }
    }
}
