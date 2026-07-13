<?php $tituloPagina = 'Galería comunitaria'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Galería comunitaria</h1>
        <?php if (is_logueado()): ?>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalPublicar">
                <i class="bi bi-upload"></i> Compartir foto
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($publicaciones)): ?>
            <p class="text-muted text-center">Todavía no hay publicaciones aprobadas.</p>
        <?php endif; ?>
        <?php foreach ($publicaciones as $pub): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?= BASE_URL ?>/uploads/galeria/<?= e($pub['imagen']) ?>" class="card-img-top" style="height:220px;object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= e($pub['titulo']) ?></h5>
                        <p class="card-text small text-muted"><?= nl2br(e($pub['descripcion'] ?? '')) ?></p>
                        <p class="card-text small">Por <?= e($pub['nombre']) ?> <?= e($pub['apellidos']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if (is_logueado()): ?>
<div class="modal fade" id="modalPublicar" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/galeria" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Compartir foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control" accept="image/*" required>
                </div>
                <p class="small text-muted mb-0">Tu publicación quedará pendiente de aprobación por el equipo de MotoRent.</p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning">Enviar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
