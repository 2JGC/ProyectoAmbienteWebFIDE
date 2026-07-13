<?php $tituloPagina = 'Recuperar contraseña'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4"><i class="bi bi-key"></i> Recuperar contraseña</h3>

                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($mensajeExito)): ?>
                        <div class="alert alert-info"><?= e($mensajeExito) ?></div>
                    <?php else: ?>
                        <p class="text-muted">Ingrese su correo y le enviaremos un enlace para restablecer su contraseña.</p>
                        <form method="post" action="<?= BASE_URL ?>/olvide-password" novalidate>
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Enviar enlace</button>
                        </form>
                    <?php endif; ?>

                    <p class="text-center mt-3 mb-0"><a href="<?= BASE_URL ?>/login">Volver al inicio de sesión</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
