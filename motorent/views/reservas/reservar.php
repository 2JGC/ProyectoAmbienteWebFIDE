<?php $tituloPagina = 'Reservar motocicleta'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-4"><i class="bi bi-calendar-check"></i> Reservar <?= e($moto['marca']) ?> <?= e($moto['modelo']) ?></h3>

                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted">Precio por día: <strong>₡<?= number_format((float) $moto['precio_dia'], 0, ',', '.') ?></strong></p>

                    <form method="post" action="<?= BASE_URL ?>/reservar?id=<?= (int) $moto['id_moto'] ?>" novalidate id="formReserva">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_moto" value="<?= (int) $moto['id_moto'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required
                                       min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                        </div>
                        <p class="mt-3 mb-0">Total estimado: <strong id="totalEstimado">₡0</strong></p>
                        <button type="submit" class="btn btn-warning w-100 mt-4">Confirmar reserva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Cálculo dinámico del total estimado en el cliente (validación/UX, no reemplaza al servidor)
    const precioDia = <?= (float) $moto['precio_dia'] ?>;
    const inicio = document.getElementById('fecha_inicio');
    const fin = document.getElementById('fecha_fin');
    const totalEl = document.getElementById('totalEstimado');

    function calcularTotal() {
        if (inicio.value && fin.value) {
            const d1 = new Date(inicio.value);
            const d2 = new Date(fin.value);
            const dias = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
            totalEl.textContent = dias > 0 ? '₡' + (dias * precioDia).toLocaleString('es-CR') : '₡0';
        }
    }
    inicio.addEventListener('change', calcularTotal);
    fin.addEventListener('change', calcularTotal);
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
