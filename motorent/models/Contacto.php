<?php

declare(strict_types=1);

/**
 * Modelo Contacto
 * Gestiona el acceso a la tabla `contactos`.
 */
class Contacto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function crear(array $datos): bool
    {
        $sql = 'INSERT INTO contactos (nombre, email, asunto, mensaje)
                VALUES (:nombre, :email, :asunto, :mensaje)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'  => $datos['nombre'],
            ':email'   => $datos['email'],
            ':asunto'  => $datos['asunto'],
            ':mensaje' => $datos['mensaje'],
        ]);
    }

    public function listarTodos(): array
    {
        $stmt = $this->db->query('SELECT * FROM contactos ORDER BY fecha_envio DESC');
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM contactos WHERE id_contacto = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function marcarLeido(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE contactos SET leido = 1 WHERE id_contacto = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM contactos WHERE id_contacto = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function contarNoLeidos(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM contactos WHERE leido = 0')->fetchColumn();
    }
}
