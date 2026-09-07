<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Préstamos realizados</h2>
        <p class="page-subtitle">Historial de préstamos registrados en el inventario</p>
    </div>
    <a href="<?= $config['base_url'] ?>/movimientos/prestamo" class="btn btn-warning btn-sm">
        <i class="bi bi-box-arrow-right"></i> Nuevo préstamo
    </a>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>

<?php
$filterCuentadante = $filters['cedula_cuentadante'] ?? '';
?>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= $config['base_url'] ?>/prestamos" class="row g-2 align-items-end">
            <div class="col-md-8">
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
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?= $config['base_url'] ?>/prestamos" class="btn btn-outline-secondary btn-sm">
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
                    <th>Fecha</th>
                    <th>Cuentadante</th>
                    <th>Elementos</th>
                    <th class="text-center">Unidades</th>
                    <th class="text-center">Pendiente</th>
                    <th style="min-width: 120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <?= ($filterCuentadante) ? 'No hay préstamos con los filtros seleccionados.' : 'No hay préstamos registrados.' ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($items as $item): ?>
                <?php
                $id = (int) $item['id_movimiento'];
                $pendiente = (int) ($item['Pendiente_total'] ?? 0);
                ?>
                <tr>
                    <td><code>#<?= (int) $item['Consecutivo'] ?></code></td>
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
                    <td class="text-center">
                        <?php if ($pendiente > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $pendiente ?></span>
                        <?php else: ?>
                        <span class="text-muted">0</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-flex flex-column gap-2 justify-content-end align-items-end">
                        <?php if ($item['Estado'] === 'Activo'): ?>
                            <?php if ($pendiente > 0): ?>
                                <a href="<?= $config['base_url'] ?>/prestamos/devolucion?id=<?= $id ?>"
                                   class="btn btn-primary btn-sm btn-action"
                                   title="Registrar devolución">
                                    <i class="bi bi-box-arrow-in-left"></i> Devolución
                                </a>
                            <?php endif; ?>
                            <a href="<?= $config['base_url'] ?>/prestamos/dar-de-baja?id=<?= $id ?>"
                               class="btn btn-dar-de-baja btn-sm btn-action"
                               title="Dar de baja préstamo">
                                <i class="bi bi-x-circle"></i> Dar de Baja
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
