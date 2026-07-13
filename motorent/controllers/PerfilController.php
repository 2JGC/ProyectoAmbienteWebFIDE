<?php

declare(strict_types=1);

/**
 * PerfilController
 * Permite al usuario autenticado ver y actualizar sus datos personales,
 * su foto de perfil y su contraseña.
 */
class PerfilController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function index(): void
    {
        requiere_login();

        $usuario = $this->usuarioModel->buscarPorId((int) $_SESSION['usuario_id']);
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $accion = $_POST['accion'] ?? '';

                if ($accion === 'datos') {
                    $this->actualizarDatos($errores);
                } elseif ($accion === 'password') {
                    $this->actualizarPassword($usuario, $errores);
                } elseif ($accion === 'foto') {
                    $this->actualizarFoto($errores);
                }

                $usuario = $this->usuarioModel->buscarPorId((int) $_SESSION['usuario_id']);
            }
        }

        require BASE_PATH . '/views/perfil/index.php';
    }

    private function actualizarDatos(array &$errores): void
    {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');
        $cedula    = trim($_POST['cedula'] ?? '');

        if ($nombre === '' || $apellidos === '') {
            $errores[] = 'El nombre y los apellidos son obligatorios.';
            return;
        }

        $this->usuarioModel->actualizarPerfil((int) $_SESSION['usuario_id'], [
            'nombre' => $nombre, 'apellidos' => $apellidos, 'telefono' => $telefono, 'cedula' => $cedula,
        ]);
        $_SESSION['usuario_nombre'] = $nombre;
        flash_success('Datos de perfil actualizados.');
        redirect('/perfil');
    }

    private function actualizarPassword(array $usuario, array &$errores): void
    {
        $actual    = $_POST['password_actual'] ?? '';
        $nueva     = $_POST['password_nueva'] ?? '';
        $confirmar = $_POST['password_confirmar'] ?? '';

        if (!password_verify($actual, $usuario['password'])) {
            $errores[] = 'La contraseña actual no es correcta.';
            return;
        }
        if (strlen($nueva) < 8) {
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            return;
        }
        if ($nueva !== $confirmar) {
            $errores[] = 'Las contraseñas no coinciden.';
            return;
        }

        $this->usuarioModel->actualizarPassword((int) $_SESSION['usuario_id'], password_hash($nueva, PASSWORD_BCRYPT));
        flash_success('Contraseña actualizada correctamente.');
        redirect('/perfil');
    }

    private function actualizarFoto(array &$errores): void
    {
        if (empty($_FILES['foto']['name'])) {
            $errores[] = 'Debe seleccionar una imagen.';
            return;
        }

        $permitidas = ['image/jpeg', 'image/png', 'image/webp'];
        $tipo = mime_content_type($_FILES['foto']['tmp_name']);

        if (!in_array($tipo, $permitidas, true)) {
            $errores[] = 'Formato de imagen no permitido (use JPG, PNG o WEBP).';
            return;
        }
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $errores[] = 'La imagen no debe superar los 2MB.';
            return;
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'perfil_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $extension;
        $destino = BASE_PATH . '/public/uploads/perfiles/' . $nombreArchivo;

        if (!is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0755, true);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            $this->usuarioModel->actualizarFoto((int) $_SESSION['usuario_id'], $nombreArchivo);
            flash_success('Foto de perfil actualizada.');
            redirect('/perfil');
        }

        $errores[] = 'No fue posible subir la imagen.';
    }
}
