<?php $tituloPagina = 'Catálogo'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <h1 class="mb-4">Catálogo de motocicletas</h1>

    <form method="get" action="<?= BASE_URL ?>/catalogo" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Buscar por marca o modelo"
                   value="<?= e($_GET['q'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                <?php foreach (['Scooter', 'Naked', 'Enduro', 'Deportiva', 'Touring'] as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= (($_GET['categoria'] ?? '') === $cat) ? 'selected' : '' ?>>
                        <?= e($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-dark w-100" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
        </div>
    </form>

    <div class="row g-4">
        <?php if (empty($motos)): ?>
            <p class="text-muted text-center">No se encontraron motocicletas con esos criterios.</p>
        <?php endif; ?>
        <?php foreach ($motos as $moto): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?= e($moto['imagen']) ?>"
                         onerror="this.src='<?= BASE_URL ?>/assets/img/default-moto.jpg'"
                         class="card-img-top" alt="<?= e($moto['marca']) ?>" style="height:200px;object-fit:cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= e($moto['marca']) ?> <?= e($moto['modelo']) ?> (<?= e((string) $moto['anio']) ?>)</h5>
                        <p class="text-muted mb-1"><?= e($moto['categoria']) ?> · <?= e((string) $moto['cilindraje']) ?>cc</p>
                        <span class="badge <?= $moto['estado'] === 'disponible' ? 'bg-success' : 'bg-secondary' ?> mb-2">
                            <?= e(ucfirst($moto['estado'])) ?>
                        </span>
                        <p class="fw-bold text-success">₡<?= number_format((float) $moto['precio_dia'], 0, ',', '.') ?> / día</p>
                        <a href="<?= BASE_URL ?>/moto?id=<?= (int) $moto['id_moto'] ?>" class="btn btn-outline-dark mt-auto">Ver detalle</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
