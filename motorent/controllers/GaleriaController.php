<?php

declare(strict_types=1);

/**
 * GaleriaController
 * Muestra publicaciones aprobadas y permite a un usuario autenticado
 * subir una nueva publicación (queda pendiente de aprobación).
 */
class GaleriaController
{
    private Publicacion $publicacionModel;

    public function __construct()
    {
        $this->publicacionModel = new Publicacion();
    }

    public function index(): void
    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requiere_login();

            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $titulo      = trim($_POST['titulo'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');

                if ($titulo === '') {
                    $errores[] = 'El título es obligatorio.';
                }
                if (empty($_FILES['imagen']['name'])) {
                    $errores[] = 'Debe adjuntar una imagen.';
                }

                if (empty($errores)) {
                    $permitidas = ['image/jpeg', 'image/png', 'image/webp'];
                    $tipo = mime_content_type($_FILES['imagen']['tmp_name']);

                    if (!in_array($tipo, $permitidas, true)) {
                        $errores[] = 'Formato de imagen no permitido.';
                    } elseif ($_FILES['imagen']['size'] > 3 * 1024 * 1024) {
                        $errores[] = 'La imagen no debe superar los 3MB.';
                    } else {
                        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                        $nombreArchivo = 'galeria_' . uniqid() . '.' . $extension;
                        $destino = BASE_PATH . '/public/uploads/galeria/' . $nombreArchivo;

                        if (!is_dir(dirname($destino))) {
                            mkdir(dirname($destino), 0755, true);
                        }

                        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                            $this->publicacionModel->crear([
                                'id_usuario'  => $_SESSION['usuario_id'],
                                'titulo'      => $titulo,
                                'descripcion' => $descripcion,
                                'imagen'      => $nombreArchivo,
                            ]);
                            flash_success('Publicación enviada. Quedará visible tras ser aprobada.');
                            redirect('/galeria');
                        }
                        $errores[] = 'No fue posible subir la imagen.';
                    }
                }
            }
        }

        $publicaciones = $this->publicacionModel->listarAprobadas();
        require BASE_PATH . '/views/galeria/index.php';
    }
}
