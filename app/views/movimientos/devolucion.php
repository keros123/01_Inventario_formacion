<?php
$id = (int) $prestamo['id_movimiento'];
$descripcion = htmlspecialchars($form['descripcion'] ?? '');
$pendienteTotal = (int) ($pendienteTotal ?? 0);
$estadoActual = $prestamo['Estado'] ?? 'Activo';
?>

<div class="page-header">
    <h2 class="page-title"><?= $pendienteTotal > 0 ? 'Devolución' : 'Gestión' ?> de préstamo #<?= (int) $prestamo['Consecutivo'] ?></h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/prestamos" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Volver a préstamos
        </a>
    </p>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <small class="text-muted d-block">Fecha préstamo</small>
                <strong><?= htmlspecialchars(date('d/m/Y', strtotime($prestamo['Fecha']))) ?></strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Cuentadante</small>
                <strong><?= htmlspecialchars($prestamo['Cuentadante'] ?? '—') ?></strong>
                <?php if (!empty($prestamo['Cedula_cuentadante'])): ?>
                <br><small class="text-muted"><?= htmlspecialchars($prestamo['Cedula_cuentadante']) ?></small>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Estado</small>
                <strong><?= htmlspecialchars($estadoActual) ?></strong>
            </div>
        </div>
    </div>
</div>

<?php if ($pendienteTotal > 0): ?>
<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $config['base_url'] ?>/prestamos/devolucion/store" id="formDevolucion">
            <input type="hidden" name="id_movimiento" value="<?= $id ?>">

            <h5 class="section-title">Elementos pendientes</h5>
            <p class="text-muted small mb-3">
                Indique la cantidad a devolver por cada elemento. Deje en cero los que no se devuelven en esta operación.
            </p>

            <div class="table-responsive mb-4">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Elemento</th>
                            <th class="text-center">Prestado</th>
                            <th class="text-center">Devuelto</th>
                            <th class="text-center">Pendiente</th>
                            <th style="width: 140px;">A devolver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lineas as $linea): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($linea['codigo']) ?></code></td>
                            <td><?= htmlspecialchars($linea['elemento']) ?></td>
                            <td class="text-center"><?= (int) $linea['prestado'] ?></td>
                            <td class="text-center"><?= (int) $linea['devuelto'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark"><?= (int) $linea['pendiente'] ?></span>
                            </td>
                            <td>
                                <input type="hidden" name="linea_codigo[]" value="<?= htmlspecialchars($linea['codigo']) ?>">
                                <input type="number" name="linea_cantidad[]"
                                       class="form-control form-control-sm linea-devolver"
                                       min="0" max="<?= (int) $linea['pendiente'] ?>"
                                       value="0" data-pendiente="<?= (int) $linea['pendiente'] ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <label class="form-label">Descripción (opcional)</label>
                <textarea name="descripcion" class="form-control" rows="2"
                          placeholder="Observaciones de la devolución"><?= $descripcion ?></textarea>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-box-arrow-in-left"></i> Devolución
                </button>
                <a href="<?= $config['base_url'] ?>/prestamos" class="btn btn-outline-secondary btn-sm">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center text-muted py-4">
        <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
        No hay elementos pendientes por devolver. Puede cambiar el estado del préstamo arriba.
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formDevolucion = document.getElementById('formDevolucion');
    if (!formDevolucion) {
        return;
    }

    formDevolucion.addEventListener('submit', function (e) {
        const inputs = formDevolucion.querySelectorAll('.linea-devolver');
        let total = 0;
        for (let i = 0; i < inputs.length; i++) {
            const input = inputs[i];
            const val = parseInt(input.value, 10) || 0;
            const max = parseInt(input.dataset.pendiente, 10) || 0;
            if (val < 0 || val > max) {
                e.preventDefault();
                alert('La cantidad a devolver debe estar entre 0 y ' + max + ' para ' + input.closest('tr').querySelector('code').textContent);
                input.focus();
                return;
            }
            total += val;
        }
        if (total <= 0) {
            e.preventDefault();
            alert('Indique al menos una cantidad a devolver.');
        }
    });
});
</script>
