<?php

declare(strict_types=1);

/**
 * index.php - Front Controller
 * Punto de entrada único de la aplicación. Traduce la URL solicitada
 * a un controlador y una acción, siguiendo el patrón MVC.
 */

require_once dirname(__DIR__) . '/config/config.php';

// -----------------------------------------------------------------
// Obtiene la ruta solicitada (sin query string ni base URL)
// -----------------------------------------------------------------
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$ruta = '/' . trim(substr($uri, strlen($scriptDir)), '/');
$ruta = $ruta === '//' ? '/' : $ruta;

// -----------------------------------------------------------------
// Tabla de rutas: 'ruta' => [Controlador, método]
// -----------------------------------------------------------------
$rutas = [
    '/'                     => ['HomeController', 'index'],

    // Autenticación
    '/registro'             => ['AuthController', 'register'],
    '/login'                => ['AuthController', 'login'],
    '/logout'               => ['AuthController', 'logout'],
    '/olvide-password'      => ['AuthController', 'forgotPassword'],
    '/reset-password'       => ['AuthController', 'resetPassword'],

    // Catálogo
    '/catalogo'             => ['MotocicletaController', 'catalogo'],
    '/moto'                 => ['MotocicletaController', 'detalle'],

    // Reservas
    '/reservar'             => ['ReservaController', 'reservar'],
    '/historial'            => ['ReservaController', 'historial'],
    '/reservar/cancelar'    => ['ReservaController', 'cancelar'],

    // Perfil
    '/perfil'               => ['PerfilController', 'index'],

    // Galería y contacto
    '/galeria'              => ['GaleriaController', 'index'],
    '/contacto'             => ['ContactoController', 'index'],

    // Administración
    '/admin/dashboard'      => ['AdminController', 'dashboard'],
    '/admin/motos'          => ['AdminController', 'motos'],
    '/admin/reservas'       => ['AdminController', 'reservas'],
    '/admin/usuarios'       => ['AdminController', 'usuarios'],
    '/admin/mensajes'       => ['AdminController', 'mensajes'],
    '/admin/publicaciones'  => ['AdminController', 'publicaciones'],
];

if (array_key_exists($ruta, $rutas)) {
    [$controlador, $metodo] = $rutas[$ruta];
    $instancia = new $controlador();
    $instancia->$metodo();
} else {
    http_response_code(404);
    require BASE_PATH . '/views/errores/404.php';
}
