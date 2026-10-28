<?php

require_once __DIR__ . '/../config/Conexion.php';

class UsuarioModel
{
    private mysqli $conn;
    private string $table = "usuarios";

    public function __construct(mysqli $conexion)
    {
        $this->conn = $conexion;
    }

    /**
     * Busca un usuario por su nombre de usuario.
     * @throws RuntimeException si falla la preparación de la consulta.
     */
    public function getUsuarioPorNombre(string $usuario): ?array
    {
        $query = "SELECT id_usuario, nombre_usuario, password, rol, activo
                FROM {$this->table}
                WHERE nombre_usuario = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new RuntimeException("Error preparando getUsuarioPorNombre: " . $this->conn->error);
        }

        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();

        return $fila ?: null;
    }

    /**
     * Actualiza el password de un usuario (usado para migrar hashes antiguos).
     */
    public function actualizarPassword(int $idUsuario, string $nuevoHash): bool
    {
        $query = "UPDATE {$this->table} SET password = ? WHERE id_usuario = ?";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Error preparando actualizarPassword: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("si", $nuevoHash, $idUsuario);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }
}
