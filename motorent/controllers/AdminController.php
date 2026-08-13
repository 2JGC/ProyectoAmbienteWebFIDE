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
        // Esta línea protege TODO el panel: se ejecuta antes de cualquier
        // método de este controlador, así que ninguna acción de admin se
        // puede usar sin haber iniciado sesión como administrador.
        requiere_admin();
        $this->usuarioModel     = new Usuario();
        $this->motoModel        = new Motocicleta();
        $this->reservaModel     = new Reserva();
        $this->contactoModel    = new Contacto();
        $this->publicacionModel = new Publicacion();
    }

    public function dashboard(): void
    {
        // Todas las tarjetas de resumen que ve el admin al entrar al
        // panel salen de aquí: cuántos usuarios hay, cuántas motos,
        // cuántas reservas pendientes, cuánto se ha ingresado, etc.
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
        // $accion viene por GET y decide qué mostrar (listado o
        // formulario de edición); $post (más abajo) viene por POST y
        // decide qué acción ejecutar (crear, editar o eliminar).
        $accion = $_GET['accion'] ?? 'listar';
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $post = $_POST['accion'] ?? '';

            if ($post === 'crear' || $post === 'editar') {
                // Armamos un solo array de datos porque crear() y
                // actualizar() esperan la misma estructura; así no
                // repetimos el código de armar el array dos veces.
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

                    // La imagen se sube solo si el admin seleccionó un
                    // archivo nuevo; si no, se conserva la que ya tenía.
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

        // Si venimos a editar una moto (?accion=editar&id=5), cargamos
        // sus datos actuales para precargar el formulario.
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
        // Incluimos el id de la moto y un timestamp en el nombre del
        // archivo para que quede claro a qué moto pertenece y no se
        // repita con otra imagen subida antes.
        $nombreArchivo = 'moto_' . $idMoto . '_' . time() . '.' . $extension;
        $url = FirebaseStorage::subir($_FILES['imagen']['tmp_name'], 'motos', $nombreArchivo, $tipo);

        if ($url !== null) {
            $this->motoModel->actualizarImagen($idMoto, $url);
        } else {
            $errores[] = 'No fue posible subir la imagen.';
        }
    }

    // ================= RESERVAS =================

    public function reservas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $id     = (int) ($_POST['id_reserva'] ?? 0);
            $estado = $_POST['estado'] ?? '';
            // Solo aceptamos uno de estos 5 estados exactos, para que
            // nadie pueda mandar un valor inventado por POST y dejar la
            // reserva en un estado que no existe en la base de datos.
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
                // Evitamos que un administrador se elimine a sí mismo por
                // error (o con mala intención) y se quede sin poder
                // volver a entrar al panel.
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

            // Aquí es donde el admin modera lo que suben los usuarios a
            // la galería comunitaria: aprobar, rechazar o eliminar.
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
