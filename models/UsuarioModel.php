<?php
require_once '../config/Conexion.php';

class UsuarioModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Para listar todos los usuarios
    public function obtenerTodos() {
        $query = "SELECT * FROM usuarios ORDER BY id_usuario ASC";
        $resultado = mysqli_query($this->db, $query);
        $usuarios = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }

    // Para buscar el usuario que se va a editar
    public function obtenerPorId($id_usuario) {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM usuarios WHERE id_usuario = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_usuario);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $usuario = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
            return $usuario;
        }
        return null;
    }

    // Para guardar un usuario nuevo
    public function guardar($cedula, $nombre_usuario, $clave_hash, $rol) {
        $stmt = mysqli_prepare($this->db, "INSERT INTO usuarios (cedula, nombre_usuario, password, rol, activo) VALUES (?, ?, ?, ?, 1)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $cedula, $nombre_usuario, $clave_hash, $rol);
            $exito = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $exito;
        }
        return false;
    }

public function actualizarConClave($id_usuario, $cedula, $nombre_usuario, $clave_hash, $rol, $activo) {
    $sql = "UPDATE usuarios 
            SET cedula = ?, 
                nombre_usuario = ?, 
                password = ?, 
                rol = ?, 
                activo = ? 
            WHERE id_usuario = ?";
            
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("sssssi", $cedula, $nombre_usuario, $clave_hash, $rol, $activo, $id_usuario);
    
    return $this->ejecutarActualizacion($stmt);
}

public function actualizarSinClave($id_usuario, $cedula, $nombre_usuario, $rol, $activo) {
    $sql = "UPDATE usuarios 
            SET cedula = ?, 
                nombre_usuario = ?, 
                rol = ?, 
                activo = ? 
            WHERE id_usuario = ?";
            
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("ssssi", $cedula, $nombre_usuario, $rol, $activo, $id_usuario);

    return $this->ejecutarActualizacion($stmt);
}

    private function ejecutarActualizacion($stmt) {
        if (mysqli_stmt_execute($stmt)) {
            $filas = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $filas;
        }
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return $error;
    }

    // Para eliminar
    public function eliminar($id_usuario) {
        $stmt = mysqli_prepare($this->db, "DELETE FROM usuarios WHERE id_usuario = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_usuario);
            $exito = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $exito;
        }
        return false;
    }
}