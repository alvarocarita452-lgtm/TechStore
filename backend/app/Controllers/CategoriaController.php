<?php

namespace App\Controllers;

use App\Models\Categoria;
use PDO;
use Throwable;

class CategoriaController
{
    private Categoria $modelo;

    public function __construct(PDO $conexion)
    {
        $this->modelo = new Categoria($conexion);
    }

    // LISTAR CATEGORIAS
    public function listar(): void
    {
        try {
            $categorias = $this->modelo->listar();

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al listar categorias'
            ]);
        }
    }

    // OBTENER CATEGORIA POR ID
    public function obtener(int $id): void
    {
        try {
            $categoria = $this->modelo->obtenerPorId($id);

            if (!$categoria) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Categoria no encontrada'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $categoria
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener categoria'
            ]);
        }
    }

    // CREAR CATEGORIA
    public function crear(): void
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            if (
                !is_array($datos) ||
                empty(trim($datos['nombre'] ?? ''))
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'El nombre es obligatorio'
                ]);

                return;
            }

            $id = $this->modelo->crear(
                trim($datos['nombre']),
                isset($datos['descripcion'])
                    ? trim($datos['descripcion'])
                    : null
            );

            http_response_code(201);

            echo json_encode([
                'success' => true,
                'message' => 'Categoria registrada correctamente',
                'id' => $id
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar categoria'
            ]);
        }
    }

    // ACTUALIZAR CATEGORIA
    public function actualizar(int $id): void
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            if (
                !is_array($datos) ||
                empty(trim($datos['nombre'] ?? ''))
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'El nombre es obligatorio'
                ]);

                return;
            }

            $actualizado = $this->modelo->actualizar(
                $id,
                trim($datos['nombre']),
                isset($datos['descripcion'])
                    ? trim($datos['descripcion'])
                    : null
            );

            if (!$actualizado) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Categoria no encontrada o sin cambios'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Categoria actualizada correctamente'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar categoria'
            ]);
        }
    }

    // ELIMINAR CATEGORIA
    public function eliminar(int $id): void
    {
        try {
            $eliminado = $this->modelo->eliminar($id);

            if (!$eliminado) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Categoria no encontrada'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Categoria eliminada correctamente'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar categoria'
            ]);
        }
    }
}
