<?php
require_once '../config/conexion.php';

class Usuario
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion(); //Variable $db hereda las propiedades de la clase Conexion (que sean públicas)
        $this->conexion = $db->conectar();
    }

    public function loginUsuario($email, $password)
    {
        $sql = 'CALL sp_validar_usuario(:email, :password, @resultado)';

        $rs = $this->conexion->prepare($sql);

        $rs->bindParam(':email', $email, PDO::PARAM_STR);
        $rs->bindParam(':password', $password, PDO::PARAM_STR);

        $rs->execute();

        $select = $this->conexion->query('SELECT @resultado AS resultado;');
        $result = $select->fetch(PDO::FETCH_ASSOC);

        return $result['resultado'] ?? null;
    }

    public function obtenerUsuario($id)
    {
        $sql = 'SELECT id, name, email FROM users WHERE id = :id';

        $rs = $this->conexion->prepare($sql);
        $rs->execute([':id' => $id]);

        return $rs->fetch(PDO::FETCH_ASSOC);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}
