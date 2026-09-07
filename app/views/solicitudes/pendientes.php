<?php
$estadoBadge = static function (string $estado): string {
    return match ($estado) {
        'Pendiente' => 'warning',
        'Aprobada'  => 'success',
        'Rechazada' => 'danger',
        'Cancelada' => 'secondary',
        default     => 'secondary',
    };
};
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Solicitudes pendientes</h2>
        <p class="page-subtitle">Apruebe o rechace las solicitudes de préstamo de los usuarios</p>
    </div>
    <a href="<?= $config['base_url'] ?>/solicitudes" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-calendar3"></i> Calendario
    </a>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Solicitante</th>
                    <th class="text-center">Líneas</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($solicitudes)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay solicitudes pendientes.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($solicitudes as $s): ?>
                <tr>
                    <td><code>#<?= (int) $s['id_solicitud'] ?></code></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($s['Fecha_solicitud']))) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($s['Solicitante']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($s['Cedula_solicitante']) ?></small>
                    </td>
                    <td class="text-center"><?= (int) $s['lineas'] ?></td>
                    <td class="text-end">
                        <a href="<?= $config['base_url'] ?>/solicitudes/ver?id=<?= (int) $s['id_solicitud'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Revisar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
