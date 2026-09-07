<?php
$f = $filters ?? [];
$tipoReporte = $tipoReporte ?? 'movimientos';
$queryBase = array_filter([
    'reporte'         => $tipoReporte,
    'fecha_desde'     => $f['fecha_desde'] ?? '',
    'fecha_hasta'     => $f['fecha_hasta'] ?? '',
    'categoria'       => $f['id_categoria'] ?? '',
    'elemento'        => $f['elemento'] ?? '',
    'estado'          => $f['estado'] ?? '',
    'tipo'            => $f['tipo'] ?? '',
    'cuentadante'     => $f['cedula_cuentadante'] ?? '',
    'cantidad_operador' => $f['cantidad_operador'] ?? '',
    'cantidad_valor'    => $f['cantidad_valor'] ?? '',
    'buscar'          => '1',
], static fn($v) => $v !== '' && $v !== null);
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Reportes</h2>
        <p class="page-subtitle">Reporte de movimientos y reporte de inventario</p>
    </div>
    <?php if (!empty($resultados)): ?>
    <a href="<?= $config['base_url'] ?>/reportes/export-csv?<?= http_build_query($queryBase) ?>"
       class="btn btn-success btn-sm">
        <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
    </a>
    <?php endif; ?>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= $config['base_url'] ?>/reportes" class="row g-3" id="formReportes">
            <input type="hidden" name="buscar" value="1">

            <div class="col-md-4">
                <label class="form-label">Tipo de reporte</label>
                <select name="reporte" class="form-select form-select-sm" id="tipoReporte">
                    <option value="movimientos" <?= $tipoReporte === 'movimientos' ? 'selected' : '' ?>>
                        Reporte de movimientos
                    </option>
                    <option value="inventario" <?= $tipoReporte === 'inventario' ? 'selected' : '' ?>>
                        Reporte de inventario
                    </option>
                </select>
            </div>

            <div class="col-md-3 filtro-movimiento">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($f['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-md-3 filtro-movimiento">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($f['fecha_hasta'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int) $cat['id_categoria'] ?>"
                        <?= (int) ($f['id_categoria'] ?? 0) === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['Nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Elemento</label>
                <input type="text" name="elemento" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($f['elemento'] ?? '') ?>"
                       placeholder="Código o nombre...">
            </div>

            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select form-select-sm" id="estadoReporte">
                    <option value="">Todos</option>
                    <?php if ($tipoReporte === 'movimientos'): ?>
                    <option value="Activo" <?= ($f['estado'] ?? '') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="Inactivo" <?= ($f['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    <option value="Cerrado" <?= ($f['estado'] ?? '') === 'Cerrado' ? 'selected' : '' ?>>Cerrado</option>
                    <?php else: ?>
                    <option value="Activo" <?= ($f['estado'] ?? '') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="Inactivo" <?= ($f['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2 filtro-inventario">
                <label class="form-label">Cantidad</label>
                <select name="cantidad_operador" class="form-select form-select-sm">
                    <option value="">—</option>
                    <option value="mayor" <?= ($f['cantidad_operador'] ?? '') === 'mayor' ? 'selected' : '' ?>>Mayor que</option>
                    <option value="igual" <?= ($f['cantidad_operador'] ?? '') === 'igual' ? 'selected' : '' ?>>Igual a</option>
                    <option value="menor" <?= ($f['cantidad_operador'] ?? '') === 'menor' ? 'selected' : '' ?>>Menor que</option>
                </select>
            </div>
            <div class="col-md-2 filtro-inventario">
                <label class="form-label">Valor</label>
                <input type="number" name="cantidad_valor" class="form-control form-control-sm" min="0"
                       value="<?= htmlspecialchars((string) ($f['cantidad_valor'] ?? '')) ?>"
                       placeholder="0">
            </div>

            <div class="col-md-3 filtro-movimiento">
                <label class="form-label">Tipo de movimiento</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Ingreso" <?= ($f['tipo'] ?? '') === 'Ingreso' ? 'selected' : '' ?>>Ingreso</option>
                    <option value="Prestamo" <?= ($f['tipo'] ?? '') === 'Prestamo' ? 'selected' : '' ?>>Préstamo</option>
                    <option value="Devolucion" <?= ($f['tipo'] ?? '') === 'Devolucion' ? 'selected' : '' ?>>Devolución</option>
                    <option value="Dar_Baja" <?= ($f['tipo'] ?? '') === 'Dar_Baja' ? 'selected' : '' ?>>Dar de baja</option>
                </select>
            </div>

            <div class="col-md-3 filtro-movimiento">
                <label class="form-label">Cuentadante</label>
                <select name="cuentadante" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($cuentadantes as $c): ?>
                    <option value="<?= htmlspecialchars($c['Cedula']) ?>"
                        <?= ($f['cedula_cuentadante'] ?? '') === $c['Cedula'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['Nombres']) ?> (<?= htmlspecialchars($c['Cedula']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> Generar reporte
                </button>
                <a href="<?= $config['base_url'] ?>/reportes?reporte=<?= urlencode($tipoReporte) ?>" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<?php if (isset($_GET['buscar']) && empty($resultados)): ?>
<div class="alert alert-info">No se encontraron registros con los criterios seleccionados.</div>
<?php endif; ?>

<?php if (!empty($resultados)): ?>
<div class="card">
    <div class="card-header bg-light py-2">
        <strong>
            <?= $tipoReporte === 'inventario' ? 'Reporte de inventario' : 'Reporte de movimientos' ?>
        </strong>
        <span class="badge bg-secondary ms-2"><?= count($resultados) ?> registro(s)</span>
    </div>
    <div class="table-responsive">
        <?php if ($tipoReporte === 'inventario'): ?>
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Elemento</th>
                    <th>Categoría</th>
                    <th class="text-center">Cantidad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $row): ?>
                <tr>
                    <td><code><?= htmlspecialchars($row['Codigo']) ?></code></td>
                    <td><?= htmlspecialchars($row['Elemento']) ?></td>
                    <td><?= htmlspecialchars($row['Categoria'] ?? '—') ?></td>
                    <td class="text-center"><?= (int) $row['Cantidad'] ?></td>
                    <td><?= htmlspecialchars($row['Estado']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Cuentadante</th>
                    <th>Categoría</th>
                    <th>Elemento</th>
                    <th class="text-center">Cantidad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $row): ?>
                <tr>
                    <td><code>#<?= (int) $row['Consecutivo'] ?></code></td>
                    <td><?= htmlspecialchars(MovimientoModel::etiquetaTipo($row['Tipo'])) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['Fecha']))) ?></td>
                    <td><?= htmlspecialchars($row['Cuentadante'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($row['Categoria'] ?? '—') ?></td>
                    <td>
                        <code><?= htmlspecialchars($row['Codigo_elemento']) ?></code>
                        <?= htmlspecialchars($row['Elemento']) ?>
                    </td>
                    <td class="text-center"><?= (int) $row['Cantidad'] ?></td>
                    <td><?= htmlspecialchars($row['Estado']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    const tipoReporte = document.getElementById('tipoReporte');
    const form = document.getElementById('formReportes');

    function actualizarFiltros() {
        const esInventario = tipoReporte.value === 'inventario';
        document.querySelectorAll('.filtro-movimiento').forEach(function (el) {
            el.style.display = esInventario ? 'none' : '';
        });
        document.querySelectorAll('.filtro-inventario').forEach(function (el) {
            el.style.display = esInventario ? '' : 'none';
        });
    }

    tipoReporte?.addEventListener('change', function () {
        actualizarFiltros();
        form.submit();
    });

    actualizarFiltros();
})();
</script>
