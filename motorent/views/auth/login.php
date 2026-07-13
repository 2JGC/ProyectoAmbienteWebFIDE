<?php $tituloPagina = 'Iniciar sesión'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4"><i class="bi bi-box-arrow-in-right"></i> Iniciar sesión</h3>

                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/login" novalidate class="needs-validation">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Ingresar</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/olvide-password">¿Olvidó su contraseña?</a>
                    </div>
                    <hr>
                    <p class="text-center mb-0">¿No tiene cuenta? <a href="<?= BASE_URL ?>/registro">Regístrese aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/validaciones.js"></script>
<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
