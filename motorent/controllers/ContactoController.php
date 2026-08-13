<?php

declare(strict_types=1);

/**
 * ContactoController
 * Formulario público de contacto; guarda el mensaje en la tabla `contactos`.
 */
class ContactoController
{
    private Contacto $contactoModel;

    public function __construct()
    {
        $this->contactoModel = new Contacto();
    }

    public function index(): void
    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $nombre  = trim($_POST['nombre'] ?? '');
                $email   = trim($_POST['email'] ?? '');
                $asunto  = trim($_POST['asunto'] ?? '');
                $mensaje = trim($_POST['mensaje'] ?? '');

                if ($nombre === '' || $asunto === '' || $mensaje === '') {
                    $errores[] = 'Todos los campos son obligatorios.';
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = 'El correo electrónico no es válido.';
                }

                if (empty($errores)) {
                    // No se envía ningún correo aquí: el mensaje solo
                    // queda guardado en la base de datos, y el admin lo
                    // revisa desde /admin/mensajes.
                    $this->contactoModel->crear([
                        'nombre' => $nombre, 'email' => $email, 'asunto' => $asunto, 'mensaje' => $mensaje,
                    ]);
                    flash_success('Mensaje enviado. Le responderemos a la brevedad.');
                    redirect('/contacto');
                }
            }
        }

        require BASE_PATH . '/views/contacto/index.php';
    }
}
