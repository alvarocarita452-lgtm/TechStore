<?php

namespace App\Controllers;

use App\Models\Pedido;
use PDO;
use PDOException;

class PedidoController
{
    private Pedido $modelo;

    public function __construct(PDO $conexion)
    {
        $this->modelo = new Pedido($conexion);
    }

    // LISTAR PEDIDOS
    public function listar(): void
    {
        try {
            $pedidos = $this->modelo->listar();

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $pedidos
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al listar los pedidos."
            ]);
        }
    }

    // OBTENER PEDIDO POR ID
    public function obtenerPorId(int $id): void
    {
        try {
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "El ID del pedido no es válido."
                ]);
                return;
            }

            $pedido = $this->modelo->obtenerPorId($id);

            if (!$pedido) {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Pedido no encontrado."
                ]);
                return;
            }

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "data" => $pedido
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al obtener el pedido."
            ]);
        }
    }

    // REGISTRAR PEDIDO
    public function crear(): void
    {
        try {
            $datos = json_decode(file_get_contents("php://input"), true);

            if (!is_array($datos)) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Los datos enviados no son válidos."
                ]);
                return;
            }

            $total = $datos["Total"] ?? null;
            $estado = trim($datos["Estado"] ?? "");
            $idCliente = $datos["IdCliente"] ?? null;
            $idUsuario = $datos["IdUsuario"] ?? null;

            if (
                !is_numeric($total) ||
                !is_finite((float)$total) ||
                (float)$total < 0 ||
                $estado === "" ||
                strlen($estado) > 30 ||
                filter_var($idCliente, FILTER_VALIDATE_INT) === false ||
                (int)$idCliente <= 0 ||
                filter_var($idUsuario, FILTER_VALIDATE_INT) === false ||
                (int)$idUsuario <= 0
            ) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Verifica los datos del pedido."
                ]);
                return;
            }

            $resultado = $this->modelo->crear(
                (float)$total,
                $estado,
                (int)$idCliente,
                (int)$idUsuario
            );

            if ($resultado) {
                http_response_code(201);
                echo json_encode([
                    "success" => true,
                    "mensaje" => "Pedido registrado correctamente."
                ]);
            }

        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "mensaje" => "No se pudo registrar el pedido. Verifica que el cliente y usuario existan."
            ]);
        }
    }

    // ACTUALIZAR PEDIDO
    public function actualizar(int $id): void
    {
        try {
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "El ID del pedido no es válido."
                ]);
                return;
            }

            $pedido = $this->modelo->obtenerPorId($id);

            if (!$pedido) {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Pedido no encontrado."
                ]);
                return;
            }

            $datos = json_decode(file_get_contents("php://input"), true);

            if (!is_array($datos)) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Los datos enviados no son válidos."
                ]);
                return;
            }

            $total = $datos["Total"] ?? null;
            $estado = trim($datos["Estado"] ?? "");
            $idCliente = $datos["IdCliente"] ?? null;
            $idUsuario = $datos["IdUsuario"] ?? null;

            if (
                !is_numeric($total) ||
                !is_finite((float)$total) ||
                (float)$total < 0 ||
                $estado === "" ||
                strlen($estado) > 30 ||
                filter_var($idCliente, FILTER_VALIDATE_INT) === false ||
                (int)$idCliente <= 0 ||
                filter_var($idUsuario, FILTER_VALIDATE_INT) === false ||
                (int)$idUsuario <= 0
            ) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Verifica los datos del pedido."
                ]);
                return;
            }

            $resultado = $this->modelo->actualizar(
                $id,
                (float)$total,
                $estado,
                (int)$idCliente,
                (int)$idUsuario
            );

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "mensaje" => "Pedido actualizado correctamente."
                ]);
            }

        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "mensaje" => "No se pudo actualizar el pedido. Verifica los datos enviados."
            ]);
        }
    }

    // ELIMINAR PEDIDO
    public function eliminar(int $id): void
    {
        try {
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "El ID del pedido no es válido."
                ]);
                return;
            }

            $pedido = $this->modelo->obtenerPorId($id);

            if (!$pedido) {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Pedido no encontrado."
                ]);
                return;
            }

            $resultado = $this->modelo->eliminar($id);

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "mensaje" => "Pedido eliminado correctamente."
                ]);
            }

        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 547) {
                http_response_code(409);
                echo json_encode([
                    "success" => false,
                    "mensaje" => "No se puede eliminar el pedido porque tiene detalles asociados."
                ]);
                return;
            }

            http_response_code(500);
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al eliminar el pedido."
            ]);
        }
    }
}
