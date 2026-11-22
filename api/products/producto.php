<?php

//endpoint -- recibir la solicitud del cliente sobre productos
header('Content-type: application/json');

require_once "../controllers/ProductoController.php";

$controlador = new ProductoController();

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {

    case 'POST':
        if (isset($_POST['pagina'])) {
            $controlador->listar();
        } else {
            $controlador->registrar();
        }
        break;
    case 'DELETE':
        $controlador->eliminar();
        break;
    case 'PUT':
        $controlador->actualizar();
        break;
    default:
        echo json_encode([
            'status' => false,
            'mensaje' => "Método $metodo no permitido"
        ]);
        break;
}
