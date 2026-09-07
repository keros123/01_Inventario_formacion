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
    <h2 class="page-title"><?= $isEdit ? 'Editar categoría' : 'Nueva categoría' ?></h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/categorias" class="text-decoration-none">
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
        <form method="POST" action="<?= $config['base_url'] ?>/categorias/<?= $isEdit ? 'update' : 'store' ?>">
            <?php if ($isEdit): ?>
            <input type="hidden" name="id_original" value="<?= $val('id_categoria') ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= $val('Nombre') ?>" required>
                </div>
                <div class="col-md-4">
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
                <a href="<?= $config['base_url'] ?>/categorias" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
