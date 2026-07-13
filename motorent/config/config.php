<?php

/**
 * config.php
 * Configuración global de la aplicación MotoRent Costa Rica.
 * Define constantes, inicia la sesión y registra el autoload de clases.
 */

declare(strict_types=1);

// -----------------------------------------------------------------
// Reporte de errores (desactivar display_errors en producción)
// -----------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', '1');

// -----------------------------------------------------------------
// Rutas base del proyecto
// -----------------------------------------------------------------
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/motorent/public'); // Ajustar según carpeta en htdocs de XAMPP

// -----------------------------------------------------------------
// Sesión
// -----------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------------------------------------------
// Autoload sencillo de Models y Controllers
// -----------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once BASE_PATH . '/config/Database.php';

// -----------------------------------------------------------------
// Helpers de seguridad y utilería general
// -----------------------------------------------------------------

/**
 * Genera (o reutiliza) un token CSRF almacenado en sesión.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Imprime el input hidden con el token CSRF, listo para usar en formularios.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Valida el token CSRF recibido por POST contra el almacenado en sesión.
 */
function csrf_verify(): bool
{
    $recibido = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $recibido);
}

/**
 * Escapa una cadena para salida segura en HTML.
 */
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige a una ruta interna de la aplicación y detiene la ejecución.
 */
function redirect(string $ruta): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($ruta, '/'));
    exit;
}

/**
 * Indica si hay un usuario autenticado.
 */
function is_logueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Indica si el usuario autenticado es administrador.
 */
function is_admin(): bool
{
    return is_logueado() && ($_SESSION['usuario_rol'] ?? '') === 'administrador';
}

/**
 * Obliga a que exista sesión activa; de lo contrario redirige al login.
 */
function requiere_login(): void
{
    if (!is_logueado()) {
        $_SESSION['flash_error'] = 'Debe iniciar sesión para continuar.';
        redirect('/login');
    }
}

/**
 * Obliga a que el usuario autenticado sea administrador.
 */
function requiere_admin(): void
{
    requiere_login();
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'No tiene permisos para acceder a esta sección.';
        redirect('/');
    }
}

/**
 * Guarda un mensaje flash (éxito) que se muestra una sola vez.
 */
function flash_success(string $mensaje): void
{
    $_SESSION['flash_success'] = $mensaje;
}

/**
 * Guarda un mensaje flash (error) que se muestra una sola vez.
 */
function flash_error(string $mensaje): void
{
    $_SESSION['flash_error'] = $mensaje;
}
