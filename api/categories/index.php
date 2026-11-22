<?php

header('Content-type: application/json');
require_once '../models/categoria.php';

$modelo = new Categoria();

$data = $modelo->obtenerCategorias();

echo json_encode($data);
