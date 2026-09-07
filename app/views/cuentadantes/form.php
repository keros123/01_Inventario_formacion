<?php
$isEdit = ($action ?? '') === 'update';
$val = function ($key, $default = '') use ($item) {
    if (is_array($item)) {
        return htmlspecialchars($item[$key] ?? $item[strtolower($key)] ?? $default);
    }
    return htmlspecialchars($default);
};
?>

<div class="page-header">
    <h2 class="page-title"><?= $isEdit ? 'Editar cuentadante' : 'Nuevo cuentadante' ?></h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/cuentadantes" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </p>
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
        <form method="POST" action="<?= $config['base_url'] ?>/cuentadantes/<?= $isEdit ? 'update' : 'store' ?>">
            <?php if ($isEdit): ?>
            <input type="hidden" name="cedula_original" value="<?= $val('Cedula') ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cédula *</label>
                    <input type="text" name="cedula" class="form-control"
                           value="<?= $val('Cedula') ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nombres *</label>
                    <input type="text" name="nombres" class="form-control"
                           value="<?= $val('Nombres') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="Activo" <?= $val('Estado', 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= $val('Estado') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Guardar
                </button>
                <a href="<?= $config['base_url'] ?>/cuentadantes" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
