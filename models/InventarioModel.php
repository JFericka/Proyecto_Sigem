<?php

class InventarioModel
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
    }

    public function listarProductos()
    {
        $sql = "SELECT 
                    p.id_producto,
                    p.codigo,
                    p.nombre,
                    p.codigo_categoria,
                    c.descripcion AS categoria,
                    p.tipo,
                    p.unidad,
                    p.stock_actual,
                    p.activo
                FROM productos p
                INNER JOIN categorias c
                    ON p.codigo_categoria = c.codigo_categoria
                ORDER BY p.id_producto ASC";

        $resultado = mysqli_query($this->db, $sql);

        if (!$resultado) {
            die("Error al consultar productos: " . mysqli_error($this->db));
        }

        $productos = [];

        while ($fila = mysqli_fetch_assoc($resultado)) {
            $productos[] = $fila;
        }

        return $productos;
    }
}