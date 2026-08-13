<?php $tituloPagina = 'Reserva confirmada'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 text-center">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
                    <h3 class="mt-3 mb-4">¡Reserva registrada!</h3>

                    <ul class="list-group list-group-flush mb-4 text-start">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Motocicleta</span>
                            <strong><?= e($moto['marca']) ?> <?= e($moto['modelo']) ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Fecha de inicio</span>
                            <strong><?= e($draft['fecha_inicio']) ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Fecha de fin</span>
                            <strong><?= e($draft['fecha_fin']) ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total</span>
                            <strong>₡<?= number_format((float) $draft['total_pago'], 0, ',', '.') ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Estado</span>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        </li>
                    </ul>

                    <p class="text-muted">Te enviamos un correo con el detalle de tu reserva.</p>
                    <a href="<?= BASE_URL ?>/historial" class="btn btn-warning">Ver mis reservas</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
