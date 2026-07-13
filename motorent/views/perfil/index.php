<?php $tituloPagina = 'Mi perfil'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <h1 class="mb-4">Mi perfil</h1>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4 text-center">
            <img src="<?= BASE_URL ?>/uploads/perfiles/<?= e($usuario['foto_perfil'] ?? '') ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/img/default-avatar.png'"
                 class="rounded-circle mb-3 border" style="width:160px;height:160px;object-fit:cover;">
            <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/perfil">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="foto">
                <input type="file" name="foto" class="form-control mb-2" accept="image/*" required>
                <button class="btn btn-outline-dark w-100">Actualizar foto</button>
            </form>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Datos personales</h5>
                    <form method="post" action="<?= BASE_URL ?>/perfil">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="datos">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required value="<?= e($usuario['nombre']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" required value="<?= e($usuario['apellidos']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" class="form-control" value="<?= e($usuario['email']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control" value="<?= e($usuario['telefono'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cédula</label>
                                <input type="text" name="cedula" class="form-control" value="<?= e($usuario['cedula'] ?? '') ?>">
                            </div>
                        </div>
                        <button class="btn btn-warning mt-3">Guardar cambios</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Cambiar contraseña</h5>
                    <form method="post" action="<?= BASE_URL ?>/perfil">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="password">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Contraseña actual</label>
                                <input type="password" name="password_actual" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password_nueva" class="form-control" required minlength="8">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirmar</label>
                                <input type="password" name="password_confirmar" class="form-control" required minlength="8">
                            </div>
                        </div>
                        <button class="btn btn-outline-dark mt-3">Actualizar contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
