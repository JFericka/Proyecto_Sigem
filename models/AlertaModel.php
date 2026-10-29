<?php
// models/AlertaModel.php
require_once __DIR__ . '/BaseModel.php';

class AlertaModel extends BaseModel
{
    const DIAS_AVISO_VENCIMIENTO = 30;

    // Marca como "vencido" los préstamos cuya fecha prevista ya pasó y siguen abiertos
    public function sincronizarPrestamosVencidos(): void
    {
        $this->ejecutar(
            "UPDATE salidas SET estado = 'vencido'
             WHERE tipo = 'prestamo' AND estado IN ('entregado', 'parcial')
               AND fecha_prevista_devolucion IS NOT NULL AND fecha_prevista_devolucion < ?",
            's',
            [date('Y-m-d')]
        )->close();
    }

    // Alertas activas calculadas en el momento a partir de los datos reales
    public function alertas(): array
    {
        $this->sincronizarPrestamosVencidos();
        $hoy = date('Y-m-d');
        $out = [];

        // 1) Préstamos no devueltos en la fecha prevista
        $prestamos = $this->filas(
            "SELECT s.numero_documento, s.nombre_quien_recibe, s.celular_quien_recibe, s.fecha_prevista_devolucion,
                    d.nombre AS destino,
                    DATEDIFF(?, s.fecha_prevista_devolucion) AS dias,
                    GROUP_CONCAT(CONCAT(p.nombre, ' (', ds.cantidad - ds.cantidad_devuelta, ' ', p.unidad, ')')
                                 ORDER BY ds.id_detalle SEPARATOR ', ') AS pendientes
             FROM salidas s
             INNER JOIN destinos d ON d.id_destino = s.id_destino
             INNER JOIN detalle_salidas ds ON ds.id_salida = s.id_salida
             INNER JOIN productos p ON p.id_producto = ds.id_producto
             WHERE s.tipo = 'prestamo'
               AND s.estado IN ('entregado', 'parcial', 'vencido')
               AND s.fecha_prevista_devolucion < ?
               AND ds.cantidad > ds.cantidad_devuelta
             GROUP BY s.id_salida
             ORDER BY s.fecha_prevista_devolucion ASC",
            'ss',
            [$hoy, $hoy]
        );
        foreach ($prestamos as $f) {
            $dias = (int) $f['dias'];
            $out[] = [
                'tipo'    => 'prestamo',
                'nivel'   => 'critica',
                'titulo'  => $f['nombre_quien_recibe'] . ' no devolvió el préstamo a tiempo',
                'detalle' => "Solicitó: {$f['pendientes']} · Lugar: {$f['destino']}"
                           . ($f['celular_quien_recibe'] !== '' ? ' · Contactarse al ' . fmt_celular($f['celular_quien_recibe']) : ''),
            ];
        }

        // 1b) Préstamos que deben devolverse hoy o mañana (aviso preventivo)
        $manana = date('Y-m-d', strtotime('+1 day'));
        $proximos = $this->filas(
            "SELECT s.numero_documento, s.nombre_quien_recibe, s.celular_quien_recibe, s.fecha_prevista_devolucion,
                    d.nombre AS destino,
                    DATEDIFF(s.fecha_prevista_devolucion, ?) AS dias,
                    GROUP_CONCAT(CONCAT(p.nombre, ' (', ds.cantidad - ds.cantidad_devuelta, ' ', p.unidad, ')')
                                 ORDER BY ds.id_detalle SEPARATOR ', ') AS pendientes
             FROM salidas s
             INNER JOIN destinos d ON d.id_destino = s.id_destino
             INNER JOIN detalle_salidas ds ON ds.id_salida = s.id_salida
             INNER JOIN productos p ON p.id_producto = ds.id_producto
             WHERE s.tipo = 'prestamo'
               AND s.estado IN ('entregado', 'parcial')
               AND s.fecha_prevista_devolucion BETWEEN ? AND ?
               AND ds.cantidad > ds.cantidad_devuelta
             GROUP BY s.id_salida
             ORDER BY s.fecha_prevista_devolucion ASC",
            'sss',
            [$hoy, $hoy, $manana]
        );
        foreach ($proximos as $f) {
            $cuando = ((int) $f['dias'] === 0) ? 'HOY' : 'mañana';
            $out[] = [
                'tipo'    => 'prestamo',
                'nivel'   => 'advertencia',
                'titulo'  => $f['nombre_quien_recibe'] . ' debe devolver el préstamo ' . $cuando,
                'detalle' => "Solicitó: {$f['pendientes']} · Lugar: {$f['destino']}",
            ];
        }

        // 2) Productos vencidos o próximos a vencer (solo los que tienen existencia)
        $venc = $this->filas(
            "SELECT nombre, unidad, stock_actual, fecha_vencimiento,
                    DATEDIFF(fecha_vencimiento, ?) AS dias
             FROM productos
             WHERE activo = 1 AND fecha_vencimiento IS NOT NULL AND stock_actual > 0
               AND fecha_vencimiento <= DATE_ADD(?, INTERVAL " . self::DIAS_AVISO_VENCIMIENTO . " DAY)
             ORDER BY fecha_vencimiento ASC",
            'ss',
            [$hoy, $hoy]
        );
        foreach ($venc as $f) {
            $dias  = (int) $f['dias'];
            $fecha = date('d/m/Y', strtotime($f['fecha_vencimiento']));
            $stock = "Stock: {$f['stock_actual']} {$f['unidad']}";
            if ($dias < 0) {
                $out[] = ['tipo' => 'vencimiento', 'nivel' => 'critica',
                          'titulo' => "Producto vencido: {$f['nombre']}",
                          'detalle' => "Venció el {$fecha} · {$stock}"];
            } else {
                $cuando = $dias === 0 ? 'vence hoy' : "vence en {$dias} " . ($dias === 1 ? 'día' : 'días');
                $out[] = ['tipo' => 'vencimiento', 'nivel' => 'advertencia',
                          'titulo' => "Próximo a vencer: {$f['nombre']}",
                          'detalle' => "Fecha de vencimiento {$fecha} ({$cuando}) · {$stock}"];
            }
        }

        // 3) Stock en o por debajo del mínimo
        $bajos = $this->filas(
            "SELECT nombre, unidad, stock_actual, stock_minimo
             FROM productos
             WHERE activo = 1 AND stock_actual <= stock_minimo
             ORDER BY stock_actual ASC, nombre ASC"
        );
        foreach ($bajos as $f) {
            if ((int) $f['stock_actual'] === 0) {
                $out[] = ['tipo' => 'stock', 'nivel' => 'critica',
                          'titulo' => "Producto agotado: {$f['nombre']}",
                          'detalle' => "No quedan existencias (mínimo configurado: {$f['stock_minimo']} {$f['unidad']}). Solicite reabastecimiento."];
            } else {
                $out[] = ['tipo' => 'stock', 'nivel' => 'advertencia',
                          'titulo' => "Stock mínimo alcanzado: {$f['nombre']}",
                          'detalle' => "Quedan {$f['stock_actual']} {$f['unidad']} (mínimo configurado: {$f['stock_minimo']}). Solicite reabastecimiento."];
            }
        }

        return $out;
    }

    // Conteo de movimientos de un día, por tipo
    public function resumenDia(string $fecha): array
    {
        $res = [
            'entradas' => ['abastecimiento' => 0, 'prestamo' => 0],
            'salidas'  => ['consumo' => 0, 'prestamo' => 0],
        ];

        foreach ($this->filas(
            "SELECT tipo_entrada AS t, COUNT(*) AS n FROM entradas
             WHERE fecha >= ? AND fecha < DATE_ADD(?, INTERVAL 1 DAY) GROUP BY tipo_entrada",
            'ss', [$fecha, $fecha]
        ) as $f) {
            $res['entradas'][$f['t'] === 'devolucion' ? 'prestamo' : 'abastecimiento'] = (int) $f['n'];
        }

        foreach ($this->filas(
            "SELECT tipo AS t, COUNT(*) AS n FROM salidas
             WHERE fecha_salida >= ? AND fecha_salida < DATE_ADD(?, INTERVAL 1 DAY) GROUP BY tipo",
            'ss', [$fecha, $fecha]
        ) as $f) {
            $res['salidas'][$f['t']] = (int) $f['n'];
        }

        return $res;
    }
}
