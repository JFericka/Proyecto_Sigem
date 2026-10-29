<?php
// models/EntradaModel.php

class EntradaModel
{
    private $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_set_charset($this->db, 'utf8mb4');
    }

    /* ---------------------------------------------------------------
     * Consultas
     * ------------------------------------------------------------- */

    public function listar(?string $fecha = null): array
    {
        $sql = "SELECT e.id_entrada, e.numero_voucher, e.tipo_entrada, e.nombre_quien_entrega,
                       u.nombre_usuario AS nombre_quien_recibe, e.fecha
                FROM entradas e
                INNER JOIN usuarios u ON u.id_usuario = e.id_usuario";
        if ($fecha !== null) {
            return $this->filas(
                $sql . " WHERE e.fecha >= ? AND e.fecha < DATE_ADD(?, INTERVAL 1 DAY)
                         ORDER BY e.fecha DESC, e.id_entrada DESC",
                'ss',
                [$fecha, $fecha]
            );
        }
        return $this->filas($sql . " ORDER BY e.fecha DESC, e.id_entrada DESC");
    }

    // Devuelve la entrada con sus productos, en el formato que usa el comprobante
    public function obtener(int $id): ?array
    {
        $cab = $this->filas(
            "SELECT e.*, u.nombre_usuario AS nombre_quien_recibe
             FROM entradas e
             INNER JOIN usuarios u ON u.id_usuario = e.id_usuario
             WHERE e.id_entrada = ?",
            'i',
            [$id]
        );
        if (!$cab) {
            return null;
        }

        $entrada = $cab[0];
        // El motivo se guarda al inicio de "observaciones" (la tabla no tiene columna propia)
        $texto  = (string) ($entrada['observaciones'] ?? '');
        $motivo = '';
        $obs    = $texto;
        if (strpos($texto, 'Motivo: ') === 0) {
            $partes = explode("\n[OBSERVACIONES]\n", substr($texto, 8), 2);
            $motivo = $partes[0];
            $obs    = $partes[1] ?? '';
        }
        $entrada['motivo_entrada'] = $motivo;
        $entrada['observaciones']  = $obs;

        $detalle = $this->filas(
            "SELECT p.codigo, p.nombre, p.unidad, d.cantidad,
                    d.stock_anterior, d.stock_posterior,
                    ds.estado_item, ds.cantidad AS prestado, ds.cantidad_devuelta
             FROM detalle_entradas d
             INNER JOIN productos p ON p.id_producto = d.id_producto
             LEFT JOIN detalle_salidas ds
                    ON ds.id_salida = ? AND ds.id_producto = d.id_producto
             WHERE d.id_entrada = ?
             ORDER BY d.id_detalle",
            'ii',
            [(int) ($entrada['id_salida_origen'] ?? 0), $id]
        );

        $entrada['productos'] = [];
        foreach ($detalle as $d) {
            if ($entrada['tipo_entrada'] === 'devolucion' && $d['estado_item'] !== null) {
                $texto = 'Estado: ' . $d['estado_item']
                       . ' (Devuelto: ' . $d['cantidad_devuelta'] . ' / ' . $d['prestado'] . ')';
            } else {
                $texto = 'Ingreso al inventario';
            }
            $entrada['productos'][] = [
                'codigo'   => $d['codigo'],
                'nombre'   => $d['nombre'],
                'detalle'  => $texto,
                'cantidad' => (int) $d['cantidad'],
                'unidad'   => $d['unidad'],
                'stock_anterior'  => $d['stock_anterior'],
                'stock_posterior' => $d['stock_posterior'],
            ];
        }

        // En una devolución se muestran los datos de quien solicitó el préstamo (solo visualización)
        $entrada['prestamo'] = null;
        if ($entrada['tipo_entrada'] === 'devolucion' && !empty($entrada['id_salida_origen'])) {
            $pr = $this->filas(
                "SELECT numero_documento, nombre_quien_recibe, cedula_quien_recibe, celular_quien_recibe
                 FROM salidas WHERE id_salida = ?",
                'i',
                [(int) $entrada['id_salida_origen']]
            );
            $entrada['prestamo'] = $pr[0] ?? null;
        }

        return $entrada;
    }

    public function catalogo(): array
    {
        return $this->filas("SELECT codigo, nombre, unidad FROM productos WHERE activo = 1 ORDER BY codigo");
    }

    public function categorias(): array
    {
        $out = [];
        foreach ($this->filas("SELECT codigo_categoria, descripcion FROM categorias ORDER BY codigo_categoria") as $f) {
            $out[$f['codigo_categoria']] = $f['descripcion'];
        }
        return $out;
    }

    // Préstamos con productos pendientes de devolver
    // Datos del préstamo que se está devolviendo (responsable, documento y destino)
    public function obtenerPrestamo(int $idSalida): ?array
    {
        $f = $this->filas(
            "SELECT s.numero_documento, s.nombre_quien_recibe AS responsable, d.nombre AS destino
             FROM salidas s
             INNER JOIN destinos d ON d.id_destino = s.id_destino
             WHERE s.id_salida = ? AND s.tipo = 'prestamo'
               AND s.estado IN ('entregado', 'parcial', 'vencido')",
            'i',
            [$idSalida]
        );
        return $f[0] ?? null;
    }

    // Voucher automático para devoluciones: DEV-<documento del préstamo> (con sufijo si hay devoluciones parciales)
    public function generarVoucherDevolucion(string $documento): string
    {
        $base = 'DEV-' . mb_substr($documento, 0, 40);
        $voucher = $base;
        $n = 2;
        while ($this->filas("SELECT 1 FROM entradas WHERE numero_voucher = ?", 's', [$voucher])) {
            $voucher = $base . '-' . $n++;
        }
        return $voucher;
    }

    public function prestamosActivos(): array
    {
        $filas = $this->filas(
            "SELECT s.id_salida, s.numero_documento, d.nombre AS destino,
                    s.nombre_quien_recibe AS responsable,
                    s.cedula_quien_recibe AS cedula, s.celular_quien_recibe AS celular,
                    p.codigo, p.nombre, p.unidad,
                    (ds.cantidad - ds.cantidad_devuelta) AS pendiente
             FROM salidas s
             INNER JOIN destinos d ON d.id_destino = s.id_destino
             INNER JOIN detalle_salidas ds ON ds.id_salida = s.id_salida
             INNER JOIN productos p ON p.id_producto = ds.id_producto
             WHERE s.tipo = 'prestamo'
               AND s.estado IN ('entregado', 'parcial', 'vencido')
               AND ds.cantidad > ds.cantidad_devuelta
             ORDER BY s.fecha_salida DESC, s.id_salida DESC, ds.id_detalle"
        );

        $prestamos = [];
        foreach ($filas as $f) {
            $id = (int) $f['id_salida'];
            if (!isset($prestamos[$id])) {
                $prestamos[$id] = [
                    'id_salida'        => $id,
                    'numero_documento' => $f['numero_documento'],
                    'destino'          => $f['destino'],
                    'responsable'      => $f['responsable'],
                    'cedula'           => $f['cedula'],
                    'celular'          => fmt_celular($f['celular']),
                    'productos'        => [],
                ];
            }
            $prestamos[$id]['productos'][] = [
                'codigo'   => $f['codigo'],
                'nombre'   => $f['nombre'],
                'prestado' => (int) $f['pendiente'],
                'unidad'   => $f['unidad'],
            ];
        }
        return array_values($prestamos);
    }

    /* ---------------------------------------------------------------
     * Registro (con transacción: o se guarda todo o no se guarda nada)
     * ------------------------------------------------------------- */

    // $lineas: [modo => catalogo|nuevo, codigo, cantidad, (nombre, codigo_categoria, tipo, unidad)]
    public function registrarAbastecimiento(array $cab, array $lineas): int
    {
        $this->db->begin_transaction();
        try {
            $this->validarVoucherLibre($cab['numero_voucher']);
            $idEntrada = $this->insertarCabecera($cab, null);

            foreach ($lineas as $l) {
                if ($l['modo'] === 'nuevo') {
                    $existe = $this->filas("SELECT 1 FROM productos WHERE codigo = ?", 's', [$l['codigo']]);
                    if ($existe) {
                        throw new RuntimeException(
                            "El código {$l['codigo']} ya existe en el catálogo. Use «Producto del Catálogo» para reabastecerlo."
                        );
                    }
                    $stmt = $this->ejecutar(
                        "INSERT INTO productos (codigo_categoria, codigo, nombre, tipo, unidad, stock_actual, stock_minimo, fecha_vencimiento, activo)
                         VALUES (?, ?, ?, ?, ?, 0, ?, ?, 1)",
                        'sssssis',
                        [$l['codigo_categoria'], $l['codigo'], $l['nombre'], $l['tipo'], $l['unidad'],
                         $l['stock_minimo'], $l['fecha_vencimiento']]
                    );
                    $idProducto = (int) $this->db->insert_id;
                    $stmt->close();
                } else {
                    $p = $this->filas(
                        "SELECT id_producto FROM productos WHERE codigo = ? AND activo = 1 FOR UPDATE",
                        's',
                        [$l['codigo']]
                    );
                    if (!$p) {
                        throw new RuntimeException("El producto {$l['codigo']} no existe o está inactivo.");
                    }
                    $idProducto = (int) $p[0]['id_producto'];

                    // Si se indicó una nueva fecha de vencimiento, actualiza la del producto
                    if (!empty($l['fecha_vencimiento'])) {
                        $this->ejecutar(
                            "UPDATE productos SET fecha_vencimiento = ? WHERE id_producto = ?",
                            'si',
                            [$l['fecha_vencimiento'], $idProducto]
                        )->close();
                    }
                }

                $this->registrarMovimiento($idEntrada, $idProducto, $l['cantidad'], true);
            }

            $this->db->commit();
            return $idEntrada;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // $lineas: [codigo, cantidad, estado => bueno|dañado|perdido]
    public function registrarDevolucion(array $cab, int $idSalida, array $lineas): int
    {
        $this->db->begin_transaction();
        try {
            $this->validarVoucherLibre($cab['numero_voucher']);

            $salida = $this->filas(
                "SELECT id_salida FROM salidas
                 WHERE id_salida = ? AND tipo = 'prestamo'
                   AND estado IN ('entregado', 'parcial', 'vencido')
                 FOR UPDATE",
                'i',
                [$idSalida]
            );
            if (!$salida) {
                throw new RuntimeException('El préstamo seleccionado no existe o ya fue devuelto por completo.');
            }

            $idEntrada = $this->insertarCabecera($cab, $idSalida);

            foreach ($lineas as $l) {
                $f = $this->filas(
                    "SELECT ds.id_detalle, ds.id_producto, ds.cantidad, ds.cantidad_devuelta
                     FROM detalle_salidas ds
                     INNER JOIN productos p ON p.id_producto = ds.id_producto
                     WHERE ds.id_salida = ? AND p.codigo = ? AND ds.cantidad > ds.cantidad_devuelta
                     ORDER BY ds.id_detalle
                     LIMIT 1
                     FOR UPDATE",
                    'is',
                    [$idSalida, $l['codigo']]
                );
                if (!$f) {
                    throw new RuntimeException("El producto {$l['codigo']} no tiene unidades pendientes en ese préstamo.");
                }

                $pendiente = (int) $f[0]['cantidad'] - (int) $f[0]['cantidad_devuelta'];
                if ($l['cantidad'] > $pendiente) {
                    throw new RuntimeException(
                        "No puede devolver {$l['cantidad']} de {$l['codigo']}: solo quedan {$pendiente} pendientes."
                    );
                }

                $idProducto = (int) $f[0]['id_producto'];

                $this->ejecutar(
                    "UPDATE detalle_salidas
                     SET cantidad_devuelta = cantidad_devuelta + ?, estado_item = ?
                     WHERE id_detalle = ?",
                    'isi',
                    [$l['cantidad'], $l['estado'], (int) $f[0]['id_detalle']]
                )->close();

                // Solo vuelve al stock disponible lo que regresa en buen estado
                $this->registrarMovimiento($idEntrada, $idProducto, $l['cantidad'], $l['estado'] === 'bueno');
            }

            $p = $this->filas(
                "SELECT COUNT(*) AS n FROM detalle_salidas WHERE id_salida = ? AND cantidad_devuelta < cantidad",
                'i',
                [$idSalida]
            );
            $nuevoEstado = ((int) $p[0]['n'] === 0) ? 'devuelto' : 'parcial';
            $this->ejecutar("UPDATE salidas SET estado = ? WHERE id_salida = ?", 'si', [$nuevoEstado, $idSalida])->close();

            $this->db->commit();
            return $idEntrada;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /* ---------------------------------------------------------------
     * Auxiliares
     * ------------------------------------------------------------- */

    private function validarVoucherLibre(string $voucher): void
    {
        $existe = $this->filas("SELECT 1 FROM entradas WHERE numero_voucher = ?", 's', [$voucher]);
        if ($existe) {
            throw new RuntimeException("El número de voucher {$voucher} ya está registrado.");
        }
    }

    private function insertarCabecera(array $cab, ?int $idSalidaOrigen): int
    {
        // Motivo + observaciones en un solo campo (observaciones)
        $texto = 'Motivo: ' . $cab['motivo_entrada'];
        if (!empty($cab['observaciones'])) {
            $texto .= "\n[OBSERVACIONES]\n" . $cab['observaciones'];
        }

        $stmt = $this->ejecutar(
            "INSERT INTO entradas
                (numero_voucher, tipo_entrada, nombre_quien_entrega,
                 id_salida_origen, id_usuario, observaciones)
             VALUES (?, ?, ?, ?, ?, ?)",
            'sssiis',
            [
                $cab['numero_voucher'],
                $cab['tipo_entrada'],
                $cab['nombre_quien_entrega'],
                $idSalidaOrigen,
                $cab['id_usuario'],
                $texto,
            ]
        );
        $id = (int) $this->db->insert_id;
        $stmt->close();
        return $id;
    }

    // Guarda el detalle con el stock antes/después y, si corresponde, suma al inventario
    private function registrarMovimiento(int $idEntrada, int $idProducto, int $cantidad, bool $sumaStock): void
    {
        $f = $this->filas("SELECT stock_actual FROM productos WHERE id_producto = ? FOR UPDATE", 'i', [$idProducto]);
        $anterior  = (int) $f[0]['stock_actual'];
        $posterior = $sumaStock ? $anterior + $cantidad : $anterior;

        $this->ejecutar(
            "INSERT INTO detalle_entradas (id_entrada, id_producto, cantidad, stock_anterior, stock_posterior)
             VALUES (?, ?, ?, ?, ?)",
            'iiiii',
            [$idEntrada, $idProducto, $cantidad, $anterior, $posterior]
        )->close();

        if ($sumaStock) {
            $this->ejecutar(
                "UPDATE productos SET stock_actual = ? WHERE id_producto = ?",
                'ii',
                [$posterior, $idProducto]
            )->close();
        }
    }

    private function ejecutar(string $sql, string $tipos = '', array $params = []): mysqli_stmt
    {
        $stmt = $this->db->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    private function filas(string $sql, string $tipos = '', array $params = []): array
    {
        $stmt = $this->ejecutar($sql, $tipos, $params);
        $res  = $stmt->get_result();
        $out  = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $out;
    }
}