<?php $tituloPagina = 'Confirmar reserva'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-4"><i class="bi bi-clipboard-check"></i> Resumen de tu reserva</h3>

                    <ul class="list-group list-group-flush mb-4">
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
                            <span>Días</span>
                            <strong><?= e((string) $draft['total_dias']) ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total a pagar</span>
                            <strong>₡<?= number_format((float) $draft['total_pago'], 0, ',', '.') ?></strong>
                        </li>
                    </ul>

                    <h5 class="mb-2">Datos del cliente</h5>
                    <p class="text-muted mb-4"><?= e($_SESSION['usuario_nombre']) ?></p>

                    <form method="post" action="<?= BASE_URL ?>/confirmacion">
                        <?= csrf_field() ?>
                        <div class="form-check my-3">
                            <input class="form-check-input" type="checkbox" name="terminos" id="terminos" value="1" required>
                            <label class="form-check-label" for="terminos">
                                Acepto los <a href="#" target="_blank">términos y condiciones</a> del alquiler.
                            </label>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Confirmar reserva</button>
                        <a href="<?= BASE_URL ?>/reservar?id=<?= (int) $moto['id_moto'] ?>" class="btn btn-link w-100 mt-2">Volver a elegir fechas</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
