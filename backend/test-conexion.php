
<?php

require_once __DIR__ . '/config/database.php';

try {

    $database = new Database();
    $conexion = $database->conectar();

    $consulta = $conexion->query("SELECT DB_NAME() AS BaseDatos");

    $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

    echo "CONEXION EXITOSA A SQL SERVER\n";
    echo "Base de datos: " . $resultado['BaseDatos'];

} catch (PDOException $e) {

    echo "ERROR DE CONEXION: " . $e->getMessage();

}
