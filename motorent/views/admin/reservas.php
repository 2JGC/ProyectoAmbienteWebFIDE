<?php $tituloPagina = 'Reservas'; require BASE_PATH . '/views/layouts/admin_header.php'; ?>

<h1 class="mb-4">Gestión de reservas</h1>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th><th>Cliente</th><th>Motocicleta</th><th>Inicio</th><th>Fin</th><th>Días</th><th>Total</th><th>Estado</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reservas as $r): ?>
            <tr>
                <td><?= (int) $r['id_reserva'] ?></td>
                <td><?= e($r['nombre']) ?> <?= e($r['apellidos']) ?></td>
                <td><?= e($r['marca']) ?> <?= e($r['modelo']) ?></td>
                <td><?= e($r['fecha_inicio']) ?></td>
                <td><?= e($r['fecha_fin']) ?></td>
                <td><?= (int) $r['total_dias'] ?></td>
                <td>₡<?= number_format((float) $r['total_pago'], 0, ',', '.') ?></td>
                <td>
                    <form method="post" action="<?= BASE_URL ?>/admin/reservas" class="d-flex gap-1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_reserva" value="<?= (int) $r['id_reserva'] ?>">
                        <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach (['pendiente', 'confirmada', 'en_curso', 'finalizada', 'cancelada'] as $estado): ?>
                                <option value="<?= $estado ?>" <?= $r['estado'] === $estado ? 'selected' : '' ?>>
                                    <?= e(ucfirst($estado)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($reservas)): ?>
            <tr><td colspan="8" class="text-center text-muted">No hay reservas registradas.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require BASE_PATH . '/views/layouts/admin_footer.php'; ?>
