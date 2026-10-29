<?php
// models/ReporteModel.php
require_once __DIR__ . '/BaseModel.php';

class ReporteModel extends BaseModel
{
    // Estado actual del inventario (no depende de fechas)
    public function inventario(): array
    {
        return $this->filas(
            "SELECT p.codigo, p.nombre, c.descripcion AS categoria, p.tipo, p.unidad,
                    p.stock_actual, p.stock_minimo, p.fecha_vencimiento, p.activo
             FROM productos p
             INNER JOIN categorias c ON c.codigo_categoria = p.codigo_categoria
             ORDER BY p.codigo"
        );
    }

    // Una fila por producto recibido, dentro del rango de fechas (inclusive)
    public function entradas(string $desde, string $hasta, string $subtipo = 'todos'): array
    {
        $filas = $this->filas(
            "SELECT e.fecha, e.numero_voucher, e.tipo_entrada, e.nombre_quien_entrega,
                    u.nombre_usuario AS recibe, e.observaciones,
                    p.codigo, p.nombre, p.unidad, d.cantidad, d.stock_anterior, d.stock_posterior
             FROM entradas e
             INNER JOIN usuarios u ON u.id_usuario = e.id_usuario
             INNER JOIN detalle_entradas d ON d.id_entrada = e.id_entrada
             INNER JOIN productos p ON p.id_producto = d.id_producto
             WHERE e.fecha >= ? AND e.fecha < DATE_ADD(?, INTERVAL 1 DAY)
               AND (? = 'todos' OR e.tipo_entrada = ?)
             ORDER BY e.fecha DESC, e.id_entrada DESC, d.id_detalle",
            'ssss',
            [$desde, $hasta, $subtipo, $subtipo]
        );
        foreach ($filas as &$f) {
            // El motivo se guarda al inicio de "observaciones"
            $motivo = '';
            $txt = (string) $f['observaciones'];
            if (strpos($txt, 'Motivo: ') === 0) {
                $motivo = explode("\n[OBSERVACIONES]\n", substr($txt, 8), 2)[0];
            }
            $f['motivo'] = $motivo;
        }
        unset($f);
        return $filas;
    }

    // Una fila por producto despachado, dentro del rango de fechas (inclusive)
    public function salidas(string $desde, string $hasta, string $subtipo = 'todos'): array
    {
        return $this->filas(
            "SELECT s.fecha_salida, s.numero_documento, s.tipo, s.estado, s.fecha_prevista_devolucion,
                    s.nombre_quien_recibe, s.cedula_quien_recibe, s.celular_quien_recibe,
                    s.motivo_salida, dst.nombre AS destino,
                    p.codigo, p.nombre, p.unidad, ds.cantidad, ds.cantidad_devuelta,
                    ds.stock_anterior, ds.stock_posterior
             FROM salidas s
             INNER JOIN destinos dst ON dst.id_destino = s.id_destino
             INNER JOIN detalle_salidas ds ON ds.id_salida = s.id_salida
             INNER JOIN productos p ON p.id_producto = ds.id_producto
             WHERE s.fecha_salida >= ? AND s.fecha_salida < DATE_ADD(?, INTERVAL 1 DAY)
               AND (? = 'todos' OR s.tipo = ?)
             ORDER BY s.fecha_salida DESC, s.id_salida DESC, ds.id_detalle",
            'ssss',
            [$desde, $hasta, $subtipo, $subtipo]
        );
    }
}
