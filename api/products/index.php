<?php

header('Content-type: application/json');
require_once '../models/producto.php';

$producto = new Producto();

$cat = $_GET['cat'] ?? null;
$id = $_GET['id'] ?? null;

if (!is_null($cat)) {
    $data = $producto->obtenerProductosCategoria($cat);
} else if (!is_null($id)) {
    $data = $producto->obtenerProductoId($id);
} else {
    $data = $producto->obtenerProductos();
}

echo json_encode($data); //Transforma a formato json
