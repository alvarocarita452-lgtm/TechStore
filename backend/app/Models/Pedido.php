<?php

namespace App\Models;

use PDO;

class Pedido
{
    private PDO $conexion;
    private string $tabla = "Pedido";

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    // LISTAR TODOS LOS PEDIDOS
    public function listar(): array
    {
        $sql = "SELECT
                    P.IdPedido,
                    P.FechaPedido,
                    P.Total,
                    P.Estado,
                    P.IdCliente,
                    P.IdUsuario,
                    C.Nombres + ' ' + C.Apellidos AS Cliente,
                    U.Nombres + ' ' + U.Apellidos AS Usuario
                FROM Pedido P
                INNER JOIN Cliente C
                    ON P.IdCliente = C.IdCliente
                INNER JOIN Usuario U
                    ON P.IdUsuario = U.IdUsuario
                ORDER BY P.IdPedido DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER UN PEDIDO POR ID
    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT
                    P.IdPedido,
                    P.FechaPedido,
                    P.Total,
                    P.Estado,
                    P.IdCliente,
                    P.IdUsuario,
                    C.Nombres + ' ' + C.Apellidos AS Cliente,
                    U.Nombres + ' ' + U.Apellidos AS Usuario
                FROM Pedido P
                INNER JOIN Cliente C
                    ON P.IdCliente = C.IdCliente
                INNER JOIN Usuario U
                    ON P.IdUsuario = U.IdUsuario
                WHERE P.IdPedido = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    // REGISTRAR UN PEDIDO
    public function crear(
        float $total,
        string $estado,
        int $idCliente,
        int $idUsuario
    ): bool {
        $sql = "INSERT INTO Pedido
                    (Total, Estado, IdCliente, IdUsuario)
                VALUES
                    (:total, :estado, :idCliente, :idUsuario)";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':total' => $total,
            ':estado' => $estado,
            ':idCliente' => $idCliente,
            ':idUsuario' => $idUsuario
        ]);
    }

    // ACTUALIZAR UN PEDIDO
    public function actualizar(
        int $id,
        float $total,
        string $estado,
        int $idCliente,
        int $idUsuario
    ): bool {
        $sql = "UPDATE Pedido
                SET Total = :total,
                    Estado = :estado,
                    IdCliente = :idCliente,
                    IdUsuario = :idUsuario
                WHERE IdPedido = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id' => $id,
            ':total' => $total,
            ':estado' => $estado,
            ':idCliente' => $idCliente,
            ':idUsuario' => $idUsuario
        ]);
    }

    // ELIMINAR UN PEDIDO
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM Pedido
                WHERE IdPedido = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);

        return $consulta->execute();
    }
}
