<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

class Database
{
    private $servidor;
    private $baseDatos;

    public function __construct()
    {
        // Cargar variables del archivo .env
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        $this->servidor = $_ENV['DB_SERVER'];
        $this->baseDatos = $_ENV['DB_DATABASE'];
    }

    public function conectar()
    {
        try {

            $conexion = new PDO(
                "sqlsrv:Server={$this->servidor};Database={$this->baseDatos};TrustServerCertificate=true",
                null,
                null
            );

            $conexion->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return $conexion;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            throw new RuntimeException(
                "No se pudo conectar con la base de datos.",
                0,
                $e
            );
        }
    }
}
