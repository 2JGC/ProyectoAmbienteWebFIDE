<?php

declare(strict_types=1);

/**
 * AdminController
 * Panel de administración: dashboard con estadísticas y gestión (CRUD) de
 * motocicletas, reservas, usuarios, mensajes de contacto y publicaciones.
 * Todas las acciones requieren rol de administrador.
 */
class AdminController
{
    private Usuario $usuarioModel;
    private Motocicleta $motoModel;
    private Reserva $reservaModel;
    private Contacto $contactoModel;
    private Publicacion $publicacionModel;

    public function __construct()
    {
        requiere_admin();
        $this->usuarioModel     = new Usuario();
        $this->motoModel        = new Motocicleta();
        $this->reservaModel     = new Reserva();
        $this->contactoModel    = new Contacto();
        $this->publicacionModel = new Publicacion();
    }

    public function dashboard(): void
    {
        $stats = [
            'total_usuarios'     => $this->usuarioModel->contarTotal(),
            'total_motos'        => $this->motoModel->contarTotal(),
            'motos_disponibles'  => $this->motoModel->contarDisponibles(),
            'total_reservas'     => $this->reservaModel->contarTotal(),
            'reservas_pendientes' => $this->reservaModel->contarPorEstado('pendiente'),
            'ingresos'           => $this->reservaModel->sumaIngresos(),
            'mensajes_no_leidos' => $this->contactoModel->contarNoLeidos(),
        ];

        require BASE_PATH . '/views/admin/dashboard.php';
    }

    // ================= MOTOCICLETAS =================

    public function motos(): void
    {
        $accion = $_GET['accion'] ?? 'listar';
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $post = $_POST['accion'] ?? '';

            if ($post === 'crear' || $post === 'editar') {
                $datos = [
                    'marca'       => trim($_POST['marca'] ?? ''),
                    'modelo'      => trim($_POST['modelo'] ?? ''),
                    'anio'        => (int) ($_POST['anio'] ?? 0),
                    'cilindraje'  => (int) ($_POST['cilindraje'] ?? 0),
                    'categoria'   => trim($_POST['categoria'] ?? ''),
                    'precio_dia'  => (float) ($_POST['precio_dia'] ?? 0),
                    'descripcion' => trim($_POST['descripcion'] ?? ''),
                    'placa'       => trim($_POST['placa'] ?? ''),
                    'estado'      => $_POST['estado'] ?? 'disponible',
                ];

                if ($datos['marca'] === '' || $datos['modelo'] === '' || $datos['placa'] === '') {
                    $errores[] = 'Marca, modelo y placa son obligatorios.';
                }

                if (empty($errores) && $post === 'crear') {
                    $this->motoModel->crear($datos);
                    flash_success('Motocicleta agregada.');
                    redirect('/admin/motos');
                } elseif (empty($errores)) {
                    $id = (int) ($_POST['id_moto'] ?? 0);
                    $this->motoModel->actualizar($id, $datos);

                    if (!empty($_FILES['imagen']['name'])) {
                        $this->subirImagenMoto($id, $errores);
                    }
                    flash_success('Motocicleta actualizada.');
                    redirect('/admin/motos');
                }
            } elseif ($post === 'eliminar') {
                $this->motoModel->eliminar((int) ($_POST['id_moto'] ?? 0));
                flash_success('Motocicleta eliminada.');
                redirect('/admin/motos');
            }
        }

        $motoEditar = null;
        if ($accion === 'editar' && isset($_GET['id'])) {
            $motoEditar = $this->motoModel->buscarPorId((int) $_GET['id']);
        }

        $motos = $this->motoModel->listarTodas();
        require BASE_PATH . '/views/admin/motos.php';
    }

    private function subirImagenMoto(int $idMoto, array &$errores): void
    {
        $permitidas = ['image/jpeg', 'image/png', 'image/webp'];
        $tipo = mime_content_type($_FILES['imagen']['tmp_name']);

        if (!in_array($tipo, $permitidas, true)) {
            $errores[] = 'Formato de imagen no permitido.';
            return;
        }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'moto_' . $idMoto . '_' . time() . '.' . $extension;
        $destino = BASE_PATH . '/public/uploads/motos/' . $nombreArchivo;

        if (!is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0755, true);
        }
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            $this->motoModel->actualizarImagen($idMoto, $nombreArchivo);
        }
    }

    // ================= RESERVAS =================

    public function reservas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $id     = (int) ($_POST['id_reserva'] ?? 0);
            $estado = $_POST['estado'] ?? '';
            $validos = ['pendiente', 'confirmada', 'en_curso', 'finalizada', 'cancelada'];

            if (in_array($estado, $validos, true)) {
                $this->reservaModel->cambiarEstado($id, $estado);
                flash_success('Estado de la reserva actualizado.');
            }
            redirect('/admin/reservas');
        }

        $reservas = $this->reservaModel->listarTodas();
        require BASE_PATH . '/views/admin/reservas.php';
    }

    // ================= USUARIOS =================

    public function usuarios(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $accion = $_POST['accion'] ?? '';
            $id = (int) ($_POST['id_usuario'] ?? 0);

            if ($accion === 'estado') {
                $this->usuarioModel->cambiarEstado($id, $_POST['estado'] ?? 'activo');
                flash_success('Estado del usuario actualizado.');
            } elseif ($accion === 'rol') {
                $this->usuarioModel->cambiarRol($id, $_POST['rol'] ?? 'cliente');
                flash_success('Rol del usuario actualizado.');
            } elseif ($accion === 'eliminar') {
                if ($id !== (int) $_SESSION['usuario_id']) {
                    $this->usuarioModel->eliminar($id);
                    flash_success('Usuario eliminado.');
                } else {
                    flash_error('No puede eliminar su propia cuenta.');
                }
            }
            redirect('/admin/usuarios');
        }

        $usuarios = $this->usuarioModel->listarTodos();
        require BASE_PATH . '/views/admin/usuarios.php';
    }

    // ================= MENSAJES DE CONTACTO =================

    public function mensajes(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $accion = $_POST['accion'] ?? '';
            $id = (int) ($_POST['id_contacto'] ?? 0);

            if ($accion === 'leido') {
                $this->contactoModel->marcarLeido($id);
            } elseif ($accion === 'eliminar') {
                $this->contactoModel->eliminar($id);
                flash_success('Mensaje eliminado.');
            }
            redirect('/admin/mensajes');
        }

        $mensajes = $this->contactoModel->listarTodos();
        require BASE_PATH . '/views/admin/mensajes.php';
    }

    // ================= PUBLICACIONES =================

    public function publicaciones(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $accion = $_POST['accion'] ?? '';
            $id = (int) ($_POST['id_publicacion'] ?? 0);

            if ($accion === 'aprobar') {
                $this->publicacionModel->cambiarEstado($id, 'aprobada');
                flash_success('Publicación aprobada.');
            } elseif ($accion === 'rechazar') {
                $this->publicacionModel->cambiarEstado($id, 'rechazada');
                flash_success('Publicación rechazada.');
            } elseif ($accion === 'eliminar') {
                $this->publicacionModel->eliminar($id);
                flash_success('Publicación eliminada.');
            }
            redirect('/admin/publicaciones');
        }

        $publicaciones = $this->publicacionModel->listarTodas();
        require BASE_PATH . '/views/admin/publicaciones.php';
    }
}
