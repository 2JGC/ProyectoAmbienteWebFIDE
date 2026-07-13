<?php $tituloPagina = 'Contacto'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <h1 class="mb-4 text-center">Contáctenos</h1>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="post" action="<?= BASE_URL ?>/contacto" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required value="<?= e($_POST['nombre'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asunto</label>
                            <input type="text" name="asunto" class="form-control" required value="<?= e($_POST['asunto'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensaje</label>
                            <textarea name="mensaje" class="form-control" rows="5" required><?= e($_POST['mensaje'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Enviar mensaje</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
