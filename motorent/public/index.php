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
// Aquí "traducimos" la URL que escribió el usuario en algo que podamos
// comparar contra nuestra tabla de rutas. Por ejemplo, si el usuario
// entra a http://localhost/motorent/public/catalogo?categoria=Naked,
// solo nos interesa la parte "/catalogo" (sin el dominio, sin la carpeta
// del proyecto y sin el ?categoria=...).
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$ruta = '/' . trim(substr($uri, strlen($scriptDir)), '/');
// Si la ruta calculada queda vacía ("//"), en realidad es la página de inicio.
$ruta = $ruta === '//' ? '/' : $ruta;

// -----------------------------------------------------------------
// Tabla de rutas: 'ruta' => [Controlador, método]
// -----------------------------------------------------------------
// Esta es básicamente la "guía telefónica" del sitio: por cada URL que
// queremos soportar, decimos qué clase (Controller) y qué método debe
// encargarse de responder. No usamos ningún framework, así que este
// array reemplaza a algo como las rutas de Laravel o Express.
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
    // El flujo de reserva va en 3 pasos: elegir fechas (/reservar),
    // revisar el resumen y aceptar términos (/checkout), y finalmente
    // confirmar y crear la reserva de verdad (/confirmacion).
    '/reservar'             => ['ReservaController', 'reservar'],
    '/checkout'             => ['ReservaController', 'checkout'],
    '/confirmacion'         => ['ReservaController', 'confirmacion'],
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

// Si la ruta pedida existe en la tabla de arriba, creamos el controlador
// correspondiente y llamamos al método indicado (eso ejecuta la lógica
// y termina incluyendo la vista). Si no existe, mostramos un 404.
if (array_key_exists($ruta, $rutas)) {
    [$controlador, $metodo] = $rutas[$ruta];
    $instancia = new $controlador();
    $instancia->$metodo();
} else {
    http_response_code(404);
    require BASE_PATH . '/views/errores/404.php';
}
