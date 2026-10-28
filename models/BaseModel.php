<?php
// models/BaseModel.php

abstract class BaseModel
{
    protected $db;

    public function __construct($conexion)
    {
        $this->db = $conexion;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_set_charset($this->db, 'utf8mb4');
    }

    protected function ejecutar(string $sql, string $tipos = '', array $params = []): mysqli_stmt
    {
        $stmt = $this->db->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    protected function filas(string $sql, string $tipos = '', array $params = []): array
    {
        $stmt = $this->ejecutar($sql, $tipos, $params);
        $res  = $stmt->get_result();
        $out  = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $out;
    }
}
