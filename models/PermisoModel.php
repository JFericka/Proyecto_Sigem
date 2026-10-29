<?php
// models/PermisoModel.php
require_once __DIR__ . '/BaseModel.php';

class PermisoModel extends BaseModel
{
    const MODULOS = ['entradas' => 'Entradas', 'salidas' => 'Salidas'];

    public function __construct($conexion)
    {
        parent::__construct($conexion);
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `permisos_extra` (
                `id_usuario` int(11) NOT NULL,
                `permiso` enum('entradas','salidas') NOT NULL,
                `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id_usuario`,`permiso`),
                CONSTRAINT `permisos_extra_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    // [id_usuario => ['entradas', ...]]
    public function agrupados(): array
    {
        $out = [];
        foreach ($this->filas("SELECT id_usuario, permiso FROM permisos_extra ORDER BY permiso") as $f) {
            $out[(int) $f['id_usuario']][] = $f['permiso'];
        }
        return $out;
    }

    public function de(int $idUsuario): array
    {
        return array_column($this->filas("SELECT permiso FROM permisos_extra WHERE id_usuario = ?", 'i', [$idUsuario]), 'permiso');
    }

    // Reemplaza los permisos extra del usuario por la lista indicada (vacía = quitarlos todos)
    public function guardar(int $idUsuario, array $permisos): void
    {
        $this->db->begin_transaction();
        try {
            $this->ejecutar("DELETE FROM permisos_extra WHERE id_usuario = ?", 'i', [$idUsuario])->close();
            foreach (array_unique($permisos) as $p) {
                if (isset(self::MODULOS[$p])) {
                    $this->ejecutar("INSERT INTO permisos_extra (id_usuario, permiso) VALUES (?, ?)", 'is', [$idUsuario, $p])->close();
                }
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
