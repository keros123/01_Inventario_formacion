<?php
$id = (int) $prestamo['id_movimiento'];
$descripcion = htmlspecialchars($form['descripcion'] ?? '');
$pendienteTotal = (int) ($pendienteTotal ?? 0);
?>

<div class="page-header">
    <h2 class="page-title">Dar de baja préstamo #<?= (int) $prestamo['Consecutivo'] ?></h2>
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
            <div class="col-md-4">
                <small class="text-muted d-block">Cuentadante</small>
                <strong><?= htmlspecialchars($prestamo['Cuentadante'] ?? '—') ?></strong>
                <?php if (!empty($prestamo['Cedula_cuentadante'])): ?>
                <br><small class="text-muted"><?= htmlspecialchars($prestamo['Cedula_cuentadante']) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $config['base_url'] ?>/prestamos/dar-de-baja/store" id="formDarDeBaja" enctype="multipart/form-data">
            <input type="hidden" name="id_movimiento" value="<?= $id ?>">

            <h5 class="section-title">Elementos a dar de baja</h5>
            <p class="text-muted small mb-3">
                Se darán de baja todos los elementos pendientes del préstamo.
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
                            <th style="width: 140px;">A dar de baja</th>
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
                                       class="form-control form-control-sm"
                                       min="0" max="<?= (int) $linea['pendiente'] ?>"
                                       value="<?= (int) $linea['pendiente'] ?>" readonly>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <label class="form-label">Fotografías de los elementos * (mínimo 1, máximo 3)</label>
                <input type="file" name="fotos[]" class="form-control" accept="image/*" multiple required
                       id="fotosDarBaja">
                <small class="text-muted">Adjunte entre 1 y 3 fotografías que evidencien el estado de los elementos.</small>
            </div>

            <div class="mb-4">
                <label class="form-label">Observaciones (obligatorio)</label>
                <textarea name="descripcion" class="form-control" rows="2"
                          placeholder="Observaciones del dado de baja" required><?= $descripcion ?></textarea>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-dar-de-baja btn-sm">
                    <i class="bi bi-x-circle"></i> Dar de baja
                </button>
                <a href="<?= $config['base_url'] ?>/prestamos" class="btn btn-outline-secondary btn-sm">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('formDarDeBaja')?.addEventListener('submit', function (e) {
    const input = document.getElementById('fotosDarBaja');
    const count = input?.files?.length || 0;
    if (count < 1) {
        e.preventDefault();
        alert('Debe adjuntar al menos una fotografía.');
        return;
    }
    if (count > 3) {
        e.preventDefault();
        alert('Máximo 3 fotografías permitidas.');
    }
});
</script>

