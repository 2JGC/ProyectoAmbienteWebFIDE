<?php $tituloPagina = 'Mis reservas'; require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <h1 class="mb-4">Mis reservas</h1>

    <?php if (empty($reservas)): ?>
        <div class="alert alert-info">Aún no tiene reservas. <a href="<?= BASE_URL ?>/catalogo">Explore el catálogo</a>.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Motocicleta</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Días</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reservas as $r): ?>
                    <tr>
                        <td><?= e($r['marca']) ?> <?= e($r['modelo']) ?></td>
                        <td><?= e($r['fecha_inicio']) ?></td>
                        <td><?= e($r['fecha_fin']) ?></td>
                        <td><?= e((string) $r['total_dias']) ?></td>
                        <td>₡<?= number_format((float) $r['total_pago'], 0, ',', '.') ?></td>
                        <td>
                            <?php
                                $badges = [
                                    'pendiente' => 'bg-warning text-dark', 'confirmada' => 'bg-primary',
                                    'en_curso' => 'bg-info text-dark', 'finalizada' => 'bg-success', 'cancelada' => 'bg-secondary',
                                ];
                            ?>
                            <span class="badge <?= $badges[$r['estado']] ?? 'bg-secondary' ?>"><?= e(ucfirst($r['estado'])) ?></span>
                        </td>
                        <td>
                            <?php if (in_array($r['estado'], ['pendiente', 'confirmada'], true)): ?>
                                <form method="post" action="<?= BASE_URL ?>/reservar/cancelar"
                                      onsubmit="return confirm('¿Cancelar esta reserva?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_reserva" value="<?= (int) $r['id_reserva'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
