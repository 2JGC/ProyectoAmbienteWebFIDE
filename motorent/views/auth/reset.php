<?php $tituloPagina = 'Restablecer contraseña'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4"><i class="bi bi-shield-lock"></i> Nueva contraseña</h3>

                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($registro): ?>
                        <form method="post" action="<?= BASE_URL ?>/reset-password" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="token" value="<?= e($token) ?>">
                            <div class="mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password2" class="form-control" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Actualizar contraseña</button>
                        </form>
                    <?php else: ?>
                        <p class="text-center"><a href="<?= BASE_URL ?>/olvide-password">Solicitar un nuevo enlace</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
