<?php

namespace App\Models;

use PDO;

class Usuario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    // LISTAR USUARIOS
    public function listar(): array
    {
        $sql = "SELECT IdUsuario, Nombres, Apellidos, Correo, Rol
                FROM Usuario
                ORDER BY IdUsuario DESC";

        $consulta = $this->conexion->query($sql);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // BUSCAR USUARIO POR ID
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT IdUsuario, Nombres, Apellidos, Correo, Rol
                FROM Usuario
                WHERE IdUsuario = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    // BUSCAR USUARIO POR CORREO
    public function buscarPorCorreo(string $correo): ?array
    {
        $sql = "SELECT IdUsuario, Nombres, Apellidos, Correo,
                       Contrasena, Rol
                FROM Usuario
                WHERE Correo = :correo";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['correo' => $correo]);

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    // REGISTRAR USUARIO
    public function crear(
        string $nombres,
        string $apellidos,
        string $correo,
        string $contrasena,
        string $rol
    ): int {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Usuario
                (Nombres, Apellidos, Correo, Contrasena, Rol)
                OUTPUT INSERTED.IdUsuario
                VALUES (:nombres, :apellidos, :correo, :contrasena, :rol)";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'contrasena' => $hash,
            'rol' => $rol
        ]);

        return (int) $consulta->fetchColumn();
    }

    // ACTUALIZAR USUARIO
    public function actualizar(
        int $id,
        string $nombres,
        string $apellidos,
        string $correo,
        string $rol
    ): bool {
        $sql = "UPDATE Usuario
                SET Nombres = :nombres,
                    Apellidos = :apellidos,
                    Correo = :correo,
                    Rol = :rol
                WHERE IdUsuario = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'id' => $id,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => $correo,
            'rol' => $rol
        ]);

        return $consulta->rowCount() > 0;
    }

    // ACTUALIZAR CONTRASEÑA
    public function actualizarContrasena(
        int $id,
        string $nuevaContrasena
    ): bool {
        $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

        $sql = "UPDATE Usuario
                SET Contrasena = :contrasena
                WHERE IdUsuario = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            'id' => $id,
            'contrasena' => $hash
        ]);

        return $consulta->rowCount() > 0;
    }

    // ELIMINAR USUARIO
    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM Usuario
                WHERE IdUsuario = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(['id' => $id]);

        return $consulta->rowCount() > 0;
    }
}
