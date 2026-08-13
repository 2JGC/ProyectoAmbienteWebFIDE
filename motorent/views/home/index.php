<?php $tituloPagina = 'Inicio'; require BASE_PATH . '/views/layouts/header.php'; ?>

<section class="hero-section text-white text-center d-flex align-items-center">
    <div class="container py-5">
        <h1 class="display-4 fw-bold">Recorré Costa Rica sobre dos ruedas</h1>
        <p class="lead">Alquiler de motocicletas confiable, fácil y seguro para tu próxima aventura.</p>
        <a href="<?= BASE_URL ?>/catalogo" class="btn btn-warning btn-lg mt-3">
            <i class="bi bi-search"></i> Ver catálogo
        </a>
    </div>
</section>

<section class="container py-5">
    <h2 class="text-center mb-4">Motocicletas destacadas</h2>
    <div class="row g-4">
        <?php if (empty($destacadas)): ?>
            <p class="text-center text-muted">Aún no hay motocicletas disponibles.</p>
        <?php endif; ?>
        <?php foreach ($destacadas as $moto): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?= e($moto['imagen']) ?>"
                         onerror="this.src='<?= BASE_URL ?>/assets/img/default-moto.jpg'"
                         class="card-img-top" alt="<?= e($moto['marca'] . ' ' . $moto['modelo']) ?>" style="height:200px;object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= e($moto['marca']) ?> <?= e($moto['modelo']) ?></h5>
                        <p class="card-text text-muted"><?= e($moto['categoria']) ?> · <?= e((string) $moto['cilindraje']) ?>cc</p>
                        <p class="fw-bold text-success">₡<?= number_format((float) $moto['precio_dia'], 0, ',', '.') ?> / día</p>
                        <a href="<?= BASE_URL ?>/moto?id=<?= (int) $moto['id_moto'] ?>" class="btn btn-outline-dark w-100">Ver detalle</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="bg-light py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <i class="bi bi-shield-check display-5 text-warning"></i>
                <h5 class="mt-3">Reservas seguras</h5>
                <p class="text-muted">Confirmación y seguimiento de cada reserva desde tu perfil.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-cash-coin display-5 text-warning"></i>
                <h5 class="mt-3">Precios claros</h5>
                <p class="text-muted">Tarifas por día sin cargos ocultos.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-people display-5 text-warning"></i>
                <h5 class="mt-3">Comunidad activa</h5>
                <p class="text-muted">Compartí tus rutas en nuestra galería comunitaria.</p>
            </div>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
