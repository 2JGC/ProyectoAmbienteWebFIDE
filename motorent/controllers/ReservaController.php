<?php

declare(strict_types=1);

/**
 * ReservaController
 * Permite a un cliente autenticado reservar una motocicleta y consultar
 * su historial de reservas.
 */
class ReservaController
{
    private Reserva $reservaModel;
    private Motocicleta $motoModel;

    public function __construct()
    {
        $this->reservaModel = new Reserva();
        $this->motoModel    = new Motocicleta();
    }

    /** Formulario de reserva (GET) y procesamiento (POST). */
    public function reservar(): void
    {
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
                $dInicio = DateTime::createFromFormat('Y-m-d', $fechaInicio) ?: null;
                $dFin    = DateTime::createFromFormat('Y-m-d', $fechaFin) ?: null;

                if (!$dInicio || !$dFin) {
                    $errores[] = 'Las fechas ingresadas no son válidas.';
                } elseif ($dInicio < $hoy) {
                    $errores[] = 'La fecha de inicio no puede ser anterior a hoy.';
                } elseif ($dFin <= $dInicio) {
                    $errores[] = 'La fecha de fin debe ser posterior a la fecha de inicio.';
                } elseif ($moto['estado'] !== 'disponible') {
                    $errores[] = 'Esta motocicleta no está disponible actualmente.';
                } elseif ($this->reservaModel->existeSolapamiento($idMoto, $fechaInicio, $fechaFin)) {
                    $errores[] = 'La motocicleta ya está reservada en ese rango de fechas.';
                }

                if (empty($errores)) {
                    $dias  = (int) $dInicio->diff($dFin)->days;
                    $total = $dias * (float) $moto['precio_dia'];

                    $this->reservaModel->crear([
                        'id_usuario'    => $_SESSION['usuario_id'],
                        'id_moto'       => $idMoto,
                        'fecha_inicio'  => $fechaInicio,
                        'fecha_fin'     => $fechaFin,
                        'total_dias'    => $dias,
                        'total_pago'    => $total,
                        'estado'        => 'pendiente',
                    ]);

                    flash_success('Reserva creada correctamente. Quedará pendiente de confirmación.');
                    redirect('/historial');
                }
            }
        }

        require BASE_PATH . '/views/reservas/reservar.php';
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
