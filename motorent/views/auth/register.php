<?php $tituloPagina = 'Registro'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4"><i class="bi bi-person-plus"></i> Crear cuenta</h3>

                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/registro" novalidate class="needs-validation" id="formRegistro">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required
                                       value="<?= e($_POST['nombre'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" required
                                       value="<?= e($_POST['apellidos'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control"
                                       value="<?= e($_POST['telefono'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cédula</label>
                                <input type="text" name="cedula" class="form-control"
                                       value="<?= e($_POST['cedula'] ?? '') ?>">
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control" required minlength="8">
                                <div class="form-text">Mínimo 8 caracteres.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password2" id="password2" class="form-control" required minlength="8">
                                <div class="invalid-feedback" id="feedbackPassword">Las contraseñas no coinciden.</div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 mt-4">Registrarme</button>
                    </form>

                    <p class="text-center mt-3 mb-0">¿Ya tiene cuenta? <a href="<?= BASE_URL ?>/login">Inicie sesión</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/validaciones.js"></script>
<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
