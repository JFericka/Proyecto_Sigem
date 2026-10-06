<?php
// models/LoginModel.php — UsuarioModel

require_once __DIR__ . '/../config/Conexion.php';

class UsuarioModel
{
    private mysqli $conn;
    private string $table = 'usuarios';

    public function __construct(mysqli $conexion)
    {
        $this->conn = $conexion;
    }

    /** @throws RuntimeException si falla la preparación de la consulta */
    public function getUsuarioPorNombre(string $usuario): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id_usuario, nombre_usuario, password, rol, activo
            FROM {$this->table} WHERE nombre_usuario = ? LIMIT 1"
        );
        if (!$stmt) {
            throw new RuntimeException("Error preparando consulta: {$this->conn->error}");
        }

        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ?: null;
    }

    /** Usado para migrar passwords antiguos en texto plano a hash seguro. */
    public function actualizarPassword(int $idUsuario, string $hash): bool
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password = ? WHERE id_usuario = ?");
        if (!$stmt) {
            error_log("Error preparando actualizarPassword: {$this->conn->error}");
            return false;
        }

        $stmt->bind_param('si', $hash, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}