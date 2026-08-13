<?php

declare(strict_types=1);

/**
 * ReservaController
 * Permite a un cliente autenticado reservar una motocicleta y consultar
 * su historial de reservas.
 *
 * El flujo de reserva va en tres pasos, cada uno con su propia ruta:
 *   1) reservar()     -> elegir moto y fechas, validar disponibilidad.
 *   2) checkout()      -> mostrar resumen y pedir aceptar términos.
 *   3) confirmacion()  -> crear la reserva de verdad y enviar el correo.
 * Mientras el cliente pasa del paso 1 al 3, los datos de la reserva
 * "en construcción" se guardan temporalmente en la sesión, en
 * $_SESSION['reserva_draft']. Todavía no existe en la base de datos.
 */
class ReservaController
{
    private Reserva $reservaModel;
    private Motocicleta $motoModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->reservaModel = new Reserva();
        $this->motoModel    = new Motocicleta();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Paso 1: formulario de selección de fechas (GET) y validación de
     * disponibilidad (POST). Si todo es válido, guarda un borrador en
     * sesión y continúa hacia /checkout (no crea la reserva todavía).
     */
    public function reservar(): void
    {
        // Nadie puede reservar sin haber iniciado sesión primero.
        requiere_login();

        $idMoto = (int) ($_GET['id'] ?? $_POST['id_moto'] ?? 0);
        $moto = $this->motoModel->buscarPorId($idMoto);
        $errores = [];

        if (!$moto) {
            flash_error('La motocicleta solicitada no existe.');
            redirect('/catalogo');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify()) {
                $errores[] = 'Token de seguridad inválido. Intente de nuevo.';
            } else {
                $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
                $fechaFin    = trim($_POST['fecha_fin'] ?? '');

                $hoy = new DateTime('today');
                // createFromFormat() devuelve false si el texto no tiene
                // el formato de fecha esperado; con el "?: null" nos
                // aseguramos de trabajar siempre con DateTime o null,
                // nunca con un false suelto.
                $dInicio = DateTime::createFromFormat('Y-m-d', $fechaInicio) ?: null;
                $dFin    = DateTime::createFromFormat('Y-m-d', $fechaFin) ?: null;

                // Vamos revisando las validaciones en orden: primero que
                // las fechas tengan sentido, después que la moto siga
                // disponible, y por último que nadie más la haya
                // reservado ya para esas mismas fechas.
                //
                // El fin puede ser el MISMO día que el inicio (eso es una
                // renta de 1 día, por ejemplo alquilar la moto solo el
                // día 13). Solo es un error si el fin queda ANTES del
                // inicio.
                if (!$dInicio || !$dFin) {
                    $errores[] = 'Las fechas ingresadas no son válidas.';
                } elseif ($dInicio < $hoy) {
                    $errores[] = 'La fecha de inicio no puede ser anterior a hoy.';
                } elseif ($dFin < $dInicio) {
                    $errores[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
                } elseif ($moto['estado'] !== 'disponible') {
                    $errores[] = 'Esta motocicleta no está disponible actualmente.';
                } elseif ($this->reservaModel->existeSolapamiento($idMoto, $fechaInicio, $fechaFin)) {
                    $errores[] = 'La motocicleta ya está reservada en ese rango de fechas.';
                }

                if (empty($errores)) {
                    // El total se calcula contando los días de alquiler
                    // de forma inclusiva (cuenta tanto el día de inicio
                    // como el de fin) y multiplicando por el precio
                    // diario. Por eso se suma 1: si inicio y fin son el
                    // mismo día, ->diff() da 0, pero en realidad es 1 día
                    // de alquiler.
                    $dias  = (int) $dInicio->diff($dFin)->days + 1;
                    $total = $dias * (float) $moto['precio_dia'];

                    // Guardamos el "borrador" en sesión en vez de crear la
                    // reserva de una vez: todavía falta que el cliente vea
                    // el resumen y acepte los términos en el paso 2.
                    $_SESSION['reserva_draft'] = [
                        'id_moto'      => $idMoto,
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin'    => $fechaFin,
                        'total_dias'   => $dias,
                        'total_pago'   => $total,
                    ];

                    redirect('/checkout');
                }
            }
        }

        require BASE_PATH . '/views/reservas/reservar.php';
    }

    /**
     * Paso 2: resumen de la reserva, datos del cliente y aceptación de
     * términos, a partir del borrador guardado en sesión por reservar().
     */
    public function checkout(): void
    {
        requiere_login();

        // Si alguien entra directo a /checkout sin haber pasado antes por
        // /reservar, no hay ningún borrador guardado y no tenemos nada
        // que mostrarle, así que lo mandamos al catálogo a empezar de cero.
        $draft = $_SESSION['reserva_draft'] ?? null;
        if (!$draft) {
            flash_error('Primero seleccione una motocicleta y las fechas de la reserva.');
            redirect('/catalogo');
        }

        $moto = $this->motoModel->buscarPorId((int) $draft['id_moto']);
        if (!$moto) {
            unset($_SESSION['reserva_draft']);
            flash_error('La motocicleta solicitada no existe.');
            redirect('/catalogo');
        }

        require BASE_PATH . '/views/reservas/checkout.php';
    }

