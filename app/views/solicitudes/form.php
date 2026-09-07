<?php
$user = Auth::user();
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
$fechaDefault = $form['fecha'] ?? ($_GET['fecha'] ?? date('Y-m-d'));
?>

<div class="page-header">
    <h2 class="page-title">Solicitud de préstamo</h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/solicitudes" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Volver al calendario
        </a>
    </p>
</div>

<div class="alert alert-info py-2">
    <i class="bi bi-info-circle"></i>
    La solicitud quedará <strong>pendiente de aprobación</strong>. El inventario no se descontará hasta que un administrador la apruebe.
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
        <form method="POST" action="<?= $config['base_url'] ?>/solicitudes/store" id="formSolicitudPrestamo">

            <h5 class="section-title">Datos de la solicitud</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Fecha solicitada *</label>
                    <input type="date" name="fecha" id="fechaSolicitud" class="form-control"
                           value="<?= htmlspecialchars($fechaDefault) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" class="form-control bg-light"
                           value="<?= htmlspecialchars($user['cedula']) ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Solicitante</label>
                    <input type="text" class="form-control bg-light"
                           value="<?= htmlspecialchars($user['nombres']) ?>" readonly>
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
                                <th style="width: 120px;">Disponible</th>
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
                            <tr class="linea-elemento" data-codigo="<?= htmlspecialchars($linea['codigo']) ?>"
                                data-stock="<?= (int) ($linea['stock'] ?? 0) ?>">
                                <td><code><?= htmlspecialchars($linea['codigo']) ?></code></td>
                                <td class="linea-nombre"><?= htmlspecialchars($linea['elemento'] ?? '') ?></td>
                                <td class="linea-stock"><?= (int) ($linea['stock'] ?? 0) ?></td>
                                <td>
                                    <input type="number" name="linea_cantidad[]" class="form-control form-control-sm input-cantidad"
                                           min="1" max="<?= (int) ($linea['stock'] ?? 0) ?>"
                                           value="<?= htmlspecialchars($linea['cantidad'] ?? '') ?>" required>
                                </td>
                                <td class="text-end">
                                    <input type="hidden" name="linea_codigo[]" value="<?= htmlspecialchars($linea['codigo']) ?>">
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
                <label class="form-label">Descripción / motivo del préstamo</label>
                <textarea name="descripcion" id="descripcionSolicitud" class="form-control" rows="3"
                          placeholder="Indique el uso o motivo de la solicitud"><?= $val('descripcion') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" id="btnEnviarSolicitud">
                    <i class="bi bi-send"></i> Enviar solicitud
                </button>
                <a href="<?= $config['base_url'] ?>/solicitudes" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div class="modal fade modal-busqueda" id="modalElemento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar elemento del inventario</h5>
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
                                <th class="text-center">Disponible</th>
                            </tr>
                        </thead>
                        <tbody id="resultadosElemento">
                            <tr>
                                <td colspan="4" class="modal-busqueda-empty">Ingrese un término para buscar...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="alertModalElemento" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarElemento" disabled>Agregar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmarSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar solicitud de préstamo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resumenConfirmacion"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarSolicitud">
                    <i class="bi bi-check-lg"></i> Confirmar envío
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.solicitudData = {
    elementos: <?= $elementosJson ?: '[]' ?>,
    solicitante: {
        cedula: <?= json_encode($user['cedula']) ?>,
        nombres: <?= json_encode($user['nombres']) ?>
    }
};
</script>
<script src="<?= $config['base_url'] ?>/js/modal-busqueda.js"></script>
<script src="<?= $config['base_url'] ?>/js/solicitud-prestamo.js"></script>
