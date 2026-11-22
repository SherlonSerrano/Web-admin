<?php
class Conexion
{ //Clase->Propiedades->Funciones(métodos)
    private $host = "localhost";
    private $db = "bdproductos";
    private $user = "root";
    private $password = "";

    public function conectar()
    {
        try {
            $conexion = new PDO(
                "mysql:host=$this->host;dbname=$this->db",
                $this->user,
                $this->password
            );

            //echo "Conexión existosa <br>";

            return $conexion;
        } catch (PDOException $error) {
            die($error->getMessage());
        }
    }
}
