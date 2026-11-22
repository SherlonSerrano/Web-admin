<?php

require_once '../models/producto.php';

class ProductoController
{
    private $producto;

    public function __construct()
    {
        $this->producto = new Producto();
    }

    public function listar()
    {
        try {

            // Obtener los datos enviados mediante POST
            $datos = [
                'precio_min' => $_POST['precio_min'] ?? null,
                'nombre' => $_POST['nombre'] ?? null,
                'precio_max' => $_POST['precio_max'] ?? null,
                'pagina' => $_POST['pagina'] ?? 1,
                'por_pagina' => $_POST['por_pagina'] ?? 10,
            ];

            // Invocar al modelo
            $respuesta = $this->producto->filtrarProductos(
                $datos
            );

            echo json_encode($respuesta);
        } catch (PDOException $e) {

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'error' => 'Error al registrar el producto'
            ]);
        }
    }

    public function registrar()
    {
        //echo json_encode($datos);
        //Validar datos recibidos
        //respuestas y manejo de excepciones
        //invocar al modelo para insertar
        try {
            $datos = [
                'nombre' => $_POST['nombre'] ?? '',
                'precio' => $_POST['precio'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? '',
                'categoria_id' => $_POST['categoria_id'] ?? null,
                'stock' => $_POST['stock'] ?? 0,
                'state' => $_POST['state'] ?? 1
            ];

            //Validaciones
            if (
                $datos['nombre'] == '' ||
                $datos['precio'] == null ||
                !is_numeric($datos['precio']) ||
                $datos['categoria_id'] === null
            ) {
                // echo json_encode (
                //     ['error' => 'Todos los campos son obligatorios'] 
                // );

                throw new Exception("Todos los campos son obligatorios");

                //exit;
            }

            $id = $this->producto->registrarProducto($datos);

            echo json_encode([
                'status' => true,
                'mensaje' => 'Producto registrado correctamente',
                'id' => $id
            ]);
        } catch (PDOException $e) {

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al registrar producto'
            ]);
        } catch (Exception $e) {

            http_response_code(400);

            echo json_encode([
                'status' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    public function actualizar()
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            $id = $datos['id'] ?? null;

            //Validaciones
            if (filter_var($id, FILTER_VALIDATE_INT) == false || $id <= 0) {
                throw new Exception("Id del producto no válido");
            }

            if (!isset($datos['nombre']) || $datos['nombre'] == '') {
                throw new Exception("El campo nombre es obligatorio");
            }

            if (!isset($datos['precio']) || !is_numeric($datos['precio']) || $datos['precio'] < 0) {
                throw new Exception("El campo precio no es válido");
            }

            if (!isset($datos['state']) || !in_array($datos['state'], [0, 1, '0', '1'], true)) {
                throw new Exception("El campo estado no es válido");
            }

            //Agregar validación de existencia

            $rpta = $this->producto->actualizarProducto($datos);

            echo json_encode([
                'status' => true,
                'mensaje' => "Producto actualizado correctamente"
            ]);
        } catch (PDOException $e) {

            error_log($e->getMessage());

            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al actualizar el producto'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    public function eliminar()
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            $id = $datos['id_producto'] ?? null;

            //Validaciones
            if (filter_var($id, FILTER_VALIDATE_INT) == false || $id <= 0) {
                throw new Exception("Id del producto no válido");
            }

            //Otro  ejemplo: validar con count() si existe un producto con ese ID

            $rpta = $this->producto->eliminarProducto($id);

            if ($rpta) {
                echo json_encode([
                    'status' => true,
                    'mensaje' => 'Producto eliminado correctamente'
                ]);
            } else {
                throw new Exception("No se puede eliminar el producto solicitado");
            }
        } catch (PDOException $e) {

            error_log($e->getMessage());

            echo json_encode([
                'status' => false,
                'mensaje' => 'Error al eliminar el producto'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }
}
