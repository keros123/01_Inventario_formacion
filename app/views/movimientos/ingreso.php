<?php
$val = function ($key, $default = '') use ($form) {
    return htmlspecialchars($form[$key] ?? $default);
};
$lineas = $form['lineas'] ?? [];
$elementosJson = json_encode(array_map(static function ($el) {
    return [
        'codigo'    => $el['Codigo'],
        'elemento'  => $el['Elemento'],
        'stock'     => (int) $el['Cantidad'],
        'categoria' => $el['Categoria'] ?? '',
    ];
}, $elementos), JSON_UNESCAPED_UNICODE);
$categoriasJson = json_encode($categorias ?? [], JSON_UNESCAPED_UNICODE);
?>

<div class="page-header">
    <h2 class="page-title">Ingreso de inventario</h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/inventario" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Volver al inventario
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
        <form method="POST" action="<?= $config['base_url'] ?>/movimientos/ingreso/store" id="formIngreso">

            <h5 class="section-title">Datos del movimiento</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Fecha *</label>
                    <input type="date" name="fecha" id="fechaIngreso" class="form-control"
                           value="<?= $val('fecha', date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" id="usuario_cedula"
                           class="form-control bg-light" value="<?= htmlspecialchars(Auth::user()['cedula']) ?>"
                           readonly tabindex="-1">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input type="text" id="usuario_nombre"
                           class="form-control bg-light" value="<?= htmlspecialchars(Auth::user()['nombres']) ?>"
                           readonly tabindex="-1">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h5 class="section-title mb-0 border-0 pb-0">Elementos</h5>
                <button type="button" class="btn btn-primary btn-sm" id="btnAgregarLinea">
                    <i class="bi bi-plus-lg"></i> Agregar elemento
                </button>
            </div>

            <div class="card mb-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Elemento</th>
                                <th style="width: 120px;">Stock</th>
                                <th style="width: 140px;">Cantidad *</th>
                                <th class="text-end" style="width: 90px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="lineasElemento">
                            <tr id="lineasVacio" <?= !empty($lineas) ? 'hidden' : '' ?>>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay elementos agregados.
                                </td>
                            </tr>
                            <?php foreach ($lineas as $linea): ?>
                            <tr class="linea-elemento" data-codigo="<?= htmlspecialchars($linea['codigo']) ?>">
                                <td><code><?= htmlspecialchars($linea['codigo']) ?></code></td>
                                <td class="linea-nombre"><?= htmlspecialchars($linea['elemento'] ?? '') ?></td>
                                <td class="linea-stock"><?= isset($linea['stock']) ? (int) $linea['stock'] : '—' ?></td>
                                <td>
                                    <input type="number" name="linea_cantidad[]" class="form-control form-control-sm"
                                           min="1" value="<?= htmlspecialchars($linea['cantidad'] ?? '') ?>" required>
                                </td>
                                <td class="text-end">
                                    <input type="hidden" name="linea_codigo[]" value="<?= htmlspecialchars($linea['codigo']) ?>">
                                    <input type="hidden" name="linea_nuevo[]" value="<?= !empty($linea['nuevo']) ? '1' : '0' ?>">
                                    <input type="hidden" name="linea_elemento_nuevo[]" value="<?= htmlspecialchars($linea['elemento_nuevo'] ?? '') ?>">
                                    <input type="hidden" name="linea_categoria_nuevo[]" value="<?= htmlspecialchars($linea['categoria_nuevo'] ?? '') ?>">
                                    <input type="hidden" name="linea_descripcion_nuevo[]" value="<?= htmlspecialchars($linea['descripcion_nuevo'] ?? '') ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-linea" title="Quitar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Descripción del movimiento</label>
                <textarea name="descripcion" id="descripcionIngreso" class="form-control" rows="3"><?= $val('descripcion') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" id="btnGuardarIngreso">
                    <i class="bi bi-box-arrow-in-down"></i> Registrar ingreso
                </button>
                <a href="<?= $config['base_url'] ?>/inventario" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<!-- Modal buscar elemento -->
<div class="modal fade modal-busqueda" id="modalElemento" tabindex="-1" aria-labelledby="modalElementoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div id="panelBuscarElemento">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalElementoLabel">Buscar elemento del inventario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-busqueda-search">
                        <input type="search" id="buscarElementoInput" class="form-control"
                               placeholder="Buscar por código, nombre o categoría..." autocomplete="off">
                    </div>
                    <div class="modal-busqueda-table-wrap">
                        <table class="table table-hover modal-busqueda-table mb-0">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Elemento</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody id="resultadosElemento">
                                <tr>
                                    <td colspan="4" class="modal-busqueda-empty">Ingrese un término para buscar...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="modal-busqueda-create" id="btnIrCrearElemento">
                        <i class="bi bi-plus-circle"></i>
                        ¿No encuentra el elemento? Crear nuevo
                    </button>
                    <div id="alertModalElemento" class="alert alert-danger mt-3 d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarElemento" disabled>Agregar</button>
                </div>
            </div>

            <div id="panelCrearElemento" hidden>
                <div class="modal-header">
                    <h5 class="modal-title">Crear nuevo elemento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Código *</label>
                            <input type="text" id="codigoNuevoElemento" class="form-control">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Elemento *</label>
                            <input type="text" id="nombreNuevoElemento" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Categoría *</label>
                            <select id="categoriaNuevoElemento" class="form-select">
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int) $cat['id_categoria'] ?>">
                                    <?= htmlspecialchars($cat['Nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Cantidad *</label>
                            <input type="number" id="cantidadNuevoElemento" class="form-control" min="1" value="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea id="descripcionNuevoElemento" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div id="alertModalCrearElemento" class="alert alert-danger mt-3 d-none" role="alert"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-link text-decoration-none" id="btnVolverBuscarElemento">
                        <i class="bi bi-arrow-left"></i> Volver a buscar
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnConfirmarNuevoElemento">Agregar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal confirmación -->
<div class="modal fade" id="modalConfirmarIngreso" tabindex="-1" aria-labelledby="modalConfirmarIngresoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarIngresoLabel">Confirmar ingreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resumenConfirmacion"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarIngreso">
                    <i class="bi bi-check-lg"></i> Confirmar ingreso
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.ingresoData = {
    elementos: <?= $elementosJson ?: '[]' ?>,
    categorias: <?= $categoriasJson ?: '[]' ?>
};
</script>
<script src="<?= $config['base_url'] ?>/js/modal-busqueda.js"></script>
<script src="<?= $config['base_url'] ?>/js/ingreso.js"></script>
