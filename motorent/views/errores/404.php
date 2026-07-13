<?php $tituloPagina = 'Página no encontrada'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5 text-center">
    <h1 class="display-1 text-warning">404</h1>
    <p class="lead">La página que busca no existe.</p>
    <a href="<?= BASE_URL ?>/" class="btn btn-dark">Volver al inicio</a>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
