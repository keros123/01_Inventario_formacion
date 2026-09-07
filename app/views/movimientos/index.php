<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Movimientos de inventario</h2>
        <p class="page-subtitle">Historial de ingresos, préstamos, devoluciones y Dar_baja</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="<?= $config['base_url'] ?>/movimientos/export-csv?<?= http_build_query($_GET) ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
        </a>
        <a href="<?= $config['base_url'] ?>/movimientos/ingreso" class="btn btn-success btn-sm">
            <i class="bi bi-box-arrow-in-down"></i> Ingreso
        </a>
        <a href="<?= $config['base_url'] ?>/movimientos/prestamo" class="btn btn-warning btn-sm">
            <i class="bi bi-box-arrow-right"></i> Préstamo
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php
$filterTipo = $filters['tipo'] ?? '';
$filterCuentadante = $filters['cedula_cuentadante'] ?? '';

$tipoLabel = static function (string $tipo): string {
    return MovimientoModel::etiquetaTipo($tipo);
};

$tipoBadge = static function (string $tipo): string {
    if (MovimientoModel::esDarDeBaja($tipo)) {
        return 'danger';
    }

    return match ($tipo) {
        'Ingreso'    => 'success',
        'Prestamo'   => 'warning text-dark',
        'Devolucion' => 'info text-dark',
        default      => 'secondary',
    };
};

$esDarDeBaja = static function (string $tipo): bool {
    return MovimientoModel::esDarDeBaja($tipo);
};
?>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= $config['base_url'] ?>/movimientos" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Ingreso" <?= $filterTipo === 'Ingreso' ? 'selected' : '' ?>>Ingreso</option>
                    <option value="Prestamo" <?= $filterTipo === 'Prestamo' ? 'selected' : '' ?>>Préstamo</option>
                    <option value="Devolucion" <?= $filterTipo === 'Devolucion' ? 'selected' : '' ?>>Devolución</option>
                    <option value="Dar_Baja" <?= $filterTipo === 'Dar_Baja' || $filterTipo === 'DarDeBaja' ? 'selected' : '' ?>>Dar_baja</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label mb-1">Cuentadante</label>
                <select name="cuentadante" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($cuentadantes as $c): ?>
                    <option value="<?= htmlspecialchars($c['Cedula']) ?>"
                            <?= $filterCuentadante === $c['Cedula'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['Nombres']) ?> (<?= htmlspecialchars($c['Cedula']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?= $config['base_url'] ?>/movimientos" class="btn btn-outline-secondary btn-sm">
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Cuentadante</th>
                    <th>Referencia</th>
                    <th>Elementos</th>
                    <th class="text-center">Unidades</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <?= ($filterTipo || $filterCuentadante)
                            ? 'No hay movimientos con los filtros seleccionados.'
                            : 'No hay movimientos registrados.' ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($items as $item): ?>
                <?php
                $tipo = $item['Tipo'] ?? '';
                $documentoUrl = match (true) {
                    $tipo === 'Ingreso'    => $config['base_url'] . '/movimientos/ingreso/documento?id=' . $item['id_movimiento'],
                    $tipo === 'Prestamo'   => $config['base_url'] . '/movimientos/prestamo/documento?id=' . $item['id_movimiento'],
                    $tipo === 'Devolucion' => $config['base_url'] . '/movimientos/devolucion/documento?id=' . $item['id_movimiento'],
                    $esDarDeBaja($tipo)    => $config['base_url'] . '/prestamos/dar-de-baja/documento?id=' . $item['id_movimiento'],
                    default                => null,
                };
                ?>
                <tr>
                    <td><code>#<?= (int) $item['Consecutivo'] ?></code></td>
                    <td>
                        <span class="badge bg-<?= $tipoBadge($tipo) ?>">
                            <?= htmlspecialchars($tipoLabel($tipo)) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['Fecha']))) ?></td>
                    <td>
                        <?php if ($item['Cuentadante']): ?>
                        <strong><?= htmlspecialchars($item['Cuentadante']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($item['Cedula_cuentadante']) ?></small>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($tipo === 'Devolucion' || $esDarDeBaja($tipo)) && !empty($item['Consecutivo_ref'])): ?>
                        <a href="<?= $config['base_url'] ?>/prestamos?cuentadante=<?= urlencode($item['Cedula_cuentadante'] ?? '') ?>"
                           class="text-decoration-none" title="Ver préstamos">
                            Préstamo #<?= (int) $item['Consecutivo_ref'] ?>
                        </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['Elementos']): ?>
                        <small><?= htmlspecialchars($item['Elementos']) ?></small>
                        <?php if ($item['Descripcion']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($item['Descripcion']) ?></small>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= (int) ($item['Total_unidades'] ?? 0) ?></td>
                    <td class="text-end">
                        <?php if ($documentoUrl): ?>
                            <a href="<?= $documentoUrl ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark-pdf"></i> Ver Documento
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
