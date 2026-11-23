<?php

use App\Controllers\CategoriaController;
use App\Controllers\ClienteController;
use App\Controllers\ProductoController;
use App\Controllers\UsuarioController;
use App\Controllers\PedidoController;
use App\Controllers\DetallePedidoController;

// =====================================
// CARGAR AUTOLOAD Y CONFIGURACIÓN
// =====================================

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

// Cargar variables del archivo .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->load();
} catch (Throwable $e) {
    error_log('Error al cargar .env: ' . $e->getMessage());
}

// =====================================
// CONFIGURACIÓN JSON
// =====================================

header('Content-Type: application/json; charset=utf-8');

// =====================================
// CONEXIÓN A LA BASE DE DATOS
// =====================================

try {
    $database = new Database();
    $conexion = $database->conectar();

} catch (Throwable $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con la base de datos.'
    ]);

    exit;
}

// =====================================
// OBTENER PARÁMETROS
// =====================================

$metodo = $_SERVER['REQUEST_METHOD'];
$recurso = $_GET['recurso'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$accion = $_GET['accion'] ?? '';
$vista = $_GET['vista'] ?? '';

// =====================================
// LOGIN
// =====================================

if ($recurso === 'login' && $metodo === 'POST') {

    try {

        $controlador = new UsuarioController($conexion);
        $controlador->login();

    } catch (Throwable $e) {

        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Error al iniciar sesión.'
        ]);
    }

    exit;
}

// =====================================
// SELECCIONAR RECURSO
// =====================================

switch ($recurso) {

    // =====================================
    // CATEGORÍAS
    // =====================================

    case 'categorias':

        $controlador = new CategoriaController($conexion);

        switch ($metodo) {

            case 'GET':

                if ($id !== null) {
                    $controlador->obtener($id);
                } else {
                    $controlador->listar();
                }

                break;

            case 'POST':

                $controlador->crear();

                break;

            case 'PUT':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID de la categoría.'
                    ]);
                    break;
                }

                $controlador->actualizar($id);

                break;

            case 'DELETE':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID de la categoría.'
                    ]);
                    break;
                }

                $controlador->eliminar($id);

                break;

            default:

                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido para categorías.'
                ]);

                break;
        }

        break;

    // =====================================
    // PRODUCTOS
    // =====================================

    case 'productos':

        $controlador = new ProductoController($conexion);

        switch ($metodo) {

            case 'GET':

                if ($vista === 'catalogo') {
                    $controlador->listarActivos();
                } elseif ($id !== null) {
                    $controlador->obtener($id);
                } else {
                    $controlador->listar();
                }

                break;

            case 'POST':

                $controlador->crear();

                break;

            case 'PUT':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del producto.'
                    ]);
                    break;
                }

                if ($accion === 'estado') {
                    $controlador->cambiarEstado($id);
                } else {
                    $controlador->actualizar($id);
                }

                break;

            case 'DELETE':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del producto.'
                    ]);
                    break;
                }

                $controlador->eliminar($id);

                break;

            default:

                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido para productos.'
                ]);

                break;
        }

        break;

    // =====================================
    // CLIENTES
    // =====================================

    case 'clientes':

        $controlador = new ClienteController($conexion);

        switch ($metodo) {

            case 'GET':

                if ($id !== null) {
                    $controlador->obtener($id);
                } else {
                    $controlador->listar();
                }

                break;

            case 'POST':

                $controlador->crear();

                break;

            case 'PUT':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del cliente.'
                    ]);
                    break;
                }

                $controlador->actualizar($id);

                break;

            case 'DELETE':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del cliente.'
                    ]);
                    break;
                }

                $controlador->eliminar($id);

                break;

            default:

                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido para clientes.'
                ]);

                break;
        }

        break;

    // =====================================
    // USUARIOS
    // =====================================

    case 'usuarios':

        $controlador = new UsuarioController($conexion);

        switch ($metodo) {

            case 'GET':

                if ($id !== null) {
                    $controlador->obtener($id);
                } else {
                    $controlador->listar();
                }

                break;

            case 'POST':

                $controlador->crear();

                break;

            case 'PUT':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del usuario.'
                    ]);
                    break;
                }

                if ($accion === 'contrasena') {
                    $controlador->actualizarContrasena($id);
                } else {
                    $controlador->actualizar($id);
                }

                break;

            case 'DELETE':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del usuario.'
                    ]);
                    break;
                }

                $controlador->eliminar($id);

                break;

            default:

                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido para usuarios.'
                ]);

                break;
        }

        break;

    // =====================================
    // PEDIDOS
    // =====================================

    case 'pedidos':

        $controlador = new PedidoController($conexion);

        switch ($metodo) {

            case 'GET':

                if ($id !== null) {
                    $controlador->obtenerPorId($id);
                } else {
                    $controlador->listar();
                }

                break;

            case 'POST':

                $controlador->crear();

                break;

            case 'PUT':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del pedido.'
                    ]);
                    break;
                }

                $controlador->actualizar($id);

                break;

            case 'DELETE':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del pedido.'
                    ]);
                    break;
                }

                $controlador->eliminar($id);

                break;

            default:

                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido para pedidos.'
                ]);

                break;
        }

        break;

    // =====================================
    // DETALLES DE PEDIDO
    // =====================================

    case 'detalles':

        $controlador = new DetallePedidoController($conexion);

        switch ($metodo) {

            case 'GET':

                if ($id !== null) {
                    $controlador->obtenerPorId($id);
                } else {
                    $controlador->listar();
                }

                break;

            case 'POST':

                $controlador->crear();

                break;

            case 'PUT':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del detalle.'
                    ]);
                    break;
                }

                $controlador->actualizar($id);

                break;

            case 'DELETE':

                if ($id === null) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Se requiere el ID del detalle.'
                    ]);
                    break;
                }

                $controlador->eliminar($id);

                break;

            default:

                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Método no permitido para detalles.'
                ]);

                break;
        }

        break;

    // =====================================
    // RECURSO NO ENCONTRADO
    // =====================================

    default:

        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Recurso no encontrado.'
        ]);

        break;
}
