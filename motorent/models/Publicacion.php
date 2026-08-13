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
        // Solo mostramos al público las publicaciones que un admin ya
        // revisó y aprobó; así evitamos que cualquiera suba una imagen
        // inapropiada y aparezca de inmediato en el sitio.
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
        // Esta versión sin filtro de estado es la que usa el admin para
        // ver también las pendientes y rechazadas, y poder moderarlas.
        $sql = 'SELECT p.*, u.nombre, u.apellidos
                FROM publicaciones p
                JOIN usuarios u ON u.id_usuario = p.id_usuario
                ORDER BY p.fecha_creacion DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function crear(array $datos): bool
    {
        // Toda publicación nueva nace en 'pendiente' salvo que se diga
        // lo contrario, porque necesita aprobación de un admin antes de
        // hacerse pública. La calificación es la cantidad de estrellas
        // (1 a 5) que el usuario le puso a su experiencia de alquiler.
        $sql = 'INSERT INTO publicaciones (id_usuario, titulo, descripcion, imagen, calificacion, estado)
                VALUES (:id_usuario, :titulo, :descripcion, :imagen, :calificacion, :estado)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_usuario'   => $datos['id_usuario'],
            ':titulo'       => $datos['titulo'],
            ':descripcion'  => $datos['descripcion'] ?? null,
            ':imagen'       => $datos['imagen'],
            ':calificacion' => $datos['calificacion'] ?? 5,
            ':estado'       => $datos['estado'] ?? 'pendiente',
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
        // El admin usa esto para aprobar o rechazar una publicación.
        $stmt = $this->db->prepare('UPDATE publicaciones SET estado = :estado WHERE id_publicacion = :id');
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM publicaciones WHERE id_publicacion = :id');
        return $stmt->execute([':id' => $id]);
    }
}
