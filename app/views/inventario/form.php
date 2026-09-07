<?php
$isEdit = ($action ?? '') === 'update';
$val = function ($key, $default = '') use ($item) {
    if (is_array($item)) {
        return htmlspecialchars($item[$key] ?? $item[strtolower($key)] ?? $default);
    }
    return htmlspecialchars($default);
};
$fotografia = '';
if (is_array($item)) {
    $fotografia = trim($item['Fotografia'] ?? $item['fotografia'] ?? '');
}
$previewUrl = $fotografia !== '' ? file_url($fotografia) : '';
?>

<div class="page-header">
    <h2 class="page-title"><?= $isEdit ? 'Editar elemento' : 'Nuevo elemento' ?></h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/inventario" class="text-decoration-none">
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
        <form method="POST"
              action="<?= $config['base_url'] ?>/inventario/<?= $isEdit ? 'update' : 'store' ?>"
              enctype="multipart/form-data">
            <?php if ($isEdit): ?>
            <input type="hidden" name="codigo_original" value="<?= $val('Codigo') ?>">
            <?php endif; ?>

            <div class="row g-3 align-items-start">
                <div class="col-lg-9">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Código *</label>
                            <input type="text" name="codigo" class="form-control"
                                   value="<?= $val('Codigo') ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Elemento *</label>
                            <input type="text" name="elemento" class="form-control"
                                   value="<?= $val('Elemento') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría *</label>
                            <select name="id_categoria" class="form-select" required>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int) $cat['id_categoria'] ?>"
                                    <?= (int) ($item['id_categoria'] ?? $item['Id_categoria'] ?? 0) === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['Nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad</label>
                            <?php if ($isEdit): ?>
                            <input type="number" class="form-control bg-light"
                                   value="<?= $val('Cantidad', '0') ?>" readonly tabindex="-1">
                            <input type="hidden" name="cantidad" value="<?= $val('Cantidad', '0') ?>">
                            <small class="text-muted">Se actualiza con ingresos y préstamos.</small>
                            <?php else: ?>
                            <input type="number" class="form-control bg-light" value="0" readonly tabindex="-1">
                            <input type="hidden" name="cantidad" value="0">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="Activo" <?= $val('Estado', 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="Inactivo" <?= $val('Estado') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="4"><?= $val('Descripcion') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-photo-panel">
                        <div class="photo-preview-panel" id="photoPreviewContainer">
                            <img src="<?= htmlspecialchars($previewUrl) ?>" alt="Vista previa del elemento"
                                 id="fotoPreview" class="photo-preview"
                                 <?= $previewUrl === '' ? 'hidden' : '' ?>>
                            <span id="fotoPreviewPlaceholder" class="photo-preview-empty"
                                  <?= $previewUrl !== '' ? 'hidden' : '' ?>>
                                <i class="bi bi-image"></i>
                            </span>
                        </div>
                        <label class="form-label mb-1" for="fotografiaInput">Fotografía</label>
                        <input type="file" name="fotografia" id="fotografiaInput" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Guardar
                </button>
                <a href="<?= $config['base_url'] ?>/inventario" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
