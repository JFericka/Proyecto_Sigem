<?php
// models/SalidaModel.php
require_once __DIR__ . '/BaseModel.php';

class SalidaModel extends BaseModel
{
    public function listar(?string $fecha = null): array
    {
        $sql = "SELECT s.id_salida, s.numero_documento, s.tipo, d.nombre AS nombre_destino,
                       s.nombre_quien_recibe, s.cedula_quien_recibe, s.celular_quien_recibe,
                       s.fecha_salida, s.estado, s.fecha_prevista_devolucion
                FROM salidas s
                INNER JOIN destinos d ON d.id_destino = s.id_destino";
        if ($fecha !== null) {
            return $this->filas(
                $sql . " WHERE s.fecha_salida >= ? AND s.fecha_salida < DATE_ADD(?, INTERVAL 1 DAY)
                         ORDER BY s.fecha_salida DESC, s.id_salida DESC",
                'ss',
                [$fecha, $fecha]
            );
        }
        return $this->filas($sql . " ORDER BY s.fecha_salida DESC, s.id_salida DESC");
    }

    // Salida con sus productos, en el formato que usa el vale
    public function obtener(int $id): ?array
    {
        $cab = $this->filas(
            "SELECT s.*, d.nombre AS nombre_destino
             FROM salidas s
             INNER JOIN destinos d ON d.id_destino = s.id_destino
             WHERE s.id_salida = ?",
            'i',
            [$id]
        );
        if (!$cab) {
            return null;
        }
        $salida = $cab[0];
        $salida['productos'] = $this->filas(
            "SELECT p.codigo, p.nombre, p.unidad, ds.cantidad, ds.cantidad_devuelta,
                    ds.stock_anterior, ds.stock_posterior
             FROM detalle_salidas ds
             INNER JOIN productos p ON p.id_producto = ds.id_producto
             WHERE ds.id_salida = ?
             ORDER BY ds.id_detalle",
            'i',
            [$id]
        );
        return $salida;
    }

    // Productos activos con su existencia (el formulario filtra según el tipo de salida)
    public function catalogo(): array
    {
        $hoy = date('Y-m-d');
        $filas = $this->filas(
            "SELECT codigo, nombre, unidad, tipo, stock_actual, fecha_vencimiento
             FROM productos WHERE activo = 1 ORDER BY codigo"
        );
        foreach ($filas as &$f) {
            $f['vencido'] = ($f['fecha_vencimiento'] !== null && $f['fecha_vencimiento'] < $hoy);
        }
        unset($f);
        return $filas;
    }

    public function destinos(): array
    {
        return $this->filas("SELECT id_destino, nombre, responsable FROM destinos WHERE activo = 1 ORDER BY nombre");
    }

    // Quita un lugar de la lista de guardados (los registros históricos conservan su destino)
    public function eliminarDestino(int $id): bool
    {
        $stmt = $this->ejecutar("UPDATE destinos SET activo = 0 WHERE id_destino = ?", 'i', [$id]);
        $ok = $stmt->affected_rows > 0;
        $stmt->close();
        return $ok;
    }

    // Lugar escrito a mano: se reutiliza si ya existe; si no, se crea (visible solo si el usuario decidió guardarlo)
    private function resolverDestinoNuevo(string $nombre, bool $guardar): int
    {
        $f = $this->filas("SELECT id_destino, activo FROM destinos WHERE nombre = ? LIMIT 1", 's', [$nombre]);
        if ($f) {
            if ($guardar && (int) $f[0]['activo'] !== 1) {
                $this->ejecutar("UPDATE destinos SET activo = 1 WHERE id_destino = ?", 'i', [(int) $f[0]['id_destino']])->close();
            }
            return (int) $f[0]['id_destino'];
        }
        $activo = $guardar ? 1 : 0;
        $stmt = $this->ejecutar("INSERT INTO destinos (nombre, tipo, responsable, activo) VALUES (?, 'otro', NULL, ?)", 'si', [$nombre, $activo]);
        $id = (int) $this->db->insert_id;
        $stmt->close();
        return $id;
    }

