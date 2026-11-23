<?php

namespace App\Controllers;

use App\Models\Producto;
use PDO;
use PDOException;
use Throwable;

class ProductoController
{
    private Producto $modelo;

    public function __construct(PDO $conexion)
    {
        $this->modelo = new Producto($conexion);
    }

    // LISTAR TODOS LOS PRODUCTOS
    public function listar(): void
    {
        try {
            $productos = $this->modelo->listar();

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $productos
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al listar los productos.'
            ]);
        }
    }

    // LISTAR PRODUCTOS ACTIVOS PARA EL CATÁLOGO
    public function listarActivos(): void
    {
        try {
            $productos = $this->modelo->listarActivos();

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $productos
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar los productos del catálogo.'
            ]);
        }
    }

    // OBTENER PRODUCTO POR ID
    public function obtener(int $id): void
    {
        try {
            $producto = $this->modelo->obtenerPorId($id);

            if (!$producto) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $producto
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener el producto.'
            ]);
        }
    }

    // REGISTRAR PRODUCTO
    public function crear(): void
    {
        try {
            $datos = json_decode(file_get_contents("php://input"), true);

            if (
                !is_array($datos) ||
                empty(trim($datos['nombre'] ?? '')) ||
                !isset($datos['precio'], $datos['stock'], $datos['idCategoria']) ||
                !is_numeric($datos['precio']) ||
                !is_numeric($datos['stock']) ||
                !is_numeric($datos['idCategoria']) ||
                (float) $datos['precio'] < 0 ||
                (int) $datos['stock'] < 0 ||
                (int) $datos['idCategoria'] <= 0
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Verifica los datos del producto.'
                ]);

                return;
            }

            $id = $this->modelo->crear(
                trim($datos['nombre']),
                isset($datos['descripcion']) && trim($datos['descripcion']) !== ''
                    ? trim($datos['descripcion'])
                    : null,
                (float) $datos['precio'],
                (int) $datos['stock'],
                (int) $datos['idCategoria']
            );

            http_response_code(201);

            echo json_encode([
                'success' => true,
                'message' => 'Producto registrado correctamente.',
                'id' => $id
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el producto.'
            ]);
        }
    }

    // ACTUALIZAR PRODUCTO
    public function actualizar(int $id): void
    {
        try {
            $datos = json_decode(file_get_contents("php://input"), true);

            if (
                !is_array($datos) ||
                empty(trim($datos['nombre'] ?? '')) ||
                !isset($datos['precio'], $datos['stock'], $datos['idCategoria']) ||
                !is_numeric($datos['precio']) ||
                !is_numeric($datos['stock']) ||
                !is_numeric($datos['idCategoria']) ||
                (float) $datos['precio'] < 0 ||
                (int) $datos['stock'] < 0 ||
                (int) $datos['idCategoria'] <= 0
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Verifica los datos del producto.'
                ]);

                return;
            }

            $productoExistente = $this->modelo->obtenerPorId($id);

            if (!$productoExistente) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ]);

                return;
            }

            $actualizado = $this->modelo->actualizar(
                $id,
                trim($datos['nombre']),
                isset($datos['descripcion']) && trim($datos['descripcion']) !== ''
                    ? trim($datos['descripcion'])
                    : null,
                (float) $datos['precio'],
                (int) $datos['stock'],
                (int) $datos['idCategoria']
            );

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => $actualizado
                    ? 'Producto actualizado correctamente.'
                    : 'No se realizaron cambios en el producto.'
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el producto.'
            ]);
        }
    }

    // CAMBIAR ESTADO DEL PRODUCTO
    public function cambiarEstado(int $id): void
    {
        try {
            $datos = json_decode(file_get_contents("php://input"), true);

            if (
                !is_array($datos) ||
                !isset($datos['estado']) ||
                !in_array($datos['estado'], [0, 1, '0', '1'], true)
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'El estado debe ser 0 o 1.'
                ]);

                return;
            }

            $estado = (int) $datos['estado'];

            $producto = $this->modelo->obtenerPorId($id);

            if (!$producto) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ]);

                return;
            }

            if ((int) $producto['Estado'] === $estado) {
                http_response_code(200);

                echo json_encode([
                    'success' => true,
                    'message' => 'El producto ya tiene ese estado.'
                ]);

                return;
            }

            $actualizado = $this->modelo->cambiarEstado($id, $estado);

            if (!$actualizado) {
                throw new \RuntimeException('No se pudo actualizar el estado.');
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => $estado === 1
                    ? 'Producto activado correctamente.'
                    : 'Producto desactivado correctamente.'
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al cambiar el estado del producto.'
            ]);
        }
    }

    // ELIMINAR PRODUCTO
    public function eliminar(int $id): void
    {
        try {
            $producto = $this->modelo->obtenerPorId($id);

            if (!$producto) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Producto no encontrado.'
                ]);

                return;
            }

            $eliminado = $this->modelo->eliminar($id);

            if (!$eliminado) {
                http_response_code(500);

                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo eliminar el producto.'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado correctamente.'
            ]);

        } catch (PDOException $e) {
            error_log($e->getMessage());

            // SQL Server: error 547 = conflicto con una clave foránea
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 547) {
                http_response_code(409);

                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede eliminar este producto porque está asociado a uno o varios pedidos.'
                ]);

                return;
            }

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el producto.'
            ]);

        } catch (Throwable $e) {
            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el producto.'
            ]);
        }
    }
}
