<?php $tituloPagina = 'Dashboard'; require BASE_PATH . '/views/layouts/admin_header.php'; ?>

<h1 class="mb-4">Dashboard</h1>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card text-white bg-dark shadow-sm">
            <div class="card-body">
                <i class="bi bi-people fs-2"></i>
                <h3 class="mt-2"><?= (int) $stats['total_usuarios'] ?></h3>
                <p class="mb-0">Usuarios registrados</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning shadow-sm">
            <div class="card-body">
                <span class="fs-2">Motos</span>
                <h3 class="mt-2"><?= (int) $stats['total_motos'] ?></h3>
                <p class="mb-0"><?= (int) $stats['motos_disponibles'] ?> disponibles</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <span class="fs-2">Reservas</span>
                <h3 class="mt-2"><?= (int) $stats['total_reservas'] ?></h3>
                <p class="mb-0"><?= (int) $stats['reservas_pendientes'] ?> pendientes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <i class="bi bi-cash-coin fs-2"></i>
                <h3 class="mt-2">₡<?= number_format($stats['ingresos'], 0, ',', '.') ?></h3>
                <p class="mb-0">Ingresos por reservas</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><i class="bi bi-envelope"></i> Mensajes sin leer</h5>
                <p class="display-6"><?= (int) $stats['mensajes_no_leidos'] ?></p>
                <a href="<?= BASE_URL ?>/admin/mensajes" class="btn btn-sm btn-outline-dark">Ver mensajes</a>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/admin_footer.php'; ?>
