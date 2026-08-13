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
// Mientras desarrollamos queremos ver TODOS los errores en pantalla,
// por eso están activados los dos. En un servidor real esto se debe
// apagar (display_errors en '0') para no mostrarle errores al usuario final.
error_reporting(E_ALL);
ini_set('display_errors', '1');

// -----------------------------------------------------------------
// Rutas base del proyecto
// -----------------------------------------------------------------
// BASE_PATH apunta a la raíz del proyecto (un nivel arriba de /config),
// así cualquier archivo puede armar rutas absolutas sin adivinar dónde está.
define('BASE_PATH', dirname(__DIR__));
// BASE_URL es la carpeta pública desde donde se sirve el sitio en XAMPP.
// Si mueves el proyecto de carpeta dentro de htdocs, hay que actualizar esto.
define('BASE_URL', '/motorent/public'); // Ajustar según carpeta en htdocs de XAMPP

// -----------------------------------------------------------------
// Sesión
// -----------------------------------------------------------------
// Iniciamos la sesión de PHP una sola vez (si ya hay una activa no la duplicamos).
// Aquí se guardan cosas como el usuario logueado, su rol y los mensajes flash.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------------------------------------------
// Autoload sencillo de Models y Controllers
// -----------------------------------------------------------------
// En vez de escribir un "require" por cada clase que usamos, PHP llama
// automáticamente a esta función cuando encuentra una clase que no conoce
// todavía. Buscamos el archivo con el mismo nombre en models/, controllers/
// o core/, y si existe lo incluimos.
spl_autoload_register(function (string $class): void {
    $paths = [
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/core/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Autoload de las librerías externas instaladas con Composer (Firebase, PHPMailer, etc).
require_once BASE_PATH . '/vendor/autoload.php';
// Clase de conexión a la base de datos, la necesitan casi todos los modelos.
require_once BASE_PATH . '/config/Database.php';

// -----------------------------------------------------------------
// Helpers de seguridad y utilería general
// -----------------------------------------------------------------
// A partir de aquí van funciones "sueltas" (no pertenecen a ninguna clase)
// que se usan en todo el proyecto: vistas, controladores, etc.

/**
 * Genera (o reutiliza) un token CSRF almacenado en sesión.
 */
function csrf_token(): string
{
    // Si todavía no existe un token para esta sesión, generamos uno
    // aleatorio (32 bytes) y lo guardamos. Así el mismo token se
    // reutiliza mientras dure la sesión del usuario.
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
    // Esto es un atajo para no tener que escribir el <input hidden> a mano
    // en cada formulario, se llama directo desde las vistas con csrf_field().
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Valida el token CSRF recibido por POST contra el almacenado en sesión.
 */
function csrf_verify(): bool
{
    // Comparamos el token que llegó en el formulario contra el que
    // guardamos en sesión. Usamos hash_equals() en vez de "==" porque
    // es una comparación resistente a "timing attacks".
    $recibido = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $recibido);
}

/**
 * Escapa una cadena para salida segura en HTML.
 */
function e(?string $valor): string
{
    // Función corta a propósito porque se usa muchísimas veces en las vistas.
    // Convierte caracteres como < > " en su versión segura para que un
    // usuario no pueda meter HTML/JavaScript malicioso (XSS) a través de
    // datos guardados en la base de datos.
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige a una ruta interna de la aplicación y detiene la ejecución.
 */
function redirect(string $ruta): void
{
    // ltrim quita la "/" inicial de $ruta para no duplicarla al pegarla
    // después de BASE_URL. El exit es importante: sin él, el código
    // seguiría ejecutándose después de mandar la redirección.
    header('Location: ' . BASE_URL . '/' . ltrim($ruta, '/'));
    exit;
}

/**
 * Construye una URL absoluta (con esquema y host) a partir de una ruta interna.
 * Necesaria para enlaces usados fuera del navegador, como los de correos electrónicos,
 * ya que una ruta relativa ahí resulta en una URL inválida.
 */
function site_url(string $ruta): string
{
    // A diferencia de redirect(), aquí sí necesitamos la URL completa
    // (con http:// y el dominio) porque un correo no sabe en qué sitio
    // se está viendo el enlace.
    $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $esquema . '://' . $host . BASE_URL . '/' . ltrim($ruta, '/');
}

/**
 * Indica si hay un usuario autenticado.
 */
function is_logueado(): bool
{
    // Cuando el usuario inicia sesión guardamos su id en $_SESSION.
    // Si esa llave existe, significa que hay alguien logueado.
    return isset($_SESSION['usuario_id']);
}

/**
 * Indica si el usuario autenticado es administrador.
 */
function is_admin(): bool
{
    // Primero confirmamos que haya sesión, y luego revisamos el rol
    // guardado al hacer login.
    return is_logueado() && ($_SESSION['usuario_rol'] ?? '') === 'administrador';
}

/**
 * Obliga a que exista sesión activa; de lo contrario redirige al login.
 */
function requiere_login(): void
{
    // Se llama al inicio de cualquier acción que solo pueda usar un
    // usuario logueado (reservar, ver perfil, etc). Si no hay sesión,
    // mandamos un mensaje de error y lo devolvemos al login.
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
    // Primero exige login (por si acaso), y luego revisa el rol.
    // Así protegemos todo el panel de administración con una sola línea.
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
    // Un mensaje "flash" es un mensaje que se guarda en sesión, se
    // muestra en la siguiente página y luego se borra automáticamente
    // (eso pasa en views/layouts/header.php con el unset()).
    $_SESSION['flash_success'] = $mensaje;
}

/**
 * Guarda un mensaje flash (error) que se muestra una sola vez.
 */
function flash_error(string $mensaje): void
{
    $_SESSION['flash_error'] = $mensaje;
}
