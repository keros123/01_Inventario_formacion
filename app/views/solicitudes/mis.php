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
        <h2 class="page-title">Mis solicitudes</h2>
        <p class="page-subtitle">
            <a href="<?= $config['base_url'] ?>/solicitudes" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Volver al calendario
            </a>
        </p>
    </div>
    <a href="<?= $config['base_url'] ?>/solicitudes/nueva" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nueva solicitud
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
                    <th>Estado</th>
                    <th class="text-center">Líneas</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($solicitudes)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No tiene solicitudes registradas.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($solicitudes as $s): ?>
                <tr>
                    <td><code>#<?= (int) $s['id_solicitud'] ?></code></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($s['Fecha_solicitud']))) ?></td>
                    <td>
                        <span class="badge bg-<?= $estadoBadge($s['Estado']) ?>">
                            <?= htmlspecialchars($s['Estado']) ?>
                        </span>
                    </td>
                    <td class="text-center"><?= (int) $s['lineas'] ?></td>
                    <td class="text-end">
                        <a href="<?= $config['base_url'] ?>/solicitudes/ver?id=<?= (int) $s['id_solicitud'] ?>"
                           class="btn btn-sm btn-outline-primary" title="Ver">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if ($s['Estado'] === 'Pendiente'): ?>
                        <form method="POST" action="<?= $config['base_url'] ?>/solicitudes/cancelar"
                              class="d-inline" onsubmit="return confirm('¿Cancelar esta solicitud?')">
                            <input type="hidden" name="id" value="<?= (int) $s['id_solicitud'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