    // $cab: numero_documento, tipo, motivo_salida, id_destino, nombre_quien_entrega,
    //       nombre_quien_recibe, id_usuario, fecha_prevista_devolucion (?string), observaciones (?string)
    // $lineas: [codigo, cantidad]
    public function registrar(array $cab, array $lineas): int
    {
        $this->db->begin_transaction();
        try {
            if ($this->filas("SELECT 1 FROM salidas WHERE numero_documento = ?", 's', [$cab['numero_documento']])) {
                throw new RuntimeException("El número de documento {$cab['numero_documento']} ya está registrado.");
            }
            if ($cab['id_destino'] > 0) {
                if (!$this->filas("SELECT 1 FROM destinos WHERE id_destino = ? AND activo = 1", 'i', [$cab['id_destino']])) {
                    throw new RuntimeException('El destino seleccionado no es válido.');
                }
                $idDestino = $cab['id_destino'];
            } else {
                $idDestino = $this->resolverDestinoNuevo($cab['destino_nuevo'], $cab['guardar_destino']);
            }

            $stmt = $this->ejecutar(
                "INSERT INTO salidas
                    (numero_documento, tipo, motivo_salida, id_destino, nombre_quien_entrega,
                     nombre_quien_recibe, cedula_quien_recibe, celular_quien_recibe, id_usuario,
                     fecha_prevista_devolucion, observaciones)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                'sssissssiss',
                [
                    $cab['numero_documento'], $cab['tipo'], $cab['motivo_salida'], $idDestino,
                    $cab['nombre_quien_entrega'], $cab['nombre_quien_recibe'],
                    $cab['cedula_quien_recibe'], $cab['celular_quien_recibe'], $cab['id_usuario'],
                    $cab['fecha_prevista_devolucion'], $cab['observaciones'],
                ]
            );
            $idSalida = (int) $this->db->insert_id;
            $stmt->close();

            $tipoEsperado = ($cab['tipo'] === 'prestamo') ? 'herramienta' : 'consumible';
            $hoy = date('Y-m-d');

            foreach ($lineas as $l) {
                $p = $this->filas(
                    "SELECT id_producto, codigo, nombre, tipo, unidad, stock_actual, fecha_vencimiento, activo
                     FROM productos WHERE codigo = ? FOR UPDATE",
                    's',
                    [$l['codigo']]
                );
                if (!$p || (int) $p[0]['activo'] !== 1) {
                    throw new RuntimeException("El producto {$l['codigo']} no existe o está dado de baja.");
                }
                $p = $p[0];

                if ($p['tipo'] !== $tipoEsperado) {
                    $regla = ($p['tipo'] === 'herramienta')
                        ? 'es una herramienta y solo puede salir como préstamo'
                        : 'es un consumible y solo puede salir como consumo';
                    throw new RuntimeException("«{$p['nombre']}» {$regla}.");
                }
                if ($p['fecha_vencimiento'] !== null && $p['fecha_vencimiento'] < $hoy) {
                    throw new RuntimeException("«{$p['nombre']}» está vencido desde el "
                        . date('d/m/Y', strtotime($p['fecha_vencimiento'])) . ' y no puede despacharse.');
                }

                $anterior = (int) $p['stock_actual'];
                if ($l['cantidad'] > $anterior) {
                    throw new RuntimeException(
                        "Stock insuficiente de «{$p['nombre']}»: disponible {$anterior} {$p['unidad']}, solicitado {$l['cantidad']}."
                    );
                }
                $posterior = $anterior - $l['cantidad'];

                $this->ejecutar(
                    "INSERT INTO detalle_salidas (id_salida, id_producto, cantidad, stock_anterior, stock_posterior)
                     VALUES (?, ?, ?, ?, ?)",
                    'iiiii',
                    [$idSalida, (int) $p['id_producto'], $l['cantidad'], $anterior, $posterior]
                )->close();

                $this->ejecutar(
                    "UPDATE productos SET stock_actual = ? WHERE id_producto = ?",
                    'ii',
                    [$posterior, (int) $p['id_producto']]
                )->close();
            }

            $this->db->commit();
            return $idSalida;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
