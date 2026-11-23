<?php

namespace App\Models;

use PDO;
use RuntimeException;
use Throwable;

class DetallePedido
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    // =====================================
    // LISTAR TODOS LOS DETALLES
    // =====================================

    public function listar(): array
    {
        $sql = "SELECT
                    D.IdDetalle,
                    D.IdPedido,
                    D.IdProducto,
                    D.Cantidad,
                    D.PrecioUnitario,
                    (D.Cantidad * D.PrecioUnitario) AS Subtotal,
                    P.Nombre AS Producto,
                    PE.FechaPedido,
                    PE.Estado AS EstadoPedido
                FROM DetallePedido D
                INNER JOIN Producto P
                    ON D.IdProducto = P.IdProducto
                INNER JOIN Pedido PE
                    ON D.IdPedido = PE.IdPedido
                ORDER BY D.IdDetalle DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================
    // OBTENER DETALLE POR ID
    // =====================================

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT
                    D.IdDetalle,
                    D.IdPedido,
                    D.IdProducto,
                    D.Cantidad,
                    D.PrecioUnitario,
                    (D.Cantidad * D.PrecioUnitario) AS Subtotal,
                    P.Nombre AS Producto,
                    PE.FechaPedido,
                    PE.Estado AS EstadoPedido
                FROM DetallePedido D
                INNER JOIN Producto P
                    ON D.IdProducto = P.IdProducto
                INNER JOIN Pedido PE
                    ON D.IdPedido = PE.IdPedido
                WHERE D.IdDetalle = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':id' => $id
        ]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    // =====================================
    // DESCONTAR STOCK
    // =====================================

    private function descontarStock(
        int $idProducto,
        int $cantidad
    ): void {

        $sql = "UPDATE Producto
                SET Stock = Stock - :cantidadRestar
                WHERE IdProducto = :idProducto
                  AND Stock >= :cantidadVerificar";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':cantidadRestar' => $cantidad,
            ':idProducto' => $idProducto,
            ':cantidadVerificar' => $cantidad
        ]);

        if ($consulta->rowCount() === 0) {
            throw new RuntimeException(
                "Stock insuficiente o producto inexistente."
            );
        }
    }

    // =====================================
    // DEVOLVER STOCK
    // =====================================

    private function devolverStock(
        int $idProducto,
        int $cantidad
    ): void {

        $sql = "UPDATE Producto
                SET Stock = Stock + :cantidad
                WHERE IdProducto = :idProducto";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':cantidad' => $cantidad,
            ':idProducto' => $idProducto
        ]);

        if ($consulta->rowCount() === 0) {
            throw new RuntimeException(
                "No se pudo devolver el stock del producto."
            );
        }
    }

    // =====================================
    // RECALCULAR TOTAL DEL PEDIDO
    // =====================================

    private function recalcularTotal(int $idPedido): void
    {
        $sql = "UPDATE Pedido
                SET Total = ISNULL(
                    (
                        SELECT SUM(Cantidad * PrecioUnitario)
                        FROM DetallePedido
                        WHERE IdPedido = :idTotal
                    ), 0
                )
                WHERE IdPedido = :idPedido";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':idTotal' => $idPedido,
            ':idPedido' => $idPedido
        ]);
    }

    // =====================================
    // REGISTRAR DETALLE
    // =====================================

    public function crear(
        int $idPedido,
        int $idProducto,
        int $cantidad,
        float $precioUnitario
    ): bool {

        try {

            $this->conexion->beginTransaction();

            // Descontar stock
            $this->descontarStock(
                $idProducto,
                $cantidad
            );

            // Registrar detalle
            $sql = "INSERT INTO DetallePedido
                        (
                            IdPedido,
                            IdProducto,
                            Cantidad,
                            PrecioUnitario
                        )
                    VALUES
                        (
                            :idPedido,
                            :idProducto,
                            :cantidad,
                            :precioUnitario
                        )";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':idPedido' => $idPedido,
                ':idProducto' => $idProducto,
                ':cantidad' => $cantidad,
                ':precioUnitario' => $precioUnitario
            ]);

            // Recalcular total
            $this->recalcularTotal($idPedido);

            $this->conexion->commit();

            return true;

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    // =====================================
    // ACTUALIZAR DETALLE
    // =====================================

    public function actualizar(
        int $id,
        int $idPedido,
        int $idProducto,
        int $cantidad,
        float $precioUnitario
    ): bool {

        try {

            $this->conexion->beginTransaction();

            // Consultar detalle anterior
            $sql = "SELECT
                        IdPedido,
                        IdProducto,
                        Cantidad
                    FROM DetallePedido WITH (UPDLOCK, ROWLOCK)
                    WHERE IdDetalle = :id";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':id' => $id
            ]);

            $anterior = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$anterior) {
                throw new RuntimeException(
                    "Detalle de pedido no encontrado."
                );
            }

            $pedidoAnterior = (int)$anterior['IdPedido'];
            $productoAnterior = (int)$anterior['IdProducto'];
            $cantidadAnterior = (int)$anterior['Cantidad'];

            // Si continúa siendo el mismo producto
            if ($productoAnterior === $idProducto) {

                $diferencia = $cantidad - $cantidadAnterior;

                if ($diferencia > 0) {

                    // Descontar unidades adicionales
                    $this->descontarStock(
                        $idProducto,
                        $diferencia
                    );

                } elseif ($diferencia < 0) {

                    // Devolver unidades sobrantes
                    $this->devolverStock(
                        $productoAnterior,
                        abs($diferencia)
                    );
                }

            } else {

                // Devolver stock del producto anterior
                $this->devolverStock(
                    $productoAnterior,
                    $cantidadAnterior
                );

                // Descontar stock del nuevo producto
                $this->descontarStock(
                    $idProducto,
                    $cantidad
                );
            }

            // Actualizar el detalle
            $sql = "UPDATE DetallePedido
                    SET IdPedido = :idPedido,
                        IdProducto = :idProducto,
                        Cantidad = :cantidad,
                        PrecioUnitario = :precioUnitario
                    WHERE IdDetalle = :id";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':idPedido' => $idPedido,
                ':idProducto' => $idProducto,
                ':cantidad' => $cantidad,
                ':precioUnitario' => $precioUnitario,
                ':id' => $id
            ]);

            // Recalcular pedido anterior
            $this->recalcularTotal($pedidoAnterior);

            // Recalcular pedido nuevo si cambió
            if ($pedidoAnterior !== $idPedido) {
                $this->recalcularTotal($idPedido);
            }

            $this->conexion->commit();

            return true;

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }

    // =====================================
    // ELIMINAR DETALLE
    // =====================================

    public function eliminar(int $id): bool
    {
        try {

            $this->conexion->beginTransaction();

            // Consultar detalle antes de eliminar
            $sql = "SELECT
                        IdPedido,
                        IdProducto,
                        Cantidad
                    FROM DetallePedido WITH (UPDLOCK, ROWLOCK)
                    WHERE IdDetalle = :id";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':id' => $id
            ]);

            $detalle = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$detalle) {
                throw new RuntimeException(
                    "Detalle de pedido no encontrado."
                );
            }

            $idPedido = (int)$detalle['IdPedido'];
            $idProducto = (int)$detalle['IdProducto'];
            $cantidad = (int)$detalle['Cantidad'];

            // Eliminar detalle
            $sql = "DELETE FROM DetallePedido
                    WHERE IdDetalle = :id";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':id' => $id
            ]);

            // Devolver unidades al inventario
            $this->devolverStock(
                $idProducto,
                $cantidad
            );

            // Recalcular total
            $this->recalcularTotal($idPedido);

            $this->conexion->commit();

            return true;

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            throw $e;
        }
    }
}
