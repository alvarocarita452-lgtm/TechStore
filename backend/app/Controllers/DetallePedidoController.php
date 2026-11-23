<?php

namespace App\Controllers;

use App\Models\DetallePedido;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class DetallePedidoController
{
    private DetallePedido $modelo;

    public function __construct(PDO $conexion)
    {
        $this->modelo = new DetallePedido($conexion);
    }

    // =====================================
    // LISTAR DETALLES
    // =====================================

    public function listar(): void
    {
        try {

            $detalles = $this->modelo->listar();

            http_response_code(200);

            echo json_encode([
                "success" => true,
                "data" => $detalles
            ]);

        } catch (Throwable $e) {

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "mensaje" => "Error al listar los detalles."
            ]);
        }
    }

    // =====================================
    // OBTENER DETALLE POR ID
    // =====================================

    public function obtenerPorId(int $id): void
    {
        try {

            if ($id <= 0) {
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "El ID no es válido."
                ]);

                return;
            }

            $detalle = $this->modelo->obtenerPorId($id);

            if (!$detalle) {
                http_response_code(404);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Detalle de pedido no encontrado."
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                "success" => true,
                "data" => $detalle
            ]);

        } catch (Throwable $e) {

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "mensaje" => "Error al obtener el detalle."
            ]);
        }
    }

    // =====================================
    // REGISTRAR DETALLE
    // =====================================

    public function crear(): void
    {
        try {

            $datos = json_decode(
                file_get_contents("php://input"),
                true
            );

            if (!is_array($datos)) {
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Los datos enviados no son válidos."
                ]);

                return;
            }

            $idPedido = $datos["IdPedido"] ?? null;
            $idProducto = $datos["IdProducto"] ?? null;
            $cantidad = $datos["Cantidad"] ?? null;
            $precioUnitario = $datos["PrecioUnitario"] ?? null;

            if (
                filter_var($idPedido, FILTER_VALIDATE_INT) === false ||
                (int)$idPedido <= 0 ||
                filter_var($idProducto, FILTER_VALIDATE_INT) === false ||
                (int)$idProducto <= 0 ||
                filter_var($cantidad, FILTER_VALIDATE_INT) === false ||
                (int)$cantidad <= 0 ||
                !is_numeric($precioUnitario) ||
                !is_finite((float)$precioUnitario) ||
                (float)$precioUnitario <= 0
            ) {
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Verifica los datos del detalle."
                ]);

                return;
            }

            $this->modelo->crear(
                (int)$idPedido,
                (int)$idProducto,
                (int)$cantidad,
                (float)$precioUnitario
            );

            http_response_code(201);

            echo json_encode([
                "success" => true,
                "mensaje" => "Detalle registrado, stock actualizado y total recalculado."
            ]);

        } catch (RuntimeException $e) {

            http_response_code(409);

            echo json_encode([
                "success" => false,
                "mensaje" => $e->getMessage()
            ]);

        } catch (PDOException $e) {

            error_log($e->getMessage());

            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 547) {
                http_response_code(409);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Verifica que el pedido y el producto existan."
                ]);

                return;
            }

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "mensaje" => "Error al registrar el detalle."
            ]);

        } catch (Throwable $e) {

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "mensaje" => "Error inesperado al registrar el detalle."
            ]);
        }
    }

    // =====================================
    // ACTUALIZAR DETALLE
    // =====================================

    public function actualizar(int $id): void
    {
        try {

            if ($id <= 0) {
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "El ID no es válido."
                ]);

                return;
            }

            $detalle = $this->modelo->obtenerPorId($id);

            if (!$detalle) {
                http_response_code(404);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Detalle no encontrado."
                ]);

                return;
            }

            $datos = json_decode(
                file_get_contents("php://input"),
                true
            );

            if (!is_array($datos)) {
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Los datos enviados no son válidos."
                ]);

                return;
            }

            $idPedido = $datos["IdPedido"] ?? null;
            $idProducto = $datos["IdProducto"] ?? null;
            $cantidad = $datos["Cantidad"] ?? null;
            $precioUnitario = $datos["PrecioUnitario"] ?? null;

            if (
                filter_var($idPedido, FILTER_VALIDATE_INT) === false ||
                (int)$idPedido <= 0 ||
                filter_var($idProducto, FILTER_VALIDATE_INT) === false ||
                (int)$idProducto <= 0 ||
                filter_var($cantidad, FILTER_VALIDATE_INT) === false ||
                (int)$cantidad <= 0 ||
                !is_numeric($precioUnitario) ||
                !is_finite((float)$precioUnitario) ||
                (float)$precioUnitario <= 0
            ) {
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Verifica los datos del detalle."
                ]);

                return;
            }

            $this->modelo->actualizar(
                $id,
                (int)$idPedido,
                (int)$idProducto,
                (int)$cantidad,
                (float)$precioUnitario
            );

            http_response_code(200);

            echo json_encode([
                "success" => true,
                "mensaje" => "Detalle actualizado, stock ajustado y total recalculado."
            ]);

        } catch (RuntimeException $e) {

            http_response_code(409);

            echo json_encode([
                "success" => false,
                "mensaje" => $e->getMessage()
            ]);

        } catch (PDOException $e) {

            error_log($e->getMessage());

            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 547) {
                http_response_code(409);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Verifica que el pedido y el producto existan."
                ]);

                return;
            }

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "mensaje" => "Error al actualizar el detalle."
            ]);

        } catch (Throwable $e) {

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "mensaje" => "Error inesperado al actualizar el detalle."
            ]);
        }
    }

    // =====================================
    // ELIMINAR DETALLE
    // =====================================

    public function eliminar(int $id): void
    {
        try {

            if ($id <= 0) {
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "El ID no es válido."
                ]);

                return;
            }

            $detalle = $this->modelo->obtenerPorId($id);

            if (!$detalle) {
                http_response_code(404);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Detalle no encontrado."
                ]);

                return;
            }

            $this->modelo->eliminar($id);

            http_response_code(200);

            echo json_encode([
                "success" => true,
                "mensaje" => "Detalle eliminado, stock devuelto y total recalculado."
            ]);

        } catch (RuntimeException $e) {

            http_response_code(409);

            echo json_encode([
                "success" => false,
                "mensaje" => $e->getMessage()
            ]);

        } catch (Throwable $e) {

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                "success" => false,
                "mensaje" => "Error al eliminar el detalle."
            ]);
        }
    }
}
