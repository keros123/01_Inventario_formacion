<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Inventario</h2>
        <p class="page-subtitle">
            <?= Auth::isAdmin()
                ? 'Gestión de elementos del inventario'
                : 'Consulta de elementos del inventario' ?>
        </p>
    </div>
    <?php if (Auth::isAdmin()): ?>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= $config['base_url'] ?>/movimientos/ingreso" class="btn btn-success btn-sm">
            <i class="bi bi-box-arrow-in-down"></i> Ingreso
        </a>
        <a href="<?= $config['base_url'] ?>/movimientos/prestamo" class="btn btn-warning btn-sm">
            <i class="bi bi-box-arrow-right"></i> Préstamo
        </a>
        <a href="<?= $config['base_url'] ?>/inventario/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Nuevo elemento
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php
$busqueda = $busqueda ?? '';
$categoriaId = $categoriaId ?? null;
$items = $items ?? [];
$exportParams = [];
if ($busqueda !== '') {
    $exportParams['q'] = $busqueda;
}
if ($categoriaId) {
    $exportParams['categoria'] = $categoriaId;
}
?>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= $config['base_url'] ?>/inventario" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label mb-1">Buscar elemento</label>
                <input type="search" name="q" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($busqueda) ?>"
                       placeholder="Código, nombre, descripción o categoría">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Categoría</label>
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int) $cat['id_categoria'] ?>"
                        <?= $categoriaId === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['Nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?= $config['base_url'] ?>/inventario/export-csv<?= $exportParams ? '?' . http_build_query($exportParams) : '' ?>"
                   class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
                </a>
                <?php if ($busqueda !== '' || $categoriaId): ?>
                <a href="<?= $config['base_url'] ?>/inventario" class="btn btn-outline-secondary btn-sm">
                    Limpiar
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($items)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-4">
        <?= $busqueda !== '' || $categoriaId
            ? 'No hay elementos que coincidan con la búsqueda.'
            : 'No hay elementos registrados.' ?>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
        <strong><i class="bi bi-box-seam"></i> Inventario</strong>
        <span class="badge bg-secondary"><?= count($items) ?> elemento(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th class="photo-col">Foto</th>
                    <th>Código</th>
                    <th>Elemento</th>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>Estado</th>
                    <?php if (Auth::isAdmin()): ?>
                    <th class="text-end">Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <?php $foto = trim($item['Fotografia'] ?? ''); ?>
                <tr>
                    <td class="photo-cell">
                        <?php if ($foto !== ''): ?>
                        <button type="button" class="photo-thumb-btn photo-thumb-wrap"
                                data-photo-src="<?= htmlspecialchars(file_url($foto)) ?>"
                                data-photo-title="<?= htmlspecialchars($item['Elemento']) ?>"
                                title="Ver fotografía">
                            <img src="<?= htmlspecialchars(file_url($foto)) ?>"
                                 alt="<?= htmlspecialchars($item['Elemento']) ?>"
                                 class="photo-thumb" width="32" height="32">
                        </button>
                        <?php else: ?>
                        <span class="photo-thumb-placeholder" title="Sin fotografía">
                            <i class="bi bi-image"></i>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td><code><?= htmlspecialchars($item['Codigo']) ?></code></td>
                    <td>
                        <strong><?= htmlspecialchars($item['Elemento']) ?></strong>
                        <?php if ($item['Descripcion']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($item['Descripcion']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['Categoria'] ?? 'Sin categoría') ?></td>
                    <td><?= (int) $item['Cantidad'] ?></td>
                    <td>
                        <span class="badge bg-<?= $item['Estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($item['Estado']) ?>
                        </span>
                    </td>
                    <?php if (Auth::isAdmin()): ?>
                    <td class="text-end">
                        <a href="<?= $config['base_url'] ?>/inventario/edit?codigo=<?= urlencode($item['Codigo']) ?>"
                           class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="<?= $config['base_url'] ?>/inventario/delete"
                              class="d-inline" onsubmit="return confirm('¿Eliminar este elemento?')">
                            <input type="hidden" name="codigo" value="<?= htmlspecialchars($item['Codigo']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered photo-modal-dialog">
        <div class="modal-content photo-modal-content">
            <div class="modal-body p-0">
                <img src="" alt="" id="photoModalImage" class="photo-modal-image">
            </div>
        </div>
    </div>
</div>
