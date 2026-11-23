<?php

namespace App\Controllers;

use App\Models\Usuario;
use PDO;
use Throwable;

class UsuarioController
{
    private Usuario $modelo;

    public function __construct(PDO $conexion)
    {
        $this->modelo = new Usuario($conexion);
    }

    // LISTAR USUARIOS
    public function listar(): void
    {
        try {
            $usuarios = $this->modelo->listar();

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $usuarios
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al listar usuarios'
            ]);
        }
    }

    // OBTENER USUARIO POR ID
    public function obtener(int $id): void
    {
        try {
            $usuario = $this->modelo->obtenerPorId($id);

            if (!$usuario) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);

                return;
            }

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => $usuario
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener usuario'
            ]);
        }
    }

    // CREAR USUARIO
    public function crear(): void
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            if (
                !is_array($datos) ||
                !is_string($datos['nombres'] ?? null) ||
                !is_string($datos['apellidos'] ?? null) ||
                !is_string($datos['correo'] ?? null) ||
                !is_string($datos['contrasena'] ?? null) ||
                !is_string($datos['rol'] ?? null) ||
                trim($datos['nombres']) === '' ||
                trim($datos['apellidos']) === '' ||
                trim($datos['correo']) === '' ||
                trim($datos['contrasena']) === '' ||
                trim($datos['rol']) === '' ||
                !filter_var(trim($datos['correo']), FILTER_VALIDATE_EMAIL)
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Datos del usuario inválidos'
                ]);

                return;
            }

            $correo = trim($datos['correo']);

            if ($this->modelo->buscarPorCorreo($correo)) {
                http_response_code(409);

                echo json_encode([
                    'success' => false,
                    'message' => 'El correo ya está registrado'
                ]);

                return;
            }

            $id = $this->modelo->crear(
                trim($datos['nombres']),
                trim($datos['apellidos']),
                $correo,
                $datos['contrasena'],
                trim($datos['rol'])
            );

            http_response_code(201);

            echo json_encode([
                'success' => true,
                'message' => 'Usuario registrado correctamente',
                'id' => $id
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar usuario'
            ]);
        }
    }

    // ACTUALIZAR USUARIO
    public function actualizar(int $id): void
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            if (
                !is_array($datos) ||
                !is_string($datos['nombres'] ?? null) ||
                !is_string($datos['apellidos'] ?? null) ||
                !is_string($datos['correo'] ?? null) ||
                !is_string($datos['rol'] ?? null) ||
                trim($datos['nombres']) === '' ||
                trim($datos['apellidos']) === '' ||
                trim($datos['correo']) === '' ||
                trim($datos['rol']) === '' ||
                !filter_var(trim($datos['correo']), FILTER_VALIDATE_EMAIL)
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'Datos del usuario inválidos'
                ]);

                return;
            }

            $usuarioExistente = $this->modelo->obtenerPorId($id);

            if (!$usuarioExistente) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);

                return;
            }

            $correo = trim($datos['correo']);
            $usuarioCorreo = $this->modelo->buscarPorCorreo($correo);

            if (
                $usuarioCorreo &&
                (int) $usuarioCorreo['IdUsuario'] !== $id
            ) {
                http_response_code(409);

                echo json_encode([
                    'success' => false,
                    'message' => 'El correo ya pertenece a otro usuario'
                ]);

                return;
            }

            $this->modelo->actualizar(
                $id,
                trim($datos['nombres']),
                trim($datos['apellidos']),
                $correo,
                trim($datos['rol'])
            );

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Usuario actualizado correctamente'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar usuario'
            ]);
        }
    }

    // ACTUALIZAR CONTRASEÑA
    public function actualizarContrasena(int $id): void
    {
        try {
            $datos = json_decode(file_get_contents('php://input'), true);

            if (
                !is_array($datos) ||
                !is_string($datos['contrasena'] ?? null) ||
                trim($datos['contrasena']) === ''
            ) {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => 'La contraseña es obligatoria'
                ]);

                return;
            }

            if (!$this->modelo->obtenerPorId($id)) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);

                return;
            }

            $this->modelo->actualizarContrasena(
                $id,
                $datos['contrasena']
            );

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar contraseña'
            ]);
        }
    }

    // ELIMINAR USUARIO
    public function eliminar(int $id): void
    {
        try {
            if (!$this->modelo->obtenerPorId($id)) {
                http_response_code(404);

                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);

                return;
            }

            $this->modelo->eliminar($id);

            http_response_code(200);

            echo json_encode([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);

        } catch (Throwable $e) {
            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar usuario'
            ]);
        }
    }
    public function login(): void
{
    try {

        $datos = json_decode(file_get_contents('php://input'), true);

        if (
            !is_array($datos) ||
            !is_string($datos['correo'] ?? null) ||
            !is_string($datos['contrasena'] ?? null) ||
            trim($datos['correo']) === '' ||
            $datos['contrasena'] === ''
        ) {
            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Correo y contraseña son obligatorios'
            ]);

            return;
        }

        $usuario = $this->modelo->buscarPorCorreo(trim($datos['correo']));

        if (
            !$usuario ||
            !password_verify($datos['contrasena'], $usuario['Contrasena'])
        ) {
            http_response_code(401);

            echo json_encode([
                'success' => false,
                'message' => 'Correo o contraseña incorrectos'
            ]);

            return;
        }

        http_response_code(200);

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'IdUsuario' => $usuario['IdUsuario'],
                'Nombres' => $usuario['Nombres'],
                'Apellidos' => $usuario['Apellidos'],
                'Correo' => $usuario['Correo'],
                'Rol' => $usuario['Rol']
            ]
        ]);

    } catch (Throwable $e) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'Error al iniciar sesión'
        ]);
    }
}

}
