<?php $tituloPagina = 'Mensajes'; require BASE_PATH . '/views/layouts/admin_header.php'; ?>

<h1 class="mb-4">Mensajes de contacto</h1>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead class="table-dark">
            <tr><th>#</th><th>Nombre</th><th>Correo</th><th>Asunto</th><th>Mensaje</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($mensajes as $m): ?>
            <tr class="<?= $m['leido'] ? '' : 'table-warning' ?>">
                <td><?= (int) $m['id_contacto'] ?></td>
                <td><?= e($m['nombre']) ?></td>
                <td><?= e($m['email']) ?></td>
                <td><?= e($m['asunto']) ?></td>
                <td style="max-width:250px;"><?= e($m['mensaje']) ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($m['fecha_envio']))) ?></td>
                <td><?= $m['leido'] ? '<span class="badge bg-success">Leído</span>' : '<span class="badge bg-warning text-dark">Nuevo</span>' ?></td>
                <td class="d-flex gap-1">
                    <?php if (!$m['leido']): ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/mensajes">
                            <?= csrf_field() ?>
                            <input type="hidden" name="accion" value="leido">
                            <input type="hidden" name="id_contacto" value="<?= (int) $m['id_contacto'] ?>">
                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-check2"></i></button>
                        </form>
                    <?php endif; ?>
                    <form method="post" action="<?= BASE_URL ?>/admin/mensajes" onsubmit="return confirm('¿Eliminar mensaje?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_contacto" value="<?= (int) $m['id_contacto'] ?>">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($mensajes)): ?>
            <tr><td colspan="8" class="text-center text-muted">No hay mensajes.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require BASE_PATH . '/views/layouts/admin_footer.php'; ?>
