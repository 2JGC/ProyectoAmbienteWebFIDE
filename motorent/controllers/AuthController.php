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

                if ($usuario && password_verify($password, $usuario['password'])) {
                    if ($usuario['estado'] !== 'activo') {
                        $errores[] = 'Su cuenta se encuentra inactiva. Contacte al administrador.';
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['usuario_id']     = $usuario['id_usuario'];
                        $_SESSION['usuario_nombre'] = $usuario['nombre'];
                        $_SESSION['usuario_rol']    = $usuario['rol'];

                        redirect($usuario['rol'] === 'administrador' ? '/admin/dashboard' : '/');
                    }
                } else {
                    $errores[] = 'Correo o contraseña incorrectos.';
                }
            }
        }

        require BASE_PATH . '/views/auth/login.php';
    }

    public function logout(): void
    {
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
                    $token = bin2hex(random_bytes(32));
                    $resetModel = new PasswordReset();
                    $resetModel->crear($email, $token);

                    // En un entorno real aquí se enviaría el correo con PHPMailer.
                    // Se muestra el enlace en pantalla únicamente para efectos de práctica académica.
                    $mensajeExito = 'Si el correo existe, se generó un enlace de recuperación: ' .
                        BASE_URL . '/reset-password?token=' . $token;
                } else {
                    $mensajeExito = 'Si el correo existe en nuestro sistema, recibirá instrucciones de recuperación.';
                }
            }
        }

        require BASE_PATH . '/views/auth/forgot.php';
    }

    /** Formulario para definir la nueva contraseña a partir del token. */
    public function resetPassword(): void
    {
        $errores = [];
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
                    $resetModel->marcarUsado((int) $registro['id_reset']);
                    flash_success('Contraseña actualizada. Ya puede iniciar sesión.');
                    redirect('/login');
                }
            }
        }

        require BASE_PATH . '/views/auth/reset.php';
    }
}
