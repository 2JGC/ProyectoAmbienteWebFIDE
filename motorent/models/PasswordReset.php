<?php

declare(strict_types=1);

/**
 * Modelo PasswordReset
 * Gestiona el acceso a la tabla `password_resets`, usada en el flujo
 * de recuperación de contraseña mediante token temporal.
 */
class PasswordReset
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Crea un nuevo token de recuperación válido por 1 hora. */
    public function crear(string $email, string $token): bool
    {
        $expira = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $sql = 'INSERT INTO password_resets (email, token, fecha_expira) VALUES (:email, :token, :expira)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':email' => $email, ':token' => $token, ':expira' => $expira]);
    }

    /** Busca un token vigente (no usado y no expirado). */
    public function buscarTokenValido(string $token): array|false
    {
        $sql = 'SELECT * FROM password_resets
                WHERE token = :token AND usado = 0 AND fecha_expira >= NOW()
                ORDER BY fecha_creacion DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public function marcarUsado(int $idReset): bool
    {
        $stmt = $this->db->prepare('UPDATE password_resets SET usado = 1 WHERE id_reset = :id');
        return $stmt->execute([':id' => $idReset]);
    }
}
