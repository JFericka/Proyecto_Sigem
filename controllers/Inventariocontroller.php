<?php

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/InventarioModel.php';

class InventarioController
{
    private $modelo;

    public function __construct($conexion)
    {
        $this->modelo = new InventarioModel($conexion);
    }

    public function listar()
    {
        return $this->modelo->listarProductos();
    }
}