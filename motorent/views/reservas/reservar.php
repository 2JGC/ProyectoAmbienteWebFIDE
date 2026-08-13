<?php $tituloPagina = 'Reservar motocicleta'; require BASE_PATH . '/views/layouts/header.php'; ?>

<!-- Flatpickr: librería para que cada campo de fecha abra su propio calendario -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-4"><i class="bi bi-calendar-check"></i> Reservar <?= e($moto['marca']) ?> <?= e($moto['modelo']) ?></h3>

                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted">Precio por día: <strong>₡<?= number_format((float) $moto['precio_dia'], 0, ',', '.') ?></strong></p>

                    <form method="post" action="<?= BASE_URL ?>/reservar?id=<?= (int) $moto['id_moto'] ?>" novalidate id="formReserva">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_moto" value="<?= (int) $moto['id_moto'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de inicio</label>
                                <input type="text" id="fecha_inicio_visible" class="form-control" placeholder="Elegí la fecha" required autocomplete="off">
                                <!-- El campo real que se envía al servidor va oculto; el de arriba es solo para que el usuario elija en el calendario. -->
                                <input type="hidden" name="fecha_inicio" id="fecha_inicio">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de fin</label>
                                <input type="text" id="fecha_fin_visible" class="form-control" placeholder="Elegí la fecha" required autocomplete="off">
                                <input type="hidden" name="fecha_fin" id="fecha_fin">
                            </div>
                        </div>
                        <p class="mt-3 mb-0">Total estimado: <strong id="totalEstimado">₡0</strong></p>
                        <button type="submit" class="btn btn-warning w-100 mt-4">Continuar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    // Cálculo dinámico del total estimado en el cliente (validación/UX, no reemplaza al servidor)
    const precioDia = <?= (float) $moto['precio_dia'] ?>;
    const inputInicioOculto = document.getElementById('fecha_inicio');
    const inputFinOculto    = document.getElementById('fecha_fin');
    const totalEl = document.getElementById('totalEstimado');

    // El servidor espera las fechas en formato YYYY-MM-DD.
    function formatoServidor(fecha) {
        const y = fecha.getFullYear();
        const m = String(fecha.getMonth() + 1).padStart(2, '0');
        const d = String(fecha.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function actualizarTotal() {
        const inicio = fpInicio.selectedDates[0];
        const fin = fpFin.selectedDates[0];
        if (inicio && fin) {
            // +1 porque el conteo es inclusivo: si eliges el mismo día
            // para inicio y fin, es 1 día de alquiler (no 0). Debe dar
            // el mismo resultado que ReservaController::reservar() en PHP.
            const dias = Math.round((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
            totalEl.textContent = dias > 0 ? '₡' + (dias * precioDia).toLocaleString('es-CR') : '₡0';
        } else {
            totalEl.textContent = '₡0';
        }
    }

    // Esta función se llama por cada día que dibuja CUALQUIERA de los dos
    // calendarios. Si ese día cae dentro del rango [inicio, fin] que ya
    // se eligió, le agregamos una clase para pintarlo (ver style.css).
    // Así, aunque son dos campos separados, los dos calendarios "saben"
    // cuál es el rango completo y lo muestran igual.
    //
    // Ojo: esta función se usa como "onDayCreate" y Flatpickr la llama
    // desde el momento en que arma el primer calendario (fpInicio), antes
    // de que exista fpFin. Por eso declaramos las dos variables con "let"
    // más abajo ANTES de crear ningún calendario: así, aunque todavía no
    // se les haya asignado el calendario correspondiente, valen
    // "undefined" en vez de romper el script.
    function pintarRango(dObj, dStr, fp, dayElem) {
        if (!fpInicio || !fpFin) {
            return;
        }
        const inicio = fpInicio.selectedDates[0];
        const fin = fpFin.selectedDates[0];
        if (!inicio || !fin) {
            return;
        }
        const desde = inicio <= fin ? inicio : fin;
        const hasta = inicio <= fin ? fin : inicio;
        if (dayElem.dateObj >= desde && dayElem.dateObj <= hasta) {
            dayElem.classList.add('rango-seleccionado');
        }
    }

    // Se declaran antes de crear los calendarios (ver comentario arriba).
    let fpInicio, fpFin;

    fpInicio = flatpickr('#fecha_inicio_visible', {
        locale: 'es',
        dateFormat: 'd/m/Y',
        minDate: 'today',
        onDayCreate: pintarRango,
        onChange: function (fechasSeleccionadas) {
            const inicio = fechasSeleccionadas[0] ?? null;
            inputInicioOculto.value = inicio ? formatoServidor(inicio) : '';

            // La fecha de fin puede ser el mismo día que el inicio (una
            // renta de 1 solo día), pero no puede ser anterior.
            if (inicio) {
                fpFin.set('minDate', inicio);
                if (fpFin.selectedDates[0] && fpFin.selectedDates[0] < inicio) {
                    fpFin.clear();
                    inputFinOculto.value = '';
                }
            }

            actualizarTotal();
            fpInicio.redraw();
            fpFin.redraw();
        },
    });

    fpFin = flatpickr('#fecha_fin_visible', {
        locale: 'es',
        dateFormat: 'd/m/Y',
        minDate: 'today',
        onDayCreate: pintarRango,
        onChange: function (fechasSeleccionadas) {
            const fin = fechasSeleccionadas[0] ?? null;
            inputFinOculto.value = fin ? formatoServidor(fin) : '';

            actualizarTotal();
            fpInicio.redraw();
            fpFin.redraw();
        },
    });
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
