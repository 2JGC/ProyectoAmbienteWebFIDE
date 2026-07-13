<?php $tituloPagina = 'Usuarios'; require BASE_PATH . '/views/layouts/admin_header.php'; ?>

<h1 class="mb-4">Gestión de usuarios</h1>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead class="table-dark">
            <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= (int) $u['id_usuario'] ?></td>
                <td><?= e($u['nombre']) ?> <?= e($u['apellidos']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td>
                    <form method="post" action="<?= BASE_URL ?>/admin/usuarios">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="rol">
                        <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                        <select name="rol" class="form-select form-select-sm" onchange="this.form.submit()"
                            <?= (int) $u['id_usuario'] === (int) $_SESSION['usuario_id'] ? 'disabled' : '' ?>>
                            <option value="cliente" <?= $u['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                            <option value="administrador" <?= $u['rol'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </form>
                </td>
                <td>
                    <form method="post" action="<?= BASE_URL ?>/admin/usuarios">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="estado">
                        <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                        <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()"
                            <?= (int) $u['id_usuario'] === (int) $_SESSION['usuario_id'] ? 'disabled' : '' ?>>
                            <option value="activo" <?= $u['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $u['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </form>
                </td>
                <td><?= e(date('d/m/Y', strtotime($u['fecha_registro']))) ?></td>
                <td>
                    <?php if ((int) $u['id_usuario'] !== (int) $_SESSION['usuario_id']): ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/usuarios"
                              onsubmit="return confirm('¿Eliminar este usuario?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require BASE_PATH . '/views/layouts/admin_footer.php'; ?>
