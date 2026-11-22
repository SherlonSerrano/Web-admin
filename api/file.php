<?php

$host = "localhost";
$bdname = "bdproductos";
$usuario = "root";
$password = "";

try {
    //Establecemos la conexión con la base de datos
    $conexion = new PDO("mysql:host=$host;dbname=$bdname", $usuario, $password);

    //echo "Conexión exitosa <br>";
    error_log('Conexión exitosa');

    $sql = "SELECT * FROM `productos` WHERE 1";
    $rs = $conexion->query($sql);
    $rows = $rs->fetchAll(PDO::FETCH_ASSOC); //fetchAll solo se usa con SELECT * FROM
    echo json_encode($rows);
} catch (PDOException $error) {
    //Capturamos el error y mostramos un mensaje
    echo $error->getMessage();
}
