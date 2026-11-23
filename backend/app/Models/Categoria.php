<?php

namespace App\Models;

use PDO;

class Categoria
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    // LISTAR CATEGORIAS
    public function listar(): array
    {
        $sql = "SELECT IdCategoria, Nombre, Descripcion
                FROM Categoria
                ORDER BY IdCategoria DESC";

        $consulta = $this->conexion->query($sql);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // BUSCAR CATEGORIA POR ID
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT IdCategoria, Nombre, Descripcion
                FROM Categoria
                WHERE IdCategoria = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    // REGISTRAR CATEGORIA
    public function crear(string $nombre, ?string $descripcion): int
    {
        $sql = "INSERT INTO Categoria (Nombre, Descripcion)
                OUTPUT INSERTED.IdCategoria
                VALUES (:nombre, :descripcion)";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);

        return (int) $consulta->fetchColumn();
    }

    // ACTUALIZAR CATEGORIA
    public function actualizar(
        int $id,
        string $nombre,
        ?string $descripcion
    ): bool {
        $sql = "UPDATE Categoria
                SET Nombre = :nombre,
                    Descripcion = :descripcion
                WHERE IdCategoria = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);

        return $consulta->rowCount() > 0;
    }

    // ELIMINAR CATEGORIA
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM Categoria
                WHERE IdCategoria = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        return $consulta->rowCount() > 0;
    }
}
