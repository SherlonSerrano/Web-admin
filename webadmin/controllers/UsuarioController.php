<?php
session_start();

require_once '../models/Usuario.php';

class UsuarioController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Usuario();
    }

    public function login()
    {
        try {
            $datos = json_decode(file_get_contents("php://input"), true);
            //Agregar validaciones

            $id = $this->modelo->loginUsuario($datos['email'], $datos['password']);

            if ($id > 0) {

                $usuario = $this->modelo->obtenerUsuario($id);

                $_SESSION['id'] = $id;
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['nombre'] = $usuario['name'];

                echo json_encode([
                    'status' => true,
                    'id' => $id,
                    //'usuario' => $usuario
                ]);
            } else {
                throw new Exception('Error en el inicio de sesión');
            }
        } catch (PDOException $e) {
            echo json_encode([
                'status' => false,
                'mensaje' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    public function check()
    {
        if (isset($_SESSION['id'])) {
            echo json_encode([
                'status' => true,
                'message' => 'Sesión activa',
                'session' => $_SESSION
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'No hay sesión iniciada'
            ]);
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();

        echo json_encode([
            'status' => 'success',
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}
