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
        // Esta consulta es el corazón de la validación de disponibilidad:
        // dos rangos de fechas se solapan si uno empieza antes de que el
        // otro termine, Y termina después de que el otro empieza.
        // Por ejemplo, si ya hay una reserva del 10 al 15, y alguien pide
        // del 12 al 20, sí se solapan aunque no sean las mismas fechas.
        //
        // Solo consideramos reservas 'pendiente', 'confirmada' o 'en_curso'
        // porque una reserva 'cancelada' o 'finalizada' ya no ocupa la moto.
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
        // Toda reserva nueva entra como 'pendiente' salvo que se indique
        // otro estado; es el admin quien luego la confirma o la rechaza.
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
        // Hacemos un JOIN con motocicletas para traer marca/modelo/imagen
        // en la misma consulta, y así no tener que ir moto por moto
        // preguntando sus datos desde la vista.
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
        // Igual que arriba, pero además unimos con usuarios porque el
        // admin necesita ver de quién es cada reserva.
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
        // Este método se usa tanto para que el cliente cancele su propia
        // reserva, como para que el admin la mueva entre pendiente,
        // confirmada, en_curso, finalizada o cancelada.
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
        // Solo sumamos reservas que sí van a generar (o ya generaron)
        // dinero real: una reserva 'pendiente' todavía no cuenta, y una
        // 'cancelada' nunca va a contar.
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
