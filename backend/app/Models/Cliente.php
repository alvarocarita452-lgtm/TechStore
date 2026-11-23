<?php

namespace App\Models;

use PDO;

class Cliente
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    // LISTAR CLIENTES
    public function listar(): array
    {
        $sql = "SELECT IdCliente, Nombres, Apellidos, Correo, Telefono
                FROM Cliente
                ORDER BY IdCliente DESC";

        $consulta = $this->conexion->query($sql);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // BUSCAR CLIENTE POR ID
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT IdCliente, Nombres, Apellidos, Correo, Telefono
                FROM Cliente
                WHERE IdCliente = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    // REGISTRAR CLIENTE
    public function crear(
        string $nombres,
        string $apellidos,
        string $correo,
        ?string $telefono
    ): int {
        $sql = "INSERT INTO Cliente
                (Nombres, Apellidos, Correo, Telefono)
                OUTPUT INSERTED.IdCliente
                VALUES (:nombres, :apellidos, :correo, :telefono)";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'telefono' => $telefono
        ]);

        return (int) $consulta->fetchColumn();
    }

    // ACTUALIZAR CLIENTE
    public function actualizar(
        int $id,
        string $nombres,
        string $apellidos,
        string $correo,
        ?string $telefono
    ): bool {
        $sql = "UPDATE Cliente
                SET Nombres = :nombres,
                    Apellidos = :apellidos,
                    Correo = :correo,
                    Telefono = :telefono
                WHERE IdCliente = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'id' => $id,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'telefono' => $telefono
        ]);

        return $consulta->rowCount() > 0;
    }

    // ELIMINAR CLIENTE
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM Cliente
                WHERE IdCliente = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        return $consulta->rowCount() > 0;
    }
}
