<?php
$val = static function (string $key, string $default = '') use ($form): string {
    return htmlspecialchars((string) ($form[$key] ?? $default));
};
$user = Auth::user();
?>

<div class="page-header">
    <h2 class="page-title">Solicitud de computadores</h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/agenda-computadores" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Volver al calendario
        </a>
    </p>
</div>

<div class="alert alert-info py-2">
    <i class="bi bi-info-circle"></i>
    La solicitud quedará <strong>pendiente de aprobación</strong> y será visible en el calendario para todos los usuarios.
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $config['base_url'] ?>/agenda-computadores/store">
            <h5 class="section-title">Datos del solicitante</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label" for="nombres">Nombres y apellidos *</label>
                    <input type="text" name="nombres" id="nombres" class="form-control"
                           value="<?= $val('nombres', $user['nombres'] ?? '') ?>" required maxlength="150">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="telefono">Teléfono móvil *</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control"
                           value="<?= $val('telefono') ?>" required maxlength="20" placeholder="3001234567">
                </div>
            </div>

            <h5 class="section-title">Datos de la reserva</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label" for="fecha">Fecha de solicitud *</label>
                    <input type="date" name="fecha" id="fecha" class="form-control"
                           value="<?= $val('fecha', date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="cantidad">Cantidad de portátiles *</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control"
                           value="<?= $val('cantidad', '1') ?>" min="1" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="sede">Sede *</label>
                    <select name="sede" id="sede" class="form-select" required>
                        <option value="Ternera" <?= $val('sede') === 'Emprender' ? '' : 'selected' ?>>Ternera</option>
                        <option value="Emprender" <?= $val('sede') === 'Emprender' ? 'selected' : '' ?>>Emprender</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="hora_inicio">Hora inicio *</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" class="form-control"
                           value="<?= $val('hora_inicio', '07:00') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="hora_final">Hora final *</label>
                    <input type="time" name="hora_final" id="hora_final" class="form-control"
                           value="<?= $val('hora_final', '12:00') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="ficha_programa">Ficha - Programa *</label>
                    <input type="text" name="ficha_programa" id="ficha_programa" class="form-control"
                           value="<?= $val('ficha_programa') ?>" required maxlength="150"
                           placeholder="Ej. 2871234 - ADSO">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Enviar solicitud
                </button>
                <a href="<?= $config['base_url'] ?>/agenda-computadores" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
