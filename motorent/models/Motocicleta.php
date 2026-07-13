<?php

declare(strict_types=1);

/**
 * Modelo Motocicleta
 * Gestiona el acceso a la tabla `motocicletas`.
 */
class Motocicleta
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Devuelve todas las motocicletas, con filtro opcional de categoría/búsqueda. */
    public function listarTodas(?string $categoria = null, ?string $busqueda = null): array
    {
        $sql = 'SELECT * FROM motocicletas WHERE 1 = 1';
        $params = [];

        if (!empty($categoria)) {
            $sql .= ' AND categoria = :categoria';
            $params[':categoria'] = $categoria;
        }
        if (!empty($busqueda)) {
            $sql .= ' AND (marca LIKE :busqueda OR modelo LIKE :busqueda)';
            $params[':busqueda'] = '%' . $busqueda . '%';
        }
        $sql .= ' ORDER BY fecha_creacion DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Devuelve solo las motocicletas disponibles (para catálogo público). */
    public function listarDisponibles(): array
    {
        $stmt = $this->db->query("SELECT * FROM motocicletas WHERE estado = 'disponible' ORDER BY fecha_creacion DESC");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM motocicletas WHERE id_moto = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $datos): bool
    {
        $sql = 'INSERT INTO motocicletas (marca, modelo, anio, cilindraje, categoria, precio_dia, descripcion, imagen, placa, estado)
                VALUES (:marca, :modelo, :anio, :cilindraje, :categoria, :precio_dia, :descripcion, :imagen, :placa, :estado)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':marca'       => $datos['marca'],
            ':modelo'      => $datos['modelo'],
            ':anio'        => $datos['anio'],
            ':cilindraje'  => $datos['cilindraje'],
            ':categoria'   => $datos['categoria'],
            ':precio_dia'  => $datos['precio_dia'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':imagen'      => $datos['imagen'] ?? 'default-moto.jpg',
            ':placa'       => $datos['placa'],
            ':estado'      => $datos['estado'] ?? 'disponible',
        ]);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE motocicletas SET marca = :marca, modelo = :modelo, anio = :anio,
                cilindraje = :cilindraje, categoria = :categoria, precio_dia = :precio_dia,
                descripcion = :descripcion, placa = :placa, estado = :estado
                WHERE id_moto = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':marca'       => $datos['marca'],
            ':modelo'      => $datos['modelo'],
            ':anio'        => $datos['anio'],
            ':cilindraje'  => $datos['cilindraje'],
            ':categoria'   => $datos['categoria'],
            ':precio_dia'  => $datos['precio_dia'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':placa'       => $datos['placa'],
            ':estado'      => $datos['estado'],
            ':id'          => $id,
        ]);
    }

    /** Actualiza únicamente la imagen de una motocicleta. */
    public function actualizarImagen(int $id, string $imagen): bool
    {
        $stmt = $this->db->prepare('UPDATE motocicletas SET imagen = :imagen WHERE id_moto = :id');
        return $stmt->execute([':imagen' => $imagen, ':id' => $id]);
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE motocicletas SET estado = :estado WHERE id_moto = :id');
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM motocicletas WHERE id_moto = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function contarTotal(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM motocicletas')->fetchColumn();
    }

    public function contarDisponibles(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM motocicletas WHERE estado = 'disponible'")->fetchColumn();
    }
}
