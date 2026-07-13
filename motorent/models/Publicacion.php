<?php

declare(strict_types=1);

/**
 * Modelo Publicacion
 * Gestiona el acceso a la tabla `publicaciones` (galería comunitaria).
 */
class Publicacion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Publicaciones aprobadas, visibles en la galería pública. */
    public function listarAprobadas(): array
    {
        $sql = "SELECT p.*, u.nombre, u.apellidos
                FROM publicaciones p
                JOIN usuarios u ON u.id_usuario = p.id_usuario
                WHERE p.estado = 'aprobada'
                ORDER BY p.fecha_creacion DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Todas las publicaciones (panel admin). */
    public function listarTodas(): array
    {
        $sql = 'SELECT p.*, u.nombre, u.apellidos
                FROM publicaciones p
                JOIN usuarios u ON u.id_usuario = p.id_usuario
                ORDER BY p.fecha_creacion DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function crear(array $datos): bool
    {
        $sql = 'INSERT INTO publicaciones (id_usuario, titulo, descripcion, imagen, estado)
                VALUES (:id_usuario, :titulo, :descripcion, :imagen, :estado)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_usuario'  => $datos['id_usuario'],
            ':titulo'      => $datos['titulo'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':imagen'      => $datos['imagen'],
            ':estado'      => $datos['estado'] ?? 'pendiente',
        ]);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM publicaciones WHERE id_publicacion = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE publicaciones SET estado = :estado WHERE id_publicacion = :id');
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM publicaciones WHERE id_publicacion = :id');
        return $stmt->execute([':id' => $id]);
    }
}
