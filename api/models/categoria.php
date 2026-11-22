<?php

require_once __DIR__ . '/../config/conexion.php';

class Categoria
{
    private $conexion;

    public function __construct()
    {
        $database = new Conexion();
        $this->conexion = $database->conectar();
    }

    public function obtenerCategorias()
    {
        $sql = "SELECT id, title
                FROM categorias
                ORDER BY id";

        $rs = $this->conexion->query($sql);

        return $rs->fetchAll(PDO::FETCH_ASSOC);
    }
}
