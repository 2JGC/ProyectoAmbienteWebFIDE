<?php $tituloPagina = 'Publicaciones'; require BASE_PATH . '/views/layouts/admin_header.php'; ?>

<h1 class="mb-4">Gestión de publicaciones (galería)</h1>

<div class="row g-4">
    <?php foreach ($publicaciones as $p): ?>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <img src="<?= e($p['imagen']) ?>" class="card-img-top" style="height:180px;object-fit:cover;">
                <div class="card-body">
                    <h6 class="card-title"><?= e($p['titulo']) ?></h6>
                    <p class="small text-muted mb-1">Por <?= e($p['nombre']) ?> <?= e($p['apellidos']) ?></p>
                    <span class="badge <?= ['pendiente' => 'bg-warning text-dark', 'aprobada' => 'bg-success', 'rechazada' => 'bg-danger'][$p['estado']] ?? 'bg-secondary' ?>">
                        <?= e(ucfirst($p['estado'])) ?>
                    </span>
                    <div class="d-flex gap-1 mt-3">
                        <?php if ($p['estado'] !== 'aprobada'): ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/publicaciones">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="aprobar">
                                <input type="hidden" name="id_publicacion" value="<?= (int) $p['id_publicacion'] ?>">
                                <button class="btn btn-sm btn-outline-success">Aprobar</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($p['estado'] !== 'rechazada'): ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/publicaciones">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="rechazar">
                                <input type="hidden" name="id_publicacion" value="<?= (int) $p['id_publicacion'] ?>">
                                <button class="btn btn-sm btn-outline-warning">Rechazar</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/publicaciones" onsubmit="return confirm('¿Eliminar publicación?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_publicacion" value="<?= (int) $p['id_publicacion'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($publicaciones)): ?>
        <p class="text-muted text-center">No hay publicaciones.</p>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/layouts/admin_footer.php'; ?>
