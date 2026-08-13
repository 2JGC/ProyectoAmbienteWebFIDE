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

    /** Crea un nuevo token de recuperación válido por 30 minutos. */
    public function crear(string $email, string $token): bool
    {
        // Calculamos la fecha/hora exacta en la que el token deja de
        // servir. Le damos solo 30 minutos a propósito: si alguien
        // intercepta el enlace del correo, la ventana para usarlo es
        // corta.
        $expira = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');
        $sql = 'INSERT INTO password_resets (email, token, fecha_expira) VALUES (:email, :token, :expira)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':email' => $email, ':token' => $token, ':expira' => $expira]);
    }

    /** Busca un token vigente (no usado y no expirado). */
    public function buscarTokenValido(string $token): array|false
    {
        // Un token solo sirve si: es exactamente el que buscamos,
        // todavía no se ha usado (usado = 0) y no ha pasado su fecha
        // de expiración. Si alguien intenta reusar un enlace viejo o
        // ya gastado, esta consulta simplemente no lo va a encontrar.
        $sql = 'SELECT * FROM password_resets
                WHERE token = :token AND usado = 0 AND fecha_expira >= NOW()
                ORDER BY fecha_creacion DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    public function marcarUsado(int $idReset): bool
    {
        // Después de que el usuario cambia su contraseña con éxito,
        // marcamos el token como usado para que no se pueda reutilizar
        // el mismo enlace una segunda vez.
        $stmt = $this->db->prepare('UPDATE password_resets SET usado = 1 WHERE id_reset = :id');
        return $stmt->execute([':id' => $idReset]);
    }
}
