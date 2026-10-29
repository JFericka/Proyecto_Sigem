<?php
// controllers/EntradaController.php

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/EntradaModel.php';

class EntradaController
{
    private $modelo;

    public function __construct($conexion)
    {
        $this->modelo = new EntradaModel($conexion);
    }

    public function listar(?string $fecha = null)
    {
        return $this->modelo->listar($fecha);
    }

    public function obtener(int $id)
    {
        return $this->modelo->obtener($id);
    }

    public function catalogo()
    {
        return $this->modelo->catalogo();
    }

    public function categorias()
    {
        return $this->modelo->categorias();
    }

    public function prestamosActivos()
    {
        return $this->modelo->prestamosActivos();
    }

    // Valida lo que llega del formulario y lo guarda. Devuelve ['ok' => bool, 'id' => ?int, 'error' => ?string]
    public function registrar(array $post, int $idUsuario): array
    {
        if ($idUsuario <= 0) {
            return $this->fallo('Sesión no válida. Inicie sesión nuevamente.');
        }

        $tipo = (string) ($post['tipo_entrada'] ?? '');
        if (!in_array($tipo, ['abastecimiento', 'devolucion'], true)) {
            return $this->fallo('Tipo de entrada no válido.');
        }

        $obs = trim((string) ($post['observaciones'] ?? ''));
        $cab = [
            'numero_voucher'       => trim((string) ($post['numero_voucher'] ?? '')),
            'tipo_entrada'         => $tipo,
            'nombre_quien_entrega' => trim((string) ($post['nombre_quien_entrega'] ?? '')),
            'motivo_entrada'       => trim((string) ($post['motivo_entrada'] ?? '')),
            'observaciones'        => ($obs === '') ? null : $obs,
            'id_usuario'           => $idUsuario,
        ];

        // En una devolución el responsable, el voucher y el motivo se toman del préstamo seleccionado
        if ($tipo === 'devolucion') {
            $idSalidaSel = (int) ($post['id_salida_origen'] ?? 0);
            if ($idSalidaSel <= 0) {
                return $this->fallo('Seleccione el préstamo que se está devolviendo.');
            }
            $prestamo = $this->modelo->obtenerPrestamo($idSalidaSel);
            if (!$prestamo) {
                return $this->fallo('El préstamo seleccionado no existe o ya fue devuelto por completo.');
            }
            $cab['nombre_quien_entrega'] = $prestamo['responsable'];
            $cab['numero_voucher']       = $this->modelo->generarVoucherDevolucion($prestamo['numero_documento']);
            $cab['motivo_entrada']       = 'Devolución del préstamo ' . $prestamo['numero_documento'] . ' (Destino: ' . $prestamo['destino'] . ')';
        }

        if ($cab['numero_voucher'] === '' || mb_strlen($cab['numero_voucher']) > 50) {
            return $this->fallo('Ingrese un número de voucher (máximo 50 caracteres).');
        }
        if ($cab['nombre_quien_entrega'] === '' || mb_strlen($cab['nombre_quien_entrega']) > 150) {
            return $this->fallo('Ingrese el nombre de quien entrega (máximo 150 caracteres).');
        }
        if ($cab['motivo_entrada'] === '') {
            return $this->fallo('El motivo de la entrada es obligatorio para el comprobante.');
        }

        try {
            if ($tipo === 'abastecimiento') {
                $lineas = $this->lineasAbastecimiento($post);
                if (is_string($lineas)) {
                    return $this->fallo($lineas);
                }
                $id = $this->modelo->registrarAbastecimiento($cab, $lineas);
            } else {
                $idSalida = (int) ($post['id_salida_origen'] ?? 0);
                if ($idSalida <= 0) {
                    return $this->fallo('Seleccione el préstamo que se está devolviendo.');
                }
                $lineas = $this->lineasDevolucion($post);
                if (is_string($lineas)) {
                    return $this->fallo($lineas);
                }
                $id = $this->modelo->registrarDevolucion($cab, $idSalida, $lineas);
            }
            return ['ok' => true, 'id' => $id, 'error' => null];

        // mysqli_sql_exception extiende RuntimeException: debe ir primero
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) {
                return $this->fallo('Ya existe un registro con ese voucher o código de producto.');
            }
            return $this->fallo('No se pudo guardar la entrada: ' . $e->getMessage());
        } catch (RuntimeException $e) {
            return $this->fallo($e->getMessage());
        }
    }

    /* ---------------------------------------------------------------
     * Armado de líneas desde los campos del formulario
     * ------------------------------------------------------------- */

    // Devuelve array de líneas, o un string con el error
    private function lineasAbastecimiento(array $post)
    {
        $modos     = array_values((array) ($post['ab_modo'] ?? []));
        $cantidades = array_values((array) ($post['ab_cantidad'] ?? []));
        $catalogo  = array_values((array) ($post['ab_producto_catalogo'] ?? []));
        $nCodigo   = array_values((array) ($post['ab_codigo'] ?? []));
        $nNombre   = array_values((array) ($post['ab_nombre'] ?? []));
        $nCategoria = array_values((array) ($post['ab_codigo_categoria'] ?? []));
        $nTipo     = array_values((array) ($post['ab_tipo'] ?? []));
        $nUnidad   = array_values((array) ($post['ab_unidad'] ?? []));
        $nMinimo   = array_values((array) ($post['ab_stock_minimo'] ?? []));
        $nVenc     = array_values((array) ($post['ab_vencimiento_nuevo'] ?? []));
        $cVenc     = array_values((array) ($post['ab_vencimiento_cat'] ?? []));

        if (count($modos) === 0) {
            return 'Agregue al menos un producto al detalle.';
        }

        $categorias = $this->modelo->categorias();
        $lineas = [];
        $codigosNuevos = [];
        $ic = 0; // índice entre las filas "catálogo"
        $in = 0; // índice entre las filas "nuevo"

        foreach ($modos as $i => $modo) {
            $n = $i + 1;
            $cantidad = filter_var($cantidades[$i] ?? null, FILTER_VALIDATE_INT);
            if ($cantidad === false || $cantidad < 1) {
                return "La cantidad de la línea {$n} debe ser un número entero mayor a 0.";
            }

            if ($modo === 'catalogo') {
                $codigo = trim((string) ($catalogo[$ic] ?? ''));
                $venc   = trim((string) ($cVenc[$ic] ?? ''));
                $ic++;
                if ($codigo === '') {
                    return "Seleccione el producto del catálogo en la línea {$n}.";
                }
                $errVenc = $this->validarVencimiento($venc, $n);
                if ($errVenc !== null) {
                    return $errVenc;
                }
                $lineas[] = ['modo' => 'catalogo', 'codigo' => $codigo, 'cantidad' => $cantidad,
                             'fecha_vencimiento' => ($venc === '') ? null : $venc];

            } elseif ($modo === 'nuevo') {
                $codigo   = strtoupper(trim((string) ($nCodigo[$in] ?? '')));
                $nombre   = trim((string) ($nNombre[$in] ?? ''));
                $categoria = trim((string) ($nCategoria[$in] ?? ''));
                $tipoProd = trim((string) ($nTipo[$in] ?? ''));
                $unidad   = trim((string) ($nUnidad[$in] ?? ''));
                $minimo   = filter_var($nMinimo[$in] ?? null, FILTER_VALIDATE_INT);
                $venc     = trim((string) ($nVenc[$in] ?? ''));
                $in++;

                if (!preg_match('/^[A-Z0-9._-]{1,50}$/', $codigo)) {
                    return "El código del producto nuevo en la línea {$n} solo puede llevar letras, números, guion y punto.";
                }
                if ($nombre === '' || mb_strlen($nombre) > 150) {
                    return "Ingrese el nombre del producto nuevo en la línea {$n} (máximo 150 caracteres).";
                }
                if (!isset($categorias[$categoria])) {
                    return "Seleccione una categoría válida en la línea {$n}.";
                }
                if (!in_array($tipoProd, ['consumible', 'herramienta'], true)) {
                    return "Seleccione el tipo de producto en la línea {$n}.";
                }
                if ($unidad === '' || mb_strlen($unidad) > 30) {
                    return "Ingrese la unidad del producto nuevo en la línea {$n} (máximo 30 caracteres).";
                }
                if ($minimo === false || $minimo < 0) {
                    return "El stock mínimo de la línea {$n} debe ser un número entero igual o mayor a 0.";
                }
                $errVenc = $this->validarVencimiento($venc, $n);
                if ($errVenc !== null) {
                    return $errVenc;
                }
                if (in_array($codigo, $codigosNuevos, true)) {
                    return "El código {$codigo} está repetido en el formulario.";
                }
                $codigosNuevos[] = $codigo;

                $lineas[] = [
                    'modo' => 'nuevo', 'codigo' => $codigo, 'nombre' => $nombre,
                    'codigo_categoria' => $categoria, 'tipo' => $tipoProd,
                    'unidad' => $unidad, 'cantidad' => $cantidad,
                    'stock_minimo' => $minimo,
                    'fecha_vencimiento' => ($venc === '') ? null : $venc,
                ];
            } else {
                return "Tipo de ingreso no válido en la línea {$n}.";
            }
        }

        return $lineas;
    }

    private function lineasDevolucion(array $post)
    {
        $codigos    = array_values((array) ($post['dev_codigo'] ?? []));
        $cantidades = array_values((array) ($post['dev_cantidad'] ?? []));
        $estados    = array_values((array) ($post['dev_estado_item'] ?? []));

        if (count($codigos) === 0) {
            return 'Seleccione un préstamo y deje al menos un producto a devolver.';
        }

        $lineas = [];
        foreach ($codigos as $i => $codigo) {
            $n = $i + 1;
            $cantidad = filter_var($cantidades[$i] ?? null, FILTER_VALIDATE_INT);
            $estado   = (string) ($estados[$i] ?? '');

            if ($cantidad === false || $cantidad < 1) {
                return "La cantidad de la línea {$n} debe ser un número entero mayor a 0.";
            }
            if (!in_array($estado, ['bueno', 'dañado', 'perdido'], true)) {
                return "Estado del ítem no válido en la línea {$n}.";
            }
            $lineas[] = ['codigo' => trim((string) $codigo), 'cantidad' => $cantidad, 'estado' => $estado];
        }
        return $lineas;
    }

    // Fecha de vencimiento opcional: formato AAAA-MM-DD y no anterior a hoy. Devuelve el error o null.
    private function validarVencimiento(string $fecha, int $n): ?string
    {
        if ($fecha === '') {
            return null;
        }
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$d || $d->format('Y-m-d') !== $fecha) {
            return "La fecha de vencimiento de la línea {$n} no es válida.";
        }
        if ($fecha < date('Y-m-d')) {
            return "La fecha de vencimiento de la línea {$n} no puede estar en el pasado.";
        }
        return null;
    }

    private function fallo(string $mensaje): array
    {
        return ['ok' => false, 'id' => null, 'error' => $mensaje];
    }
}