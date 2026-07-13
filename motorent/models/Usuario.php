<?php

declare(strict_types=1);

/**
 * Modelo Usuario
 * Gestiona el acceso a la tabla `usuarios` mediante PDO y prepared statements.
 */
class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Busca un usuario por su email (usado en login y validaciones). */
    public function buscarPorEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /** Busca un usuario por su id. */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Registra un nuevo usuario con contraseña ya hasheada. */
    public function crear(array $datos): bool
    {
        $sql = 'INSERT INTO usuarios (nombre, apellidos, email, password, telefono, cedula, rol)
                VALUES (:nombre, :apellidos, :email, :password, :telefono, :cedula, :rol)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $datos['nombre'],
            ':apellidos' => $datos['apellidos'],
            ':email'     => $datos['email'],
            ':password'  => $datos['password'], // ya viene hasheada
            ':telefono'  => $datos['telefono'] ?? null,
            ':cedula'    => $datos['cedula'] ?? null,
            ':rol'       => $datos['rol'] ?? 'cliente',
        ]);
    }

    /** Actualiza los datos de perfil (sin tocar la contraseña). */
    public function actualizarPerfil(int $id, array $datos): bool
    {
        $sql = 'UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos,
                telefono = :telefono, cedula = :cedula WHERE id_usuario = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $datos['nombre'],
            ':apellidos' => $datos['apellidos'],
            ':telefono'  => $datos['telefono'] ?? null,
            ':cedula'    => $datos['cedula'] ?? null,
            ':id'        => $id,
        ]);
    }

    /** Actualiza únicamente la foto de perfil. */
    public function actualizarFoto(int $id, string $rutaFoto): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET foto_perfil = :foto WHERE id_usuario = :id');
        return $stmt->execute([':foto' => $rutaFoto, ':id' => $id]);
    }

    /** Actualiza la contraseña (ya hasheada). */
    public function actualizarPassword(int $id, string $nuevoHash): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET password = :password WHERE id_usuario = :id');
        return $stmt->execute([':password' => $nuevoHash, ':id' => $id]);
    }

    /** Actualiza la contraseña buscando por email (usado en recuperación). */
    public function actualizarPasswordPorEmail(string $email, string $nuevoHash): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET password = :password WHERE email = :email');
        return $stmt->execute([':password' => $nuevoHash, ':email' => $email]);
    }

    /** Lista todos los usuarios (panel de administración). */
    public function listarTodos(): array
    {
        $stmt = $this->db->query('SELECT * FROM usuarios ORDER BY fecha_registro DESC');
        return $stmt->fetchAll();
    }

    /** Cambia el estado (activo/inactivo) de un usuario. */
    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET estado = :estado WHERE id_usuario = :id');
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    /** Cambia el rol de un usuario. */
    public function cambiarRol(int $id, string $rol): bool
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET rol = :rol WHERE id_usuario = :id');
        return $stmt->execute([':rol' => $rol, ':id' => $id]);
    }

    /** Elimina un usuario por id. */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM usuarios WHERE id_usuario = :id');
        return $stmt->execute([':id' => $id]);
    }

    /** Cuenta el total de usuarios registrados (para el dashboard). */
    public function contarTotal(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }
}