    /**
     * Paso 3: crea la reserva definitiva a partir del borrador, envía el
     * correo de confirmación al cliente y muestra el resumen final.
     */
    public function confirmacion(): void
    {
        requiere_login();

        // Esta acción solo tiene sentido como resultado de enviar el
        // formulario de /checkout. Si alguien entra por GET (por ejemplo
        // pegando la URL directamente), no hay nada que confirmar.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/catalogo');
        }

        $draft = $_SESSION['reserva_draft'] ?? null;
        if (!$draft) {
            flash_error('Su borrador de reserva expiró. Intente nuevamente.');
            redirect('/catalogo');
        }

        if (!csrf_verify()) {
            flash_error('Token de seguridad inválido. Intente de nuevo.');
            redirect('/checkout');
        }

        if (empty($_POST['terminos'])) {
            flash_error('Debe aceptar los términos y condiciones para confirmar la reserva.');
            redirect('/checkout');
        }

        // Volvemos a comprobar disponibilidad justo antes de guardar,
        // porque pudo pasar tiempo entre el paso 1 y este paso 3, y
        // alguien más pudo haber reservado la misma moto mientras tanto.
        $moto = $this->motoModel->buscarPorId((int) $draft['id_moto']);
        if (!$moto
            || $moto['estado'] !== 'disponible'
            || $this->reservaModel->existeSolapamiento((int) $draft['id_moto'], $draft['fecha_inicio'], $draft['fecha_fin'])
        ) {
            unset($_SESSION['reserva_draft']);
            flash_error('La motocicleta ya no está disponible para esas fechas. Intente con otra selección.');
            redirect('/catalogo');
        }

        // Recién aquí, en el paso final, se guarda la reserva en la base
        // de datos.
        $this->reservaModel->crear([
            'id_usuario'   => $_SESSION['usuario_id'],
            'id_moto'      => $draft['id_moto'],
            'fecha_inicio' => $draft['fecha_inicio'],
            'fecha_fin'    => $draft['fecha_fin'],
            'total_dias'   => $draft['total_dias'],
            'total_pago'   => $draft['total_pago'],
            'estado'       => 'pendiente',
        ]);

        // Le mandamos un correo al cliente con el resumen de su reserva.
        // Si el envío falla (por ejemplo, sin conexión a internet), no
        // interrumpimos el proceso: la reserva ya quedó guardada.
        $usuario = $this->usuarioModel->buscarPorId((int) $_SESSION['usuario_id']);
        if ($usuario) {
            $cuerpo = '<p>Hola ' . e($usuario['nombre']) . ',</p>'
                . '<p>Tu reserva en MotoRent Costa Rica fue registrada con el siguiente detalle:</p>'
                . '<ul>'
                . '<li>Motocicleta: ' . e($moto['marca']) . ' ' . e($moto['modelo']) . '</li>'
                . '<li>Fecha de inicio: ' . e($draft['fecha_inicio']) . '</li>'
                . '<li>Fecha de fin: ' . e($draft['fecha_fin']) . '</li>'
                . '<li>Días: ' . e((string) $draft['total_dias']) . '</li>'
                . '<li>Total: ₡' . number_format((float) $draft['total_pago'], 0, ',', '.') . '</li>'
                . '<li>Estado: Pendiente de confirmación</li>'
                . '</ul>'
                . '<p>Puedes revisar el estado de tu reserva en cualquier momento desde tu historial.</p>';

            Mailer::enviar($usuario['email'], 'Confirmación de reserva - MotoRent', $cuerpo);
        }

        // Limpiamos el borrador: ya cumplió su propósito y no debe
        // quedar "flotando" en la sesión para la próxima reserva.
        unset($_SESSION['reserva_draft']);
        require BASE_PATH . '/views/reservas/confirmacion.php';
    }

    public function historial(): void
    {
        requiere_login();
        $reservas = $this->reservaModel->listarPorUsuario((int) $_SESSION['usuario_id']);
        require BASE_PATH . '/views/reservas/historial.php';
    }

    /** Permite al cliente cancelar una reserva propia que siga pendiente/confirmada. */
    public function cancelar(): void
    {
        requiere_login();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
            $id = (int) ($_POST['id_reserva'] ?? 0);
            $reserva = $this->reservaModel->buscarPorId($id);

            // Tres condiciones deben cumplirse para poder cancelar:
            // que la reserva exista, que sea del usuario que la está
            // pidiendo cancelar (no la de otra persona), y que todavía
            // esté en un estado que tenga sentido cancelar.
            if ($reserva && (int) $reserva['id_usuario'] === (int) $_SESSION['usuario_id']
                && in_array($reserva['estado'], ['pendiente', 'confirmada'], true)) {
                $this->reservaModel->cambiarEstado($id, 'cancelada');
                flash_success('Reserva cancelada.');
            } else {
                flash_error('No fue posible cancelar la reserva.');
            }
        }

        redirect('/historial');
    }
}
