<?php

namespace App\Controllers;

use App\Models\Cliente;
use PDO;
use Throwable;

class ClienteController
{
    private Cliente $modelo;

    public function __construct(PDO $conexion)
    {
        $this->modelo = new Cliente($conexion);
    }

    // LISTAR CLIENTES
    public function listar(): void
    {
        try {
            $clientes = $this->modelo->listar();

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $clientes
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al listar clientes'
            ]);
        }
    }

    // OBTENER CLIENTE POR ID
    public function obtener(int $id): void
    {
        try {
            $cliente = $this->modelo->obtenerPorId($id);

            if (!$cliente) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $cliente
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener cliente'
            ]);
        }
    }

    // CREAR CLIENTE
    public function crear(): void
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            if (
                !is_array($datos) ||
                empty(trim($datos['nombres'] ?? '')) ||
                empty(trim($datos['apellidos'] ?? '')) ||
                empty(trim($datos['correo'] ?? '')) ||
                !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Datos del cliente inválidos'
                ]);

                return;
            }

            $id = $this->modelo->crear(
                trim($datos['nombres']),
                trim($datos['apellidos']),
                trim($datos['correo']),
                isset($datos['telefono'])
                    ? trim($datos['telefono'])
                    : null
            );

            http_response_code(201);

            echo json_encode([
                'success' => true,
                'message' => 'Cliente registrado correctamente',
                'id' => $id
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar cliente'
            ]);
        }
    }

    // ACTUALIZAR CLIENTE
    public function actualizar(int $id): void
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            if (
                !is_array($datos) ||
                empty(trim($datos['nombres'] ?? '')) ||
                empty(trim($datos['apellidos'] ?? '')) ||
                empty(trim($datos['correo'] ?? '')) ||
                !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Datos del cliente inválidos'
                ]);

                return;
            }

            $actualizado = $this->modelo->actualizar(
                $id,
                trim($datos['nombres']),
                trim($datos['apellidos']),
                trim($datos['correo']),
                isset($datos['telefono'])
                    ? trim($datos['telefono'])
                    : null
            );

            if (!$actualizado) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Cliente no encontrado o sin cambios'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Cliente actualizado correctamente'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar cliente'
            ]);
        }
    }

    // ELIMINAR CLIENTE
    public function eliminar(int $id): void
    {
        try {
            $eliminado = $this->modelo->eliminar($id);

            if (!$eliminado) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Cliente eliminado correctamente'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar cliente'
            ]);
        }
    }
}
