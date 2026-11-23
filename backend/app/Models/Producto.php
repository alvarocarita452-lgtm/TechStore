<?php

namespace App\Models;

use PDO;

class Producto
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    // LISTAR TODOS LOS PRODUCTOS (ADMINISTRACIÓN)
    public function listar(): array
    {
        $sql = "SELECT
                    p.IdProducto,
                    p.Nombre,
                    p.Descripcion,
                    p.Precio,
                    p.Stock,
                    p.IdCategoria,
                    p.Estado,
                    c.Nombre AS Categoria
                FROM Producto p
                INNER JOIN Categoria c
                    ON p.IdCategoria = c.IdCategoria
                ORDER BY p.IdProducto DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // LISTAR SOLAMENTE PRODUCTOS ACTIVOS (CATÁLOGO)
    public function listarActivos(): array
    {
        $sql = "SELECT
                    p.IdProducto,
                    p.Nombre,
                    p.Descripcion,
                    p.Precio,
                    p.Stock,
                    p.IdCategoria,
                    p.Estado,
                    c.Nombre AS Categoria
                FROM Producto p
                INNER JOIN Categoria c
                    ON p.IdCategoria = c.IdCategoria
                WHERE p.Estado = 1
                ORDER BY p.IdProducto DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PRODUCTO POR ID
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT
                    IdProducto,
                    Nombre,
                    Descripcion,
                    Precio,
                    Stock,
                    IdCategoria,
                    Estado
                FROM Producto
                WHERE IdProducto = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        $producto = $consulta->fetch(PDO::FETCH_ASSOC);

        return $producto ?: null;
    }

    // REGISTRAR PRODUCTO
    public function crear(
        string $nombre,
        ?string $descripcion,
        float $precio,
        int $stock,
        int $idCategoria
    ): int {
        $sql = "INSERT INTO Producto
                    (Nombre, Descripcion, Precio, Stock, IdCategoria)
                OUTPUT INSERTED.IdProducto
                VALUES
                    (:nombre, :descripcion, :precio, :stock, :idCategoria)";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'idCategoria' => $idCategoria
        ]);

        return (int) $consulta->fetchColumn();
    }

    // ACTUALIZAR PRODUCTO
    public function actualizar(
        int $id,
        string $nombre,
        ?string $descripcion,
        float $precio,
        int $stock,
        int $idCategoria
    ): bool {
        $sql = "UPDATE Producto
                SET Nombre = :nombre,
                    Descripcion = :descripcion,
                    Precio = :precio,
                    Stock = :stock,
                    IdCategoria = :idCategoria
                WHERE IdProducto = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'idCategoria' => $idCategoria
        ]);

        return $consulta->rowCount() > 0;
    }

    // CAMBIAR ESTADO DEL PRODUCTO
    public function cambiarEstado(int $id, int $estado): bool
    {
        $sql = "UPDATE Producto
                SET Estado = :estado
                WHERE IdProducto = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'id' => $id,
            'estado' => $estado
        ]);

        return $consulta->rowCount() > 0;
    }

    // ELIMINAR PRODUCTO
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM Producto
                WHERE IdProducto = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        return $consulta->rowCount() > 0;
    }
}
