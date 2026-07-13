<?php $tituloPagina = 'Motocicletas'; require BASE_PATH . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestión de motocicletas</h1>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalMoto" onclick="limpiarFormularioMoto()">
        <i class="bi bi-plus-circle"></i> Nueva motocicleta
    </button>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped bg-white align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th><th>Marca/Modelo</th><th>Año</th><th>Categoría</th><th>Precio/día</th><th>Placa</th><th>Estado</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($motos as $m): ?>
            <tr>
                <td><?= (int) $m['id_moto'] ?></td>
                <td><?= e($m['marca']) ?> <?= e($m['modelo']) ?></td>
                <td><?= e((string) $m['anio']) ?></td>
                <td><?= e($m['categoria']) ?></td>
                <td>₡<?= number_format((float) $m['precio_dia'], 0, ',', '.') ?></td>
                <td><?= e($m['placa']) ?></td>
                <td><span class="badge bg-secondary"><?= e($m['estado']) ?></span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary"
                            onclick='cargarFormularioMoto(<?= json_encode($m) ?>)'
                            data-bs-toggle="modal" data-bs-target="#modalMoto">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="post" action="<?= BASE_URL ?>/admin/motos" class="d-inline"
                          onsubmit="return confirm('¿Eliminar esta motocicleta?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_moto" value="<?= (int) $m['id_moto'] ?>">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal crear/editar motocicleta -->
<div class="modal fade" id="modalMoto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/admin/motos" class="modal-content" id="formMoto">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" id="accionMoto" value="crear">
            <input type="hidden" name="id_moto" id="id_moto">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalMoto">Nueva motocicleta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Marca</label><input type="text" name="marca" id="marca" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Modelo</label><input type="text" name="modelo" id="modelo" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Año</label><input type="number" name="anio" id="anio" class="form-control" min="1990" max="2100" required></div>
                    <div class="col-md-4"><label class="form-label">Cilindraje (cc)</label><input type="number" name="cilindraje" id="cilindraje" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Categoría</label>
                        <select name="categoria" id="categoria" class="form-select" required>
                            <?php foreach (['Scooter', 'Naked', 'Enduro', 'Deportiva', 'Touring'] as $c): ?>
                                <option value="<?= e($c) ?>"><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Precio por día (₡)</label><input type="number" step="0.01" name="precio_dia" id="precio_dia" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Placa</label><input type="text" name="placa" id="placa" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="disponible">Disponible</option>
                            <option value="reservada">Reservada</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label">Descripción</label><textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea></div>
                    <div class="col-12"><label class="form-label">Imagen (opcional al editar)</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function limpiarFormularioMoto() {
    document.getElementById('formMoto').reset();
    document.getElementById('accionMoto').value = 'crear';
    document.getElementById('id_moto').value = '';
    document.getElementById('tituloModalMoto').textContent = 'Nueva motocicleta';
}
function cargarFormularioMoto(m) {
    document.getElementById('accionMoto').value = 'editar';
    document.getElementById('id_moto').value = m.id_moto;
    document.getElementById('marca').value = m.marca;
    document.getElementById('modelo').value = m.modelo;
    document.getElementById('anio').value = m.anio;
    document.getElementById('cilindraje').value = m.cilindraje;
    document.getElementById('categoria').value = m.categoria;
    document.getElementById('precio_dia').value = m.precio_dia;
    document.getElementById('placa').value = m.placa;
    document.getElementById('estado').value = m.estado;
    document.getElementById('descripcion').value = m.descripcion || '';
    document.getElementById('tituloModalMoto').textContent = 'Editar motocicleta';
}
</script>

<?php require BASE_PATH . '/views/layouts/admin_footer.php'; ?>
