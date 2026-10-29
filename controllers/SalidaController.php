<?php
// controllers/SalidaController.php

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/SalidaModel.php';
require_once __DIR__ . '/../models/AlertaModel.php';

class SalidaController
{
    private $modelo;
    private $alertas;

    public function __construct($conexion)
    {
        $this->modelo  = new SalidaModel($conexion);
        $this->alertas = new AlertaModel($conexion);
    }

    public function listar(?string $fecha = null): array
    {
        $this->alertas->sincronizarPrestamosVencidos();
        return $this->modelo->listar($fecha);
    }

    public function obtener(int $id): ?array
    {
        return $this->modelo->obtener($id);
    }

    public function catalogo(): array
    {
        return $this->modelo->catalogo();
    }

    public function destinos(): array
    {
        return $this->modelo->destinos();
    }

    public function eliminarDestino(int $id): bool
    {
        return $id > 0 && $this->modelo->eliminarDestino($id);
    }

    // Devuelve ['ok' => bool, 'id' => ?int, 'error' => ?string]
    public function registrar(array $post, int $idUsuario, string $nombreUsuario): array
    {
        if ($idUsuario <= 0) {
            return $this->fallo('Sesión no válida. Inicie sesión nuevamente.');
        }

        $tipo = (string) ($post['tipo'] ?? '');
        if (!in_array($tipo, ['consumo', 'prestamo'], true)) {
            return $this->fallo('Tipo de salida no válido.');
        }

        $obs = trim((string) ($post['observaciones'] ?? ''));
        $cab = [
            'numero_documento'          => trim((string) ($post['numero_documento'] ?? '')),
            'tipo'                      => $tipo,
            'motivo_salida'             => trim((string) ($post['motivo_salida'] ?? '')),
            'id_destino'                => 0,
            'destino_nuevo'             => '',
            'guardar_destino'           => false,
            'cedula_quien_recibe'       => '',
            'celular_quien_recibe'      => '',
            'nombre_quien_entrega'      => $nombreUsuario,
            'nombre_quien_recibe'       => trim((string) ($post['nombre_quien_recibe'] ?? '')),
            'id_usuario'                => $idUsuario,
            'fecha_prevista_devolucion' => null,
            'observaciones'             => ($obs === '') ? null : $obs,
        ];

        if ($cab['numero_documento'] === '' || mb_strlen($cab['numero_documento']) > 50) {
            return $this->fallo('Ingrese el número de documento (máximo 50 caracteres).');
        }
        if ($cab['motivo_salida'] === '' || mb_strlen($cab['motivo_salida']) > 255) {
            return $this->fallo('Ingrese el motivo de la salida (máximo 255 caracteres).');
        }
        // Destino: uno de la lista o un lugar nuevo escrito a mano (que se puede guardar o no)
        $sel = (string) ($post['id_destino'] ?? '');
        if ($sel === 'nuevo') {
            $nuevo = trim((string) ($post['destino_nuevo'] ?? ''));
            if ($nuevo === '' || mb_strlen($nuevo) > 150) {
                return $this->fallo('Escriba el nombre del lugar nuevo (máximo 150 caracteres).');
            }
            $cab['destino_nuevo']   = $nuevo;
            $cab['guardar_destino'] = (($post['guardar_destino'] ?? 'no') === 'si');
        } else {
            $cab['id_destino'] = (int) $sel;
            if ($cab['id_destino'] <= 0) {
                return $this->fallo('Seleccione el destino de la salida.');
            }
        }
        if ($cab['nombre_quien_recibe'] === '' || mb_strlen($cab['nombre_quien_recibe']) > 150) {
            return $this->fallo('Ingrese el nombre de quien recibe (máximo 150 caracteres).');
        }

        // Datos de contacto de quien solicita
        $ced = strtoupper(preg_replace('/\s+/', '', trim((string) ($post['cedula_quien_recibe'] ?? ''))));
        if (!preg_match('/^[0-9A-Z-]{6,20}$/', $ced)) {
            return $this->fallo('Ingrese la cédula de quien solicita (6 a 20 caracteres: números, letras y guiones).');
        }
        $cel = preg_replace('/\D/', '', (string) ($post['celular_quien_recibe'] ?? ''));
        if (strlen($cel) === 11 && substr($cel, 0, 3) === '505') {
            $cel = substr($cel, 3);
        }
        if (!preg_match('/^\d{8}$/', $cel)) {
            return $this->fallo('El número de celular debe tener 8 dígitos.');
        }
        $cab['cedula_quien_recibe']  = $ced;
        $cab['celular_quien_recibe'] = $cel;

        if ($tipo === 'prestamo') {
            $f = (string) ($post['fecha_prevista_devolucion'] ?? '');
            $d = DateTime::createFromFormat('Y-m-d', $f);
            if (!$d || $d->format('Y-m-d') !== $f) {
                return $this->fallo('Indique la fecha prevista de devolución del préstamo.');
            }
            if ($f < date('Y-m-d')) {
                return $this->fallo('La fecha prevista de devolución no puede estar en el pasado.');
            }
            $cab['fecha_prevista_devolucion'] = $f;
        }

        $codigos    = array_values((array) ($post['productos'] ?? []));
        $cantidades = array_values((array) ($post['cantidades'] ?? []));
        if (count($codigos) === 0) {
            return $this->fallo('Agregue al menos un producto al despacho.');
        }

        $lineas = [];
        $vistos = [];
        foreach ($codigos as $i => $codigo) {
            $n = $i + 1;
            $codigo   = trim((string) $codigo);
            $cantidad = filter_var($cantidades[$i] ?? null, FILTER_VALIDATE_INT);
            if ($codigo === '') {
                return $this->fallo("Seleccione el producto de la línea {$n}.");
            }
            if ($cantidad === false || $cantidad < 1) {
                return $this->fallo("La cantidad de la línea {$n} debe ser un número entero mayor a 0.");
            }
            if (in_array($codigo, $vistos, true)) {
                return $this->fallo("El producto {$codigo} está repetido en el despacho. Sume las cantidades en una sola línea.");
            }
            $vistos[] = $codigo;
            $lineas[] = ['codigo' => $codigo, 'cantidad' => $cantidad];
        }

        try {
            $id = $this->modelo->registrar($cab, $lineas);
            return ['ok' => true, 'id' => $id, 'error' => null];
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) {
                return $this->fallo('Ya existe una salida con ese número de documento.');
            }
            error_log($e->getMessage());
            return $this->fallo('No se pudo guardar la salida. Intente nuevamente.');
        } catch (RuntimeException $e) {
            return $this->fallo($e->getMessage());
        }
    }

    private function fallo(string $mensaje): array
    {
        return ['ok' => false, 'id' => null, 'error' => $mensaje];
    }
}
