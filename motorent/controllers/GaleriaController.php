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
            // Ver la galería es público, pero publicar en ella requiere
            // haber iniciado sesión.
            requiere_login();

            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $titulo       = trim($_POST['titulo'] ?? '');
                $descripcion  = trim($_POST['descripcion'] ?? '');
                // La calificación es la cantidad de estrellas (1 a 5) que
                // el usuario le da a su experiencia con la moto alquilada.
                $calificacion = (int) ($_POST['calificacion'] ?? 0);

                if ($titulo === '') {
                    $errores[] = 'El título es obligatorio.';
                }
                if ($calificacion < 1 || $calificacion > 5) {
                    $errores[] = 'Selecciona una calificación entre 1 y 5 estrellas.';
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
                        // uniqid() nos da un nombre de archivo distinto
                        // cada vez, para que dos publicaciones con
                        // "foto.jpg" no se sobrescriban entre sí.
                        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                        $nombreArchivo = 'galeria_' . uniqid() . '.' . $extension;
                        $url = FirebaseStorage::subir($_FILES['imagen']['tmp_name'], 'galeria', $nombreArchivo, $tipo);

                        if ($url !== null) {
                            // La publicación se crea, pero no aparece de
                            // inmediato en la galería pública: queda
                            // "pendiente" hasta que un admin la apruebe
                            // (ver Publicacion::crear()).
                            $this->publicacionModel->crear([
                                'id_usuario'   => $_SESSION['usuario_id'],
                                'titulo'       => $titulo,
                                'descripcion'  => $descripcion,
                                'imagen'       => $url,
                                'calificacion' => $calificacion,
                            ]);
                            flash_success('Publicación enviada. Quedará visible tras ser aprobada.');
                            redirect('/galeria');
                        }
                        $errores[] = 'No fue posible subir la imagen.';
                    }
                }
            }
        }

        // Al público en general (haya iniciado sesión o no) solo se le
        // muestran las publicaciones ya aprobadas.
        $publicaciones = $this->publicacionModel->listarAprobadas();
        require BASE_PATH . '/views/galeria/index.php';
    }
}
