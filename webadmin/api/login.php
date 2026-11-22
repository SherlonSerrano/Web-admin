<?php

header('Content-type: application/json');

require_once "../controllers/UsuarioController.php";

$controlador = new UsuarioController();

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {

    case 'POST':
        $controlador->login();
        break;
    case 'GET':
        $controlador->check();
        break;
    case 'DELETE':
        $controlador->logout();
        break;
    default:
        echo json_encode([
            'status' => false,
            'mensaje' => "Método $metodo no permitido"
        ]);
        break;
}