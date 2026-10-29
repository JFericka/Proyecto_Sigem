<?php
// models/InventarioModel.php
require_once __DIR__ . '/BaseModel.php';

class InventarioModel extends BaseModel
{
    public function listarProductos(): array
    {
        return $this->filas(
            "SELECT p.id_producto, p.codigo, p.nombre, p.codigo_categoria,
                    c.descripcion AS categoria, p.tipo, p.unidad,
                    p.stock_actual, p.stock_minimo, p.fecha_vencimiento, p.activo
             FROM productos p
             INNER JOIN categorias c ON p.codigo_categoria = c.codigo_categoria
             ORDER BY p.id_producto ASC"
        );
    }

    public function obtenerProducto(int $id): ?array
    {
        $f = $this->filas(
            "SELECT id_producto, codigo, nombre, unidad, stock_actual, activo FROM productos WHERE id_producto = ?",
            'i',
            [$id]
        );
        return $f[0] ?? null;
    }

    // Da de baja un producto: lo inhabilita y deja constancia en la bitácora (bajas_productos)
    public function darDeBaja(int $idProducto, string $motivo, string $observaciones, int $idUsuario): void
    {
        $this->db->begin_transaction();
        try {
            $f = $this->filas(
                "SELECT stock_actual, activo FROM productos WHERE id_producto = ? FOR UPDATE",
                'i',
                [$idProducto]
            );
            if (!$f) {
                throw new RuntimeException('El producto no existe.');
            }
            if ((int) $f[0]['activo'] !== 1) {
                throw new RuntimeException('El producto ya estaba dado de baja.');
            }

            $this->ejecutar(
                "INSERT INTO bajas_productos (id_producto, motivo, observaciones, stock_al_dar_baja, id_usuario)
                 VALUES (?, ?, ?, ?, ?)",
                'issii',
                [$idProducto, $motivo, $observaciones, (int) $f[0]['stock_actual'], $idUsuario]
            )->close();

            $this->ejecutar("UPDATE productos SET activo = 0 WHERE id_producto = ?", 'i', [$idProducto])->close();

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
