<?php

declare(strict_types=1);

/**
 * Database.php
 * Encapsula la conexión PDO a MySQL bajo el patrón Singleton,
 * de forma que toda la aplicación reutilice la misma conexión.
 */
class Database
{
    // Aquí guardamos la única conexión que va a existir mientras
    // corre la aplicación. Empieza en null porque todavía no nos
    // hemos conectado a nada.
    private static ?PDO $instancia = null;

    // Ajustar credenciales según el entorno local de XAMPP
    private const HOST    = '127.0.0.1';
    private const PUERTO  = '3306';
    private const NOMBRE  = 'motorent';
    private const USUARIO = 'root';
    private const CLAVE   = '';

    private function __construct()
    {
        // Constructor privado: no se permite instanciar directamente.
        // Así obligamos a que todo el mundo use Database::getConnection()
        // en vez de hacer "new Database()" por su cuenta.
    }

    /**
     * Devuelve la instancia única de PDO conectada a la base de datos.
     */
    public static function getConnection(): PDO
    {
        // Este es el truco del patrón Singleton: la primera vez que
        // alguien pide la conexión, la creamos. Las siguientes veces
        // devolvemos la misma conexión ya abierta, en vez de abrir
        // una nueva cada vez (eso sería un desperdicio de recursos).
        if (self::$instancia === null) {
            $dsn = 'mysql:host=' . self::HOST . ';port=' . self::PUERTO .
                   ';dbname=' . self::NOMBRE . ';charset=utf8mb4';

            $opciones = [
                // Que PDO lance excepciones cuando algo sale mal en una
                // consulta, en vez de fallar en silencio.
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Que los resultados vengan como arrays asociativos
                // (['id_usuario' => 1, 'nombre' => 'Ana', ...]) en vez
                // de objetos o arrays numéricos.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Prepared statements reales
            ];

            try {
                self::$instancia = new PDO($dsn, self::USUARIO, self::CLAVE, $opciones);
            } catch (PDOException $e) {
                // No exponer detalles sensibles en producción
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }

        return self::$instancia;
    }
}
