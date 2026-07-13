<?php $tituloPagina = $moto['marca'] . ' ' . $moto['modelo']; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-6">
            <img src="<?= BASE_URL ?>/uploads/motos/<?= e($moto['imagen']) ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/img/default-moto.jpg'"
                 class="img-fluid rounded shadow-sm" alt="<?= e($moto['marca']) ?>">
        </div>
        <div class="col-md-6">
            <h1><?= e($moto['marca']) ?> <?= e($moto['modelo']) ?></h1>
            <span class="badge <?= $moto['estado'] === 'disponible' ? 'bg-success' : 'bg-secondary' ?> mb-2">
                <?= e(ucfirst($moto['estado'])) ?>
            </span>
            <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item"><strong>Año:</strong> <?= e((string) $moto['anio']) ?></li>
                <li class="list-group-item"><strong>Cilindraje:</strong> <?= e((string) $moto['cilindraje']) ?>cc</li>
                <li class="list-group-item"><strong>Categoría:</strong> <?= e($moto['categoria']) ?></li>
                <li class="list-group-item"><strong>Placa:</strong> <?= e($moto['placa']) ?></li>
            </ul>
            <p><?= nl2br(e($moto['descripcion'] ?? 'Sin descripción disponible.')) ?></p>
            <p class="fs-4 fw-bold text-success">₡<?= number_format((float) $moto['precio_dia'], 0, ',', '.') ?> / día</p>

            <?php if ($moto['estado'] === 'disponible'): ?>
                <a href="<?= BASE_URL ?>/reservar?id=<?= (int) $moto['id_moto'] ?>" class="btn btn-warning btn-lg">
                    <i class="bi bi-calendar-check"></i> Reservar ahora
                </a>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>No disponible actualmente</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
