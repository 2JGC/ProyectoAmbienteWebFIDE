<?php

declare(strict_types=1);

/**
 * Modelo Reserva
 * Gestiona el acceso a la tabla `reservas`, incluyendo validación de
 * solapamiento de fechas para una misma motocicleta.
 */
class Reserva
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Verifica si existe una reserva activa que se solape con el rango de fechas dado.
     */
    public function existeSolapamiento(int $idMoto, string $fechaInicio, string $fechaFin): bool
    {
        $sql = "SELECT COUNT(*) FROM reservas
                WHERE id_moto = :id_moto
                AND estado IN ('pendiente','confirmada','en_curso')
                AND fecha_inicio <= :fecha_fin AND fecha_fin >= :fecha_inicio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_moto'      => $idMoto,
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function crear(array $datos): bool
    {
        $sql = 'INSERT INTO reservas (id_usuario, id_moto, fecha_inicio, fecha_fin, total_dias, total_pago, estado)
                VALUES (:id_usuario, :id_moto, :fecha_inicio, :fecha_fin, :total_dias, :total_pago, :estado)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_usuario'   => $datos['id_usuario'],
            ':id_moto'      => $datos['id_moto'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin'    => $datos['fecha_fin'],
            ':total_dias'   => $datos['total_dias'],
            ':total_pago'   => $datos['total_pago'],
            ':estado'       => $datos['estado'] ?? 'pendiente',
        ]);
    }

    /** Historial de reservas de un usuario específico, con datos de la moto. */
    public function listarPorUsuario(int $idUsuario): array
    {
        $sql = 'SELECT r.*, m.marca, m.modelo, m.imagen
                FROM reservas r
                JOIN motocicletas m ON m.id_moto = r.id_moto
                WHERE r.id_usuario = :id_usuario
                ORDER BY r.fecha_creacion DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    /** Todas las reservas con datos de usuario y motocicleta (panel admin). */
    public function listarTodas(): array
    {
        $sql = 'SELECT r.*, m.marca, m.modelo, u.nombre, u.apellidos
                FROM reservas r
                JOIN motocicletas m ON m.id_moto = r.id_moto
                JOIN usuarios u ON u.id_usuario = r.id_usuario
                ORDER BY r.fecha_creacion DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM reservas WHERE id_reserva = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE reservas SET estado = :estado WHERE id_reserva = :id');
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    public function contarTotal(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM reservas')->fetchColumn();
    }

    /** Suma de ingresos de reservas confirmadas/finalizadas (para el dashboard). */
    public function sumaIngresos(): float
    {
        $sql = "SELECT COALESCE(SUM(total_pago),0) FROM reservas WHERE estado IN ('confirmada','en_curso','finalizada')";
        return (float) $this->db->query($sql)->fetchColumn();
    }

    public function contarPorEstado(string $estado): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM reservas WHERE estado = :estado');
        $stmt->execute([':estado' => $estado]);
        return (int) $stmt->fetchColumn();
    }
}
