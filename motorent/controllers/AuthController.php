<?php

declare(strict_types=1);

/**
 * AuthController
 * Maneja registro, inicio/cierre de sesión y recuperación de contraseña
 * mediante token temporal.
 */
class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /** Formulario de registro (GET) y procesamiento (POST). */
    public function register(): void
    {
        $errores = [];

        // Si la petición es GET, simplemente mostramos el formulario vacío
        // (el require de abajo, fuera del if). Solo procesamos datos
        // cuando el formulario se envía por POST.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $nombre    = trim($_POST['nombre'] ?? '');
                $apellidos = trim($_POST['apellidos'] ?? '');
                $email     = trim($_POST['email'] ?? '');
                $password  = $_POST['password'] ?? '';
                $password2 = $_POST['password2'] ?? '';
                $telefono  = trim($_POST['telefono'] ?? '');
                $cedula    = trim($_POST['cedula'] ?? '');

                // ---- Validación del lado servidor ----
                // Aunque el formulario ya valida en JavaScript, siempre hay
                // que revisar los datos también en el servidor: el usuario
                // podría desactivar JavaScript o mandar la petición sin
                // pasar por el formulario.
                if ($nombre === '' || $apellidos === '') {
                    $errores[] = 'El nombre y los apellidos son obligatorios.';
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = 'El correo electrónico no es válido.';
                }
                if (strlen($password) < 8) {
                    $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
                }
                if ($password !== $password2) {
                    $errores[] = 'Las contraseñas no coinciden.';
                }
                if (empty($errores) && $this->usuarioModel->buscarPorEmail($email)) {
                    $errores[] = 'Ya existe una cuenta registrada con ese correo.';
                }

                if (empty($errores)) {
                    // Nunca guardamos la contraseña tal cual la escribió el
                    // usuario: la "hasheamos" con bcrypt, que es una función
                    // de un solo sentido (no se puede revertir el hash para
                    // obtener la contraseña original).
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $creado = $this->usuarioModel->crear([
                        'nombre'    => $nombre,
                        'apellidos' => $apellidos,
                        'email'     => $email,
                        'password'  => $hash,
                        'telefono'  => $telefono,
                        'cedula'    => $cedula,
                        'rol'       => 'cliente',
                    ]);

                    if ($creado) {
                        flash_success('Cuenta creada correctamente. Ya puede iniciar sesión.');
                        redirect('/login');
                    }
                    $errores[] = 'Ocurrió un error al crear la cuenta. Intente nuevamente.';
                }
            }
        }

        require BASE_PATH . '/views/auth/register.php';
    }

    /** Formulario de login (GET) y procesamiento (POST). */
    public function login(): void
    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $email    = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';

                $usuario = $this->usuarioModel->buscarPorEmail($email);

                // password_verify() compara la contraseña en texto plano
                // que escribió el usuario contra el hash guardado en la
                // base de datos. Nunca comparamos contraseñas directamente.
                if ($usuario && password_verify($password, $usuario['password'])) {
                    if ($usuario['estado'] !== 'activo') {
                        $errores[] = 'Su cuenta se encuentra inactiva. Contacte al administrador.';
                    } else {
                        // Regeneramos el id de sesión al iniciar sesión por
                        // seguridad: evita que alguien "robe" una sesión
                        // anterior a este mismo id (session fixation).
                        session_regenerate_id(true);
                        $_SESSION['usuario_id']     = $usuario['id_usuario'];
                        $_SESSION['usuario_nombre'] = $usuario['nombre'];
                        $_SESSION['usuario_rol']    = $usuario['rol'];

                        // Si es administrador lo mandamos directo al panel;
                        // si es cliente, a la página de inicio normal.
                        redirect($usuario['rol'] === 'administrador' ? '/admin/dashboard' : '/');
                    }
                } else {
                    // A propósito usamos el mismo mensaje de error tanto si
                    // el correo no existe como si la contraseña está mal,
                    // para no darle pistas a alguien que intente adivinar
                    // cuentas válidas.
                    $errores[] = 'Correo o contraseña incorrectos.';
                }
            }
        }

        require BASE_PATH . '/views/auth/login.php';
    }

    public function logout(): void
    {
        // Vaciamos y destruimos la sesión completa: así se borran también
        // los mensajes flash y cualquier otro dato guardado, no solo el
        // usuario_id.
        $_SESSION = [];
        session_destroy();
        redirect('/login');
    }

    /** Formulario para solicitar el enlace de recuperación. */
    public function forgotPassword(): void
    {
        $errores = [];
        $mensajeExito = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $email = trim($_POST['email'] ?? '');
                $usuario = $this->usuarioModel->buscarPorEmail($email);

                // Por seguridad, no revelamos si el correo existe o no.
                if ($usuario) {
                    // Generamos un token aleatorio único que va a viajar
                    // dentro del enlace del correo. Nadie más que quien
                    // recibe el correo debería conocer este valor.
                    $token = bin2hex(random_bytes(32));
                    $resetModel = new PasswordReset();
                    $resetModel->crear($email, $token);

                    $enlace = site_url('/reset-password?token=' . $token);
                    $cuerpo = '<p>Hola ' . e($usuario['nombre']) . ',</p>'
                        . '<p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en MotoRent Costa Rica.</p>'
                        . '<p><a href="' . $enlace . '">Haz clic aquí para crear una nueva contraseña</a></p>'
                        . '<p>Este enlace vence en 30 minutos. Si no solicitaste este cambio, puedes ignorar este correo.</p>';

                    Mailer::enviar($email, 'Recuperación de contraseña - MotoRent', $cuerpo);
                }

                // Este mensaje se muestra exista o no la cuenta, para no
                // darle a un atacante una forma de averiguar qué correos
                // están registrados en el sistema.
                $mensajeExito = 'Si el correo existe en nuestro sistema, recibirá instrucciones de recuperación.';
            }
        }

        require BASE_PATH . '/views/auth/forgot.php';
    }

    /** Formulario para definir la nueva contraseña a partir del token. */
    public function resetPassword(): void
    {
        $errores = [];
        // El token puede venir en la URL (cuando el usuario hace clic en
        // el enlace del correo, es GET) o en el POST (cuando reenvía el
        // formulario con la nueva contraseña).
        $token = trim($_GET['token'] ?? $_POST['token'] ?? '');
        $resetModel = new PasswordReset();
        $registro = $token !== '' ? $resetModel->buscarTokenValido($token) : false;

        if (!$registro) {
            $errores[] = 'El enlace de recuperación no es válido o ha expirado.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $registro) {
            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $password  = $_POST['password'] ?? '';
                $password2 = $_POST['password2'] ?? '';

                if (strlen($password) < 8) {
                    $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
                }
                if ($password !== $password2) {
                    $errores[] = 'Las contraseñas no coinciden.';
                }

                if (empty($errores)) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $this->usuarioModel->actualizarPasswordPorEmail($registro['email'], $hash);
                    // Una vez usado el token, lo invalidamos para que ese
                    // mismo enlace no sirva una segunda vez.
                    $resetModel->marcarUsado((int) $registro['id_reset']);
                    flash_success('Contraseña actualizada. Ya puede iniciar sesión.');
                    redirect('/login');
                }
            }
        }

        require BASE_PATH . '/views/auth/reset.php';
    }
}
