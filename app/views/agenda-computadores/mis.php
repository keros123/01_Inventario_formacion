<?php
$estadoBadge = static function (string $estado): string {
    return match ($estado) {
        'Pendiente'    => 'warning',
        'Reprogramada' => 'info',
        'Aprobada'     => 'success',
        'Rechazada'    => 'danger',
        'Cancelada'    => 'secondary',
        default        => 'secondary',
    };
};
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Mis solicitudes de computadores</h2>
        <p class="page-subtitle">
            <a href="<?= $config['base_url'] ?>/agenda-computadores" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Volver al calendario
            </a>
        </p>
    </div>
    <a href="<?= $config['base_url'] ?>/agenda-computadores/nueva" class="btn btn-primary btn-sm">
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
                    <th>Horario</th>
                    <th>Sede</th>
                    <th class="text-center">Portátiles</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($solicitudes)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No tiene solicitudes registradas.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($solicitudes as $s): ?>
                <tr>
                    <td><code>#<?= (int) $s['id_agenda'] ?></code></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($s['Fecha_solicitud']))) ?></td>
                    <td><?= htmlspecialchars($s['Hora_inicio_fmt']) ?>–<?= htmlspecialchars($s['Hora_final_fmt']) ?></td>
                    <td><?= htmlspecialchars($s['Sede']) ?></td>
                    <td class="text-center"><?= (int) $s['Cantidad_portatiles'] ?></td>
                    <td>
                        <span class="badge bg-<?= $estadoBadge($s['Estado']) ?>">
                            <?= htmlspecialchars($s['Estado']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= $config['base_url'] ?>/agenda-computadores/ver?id=<?= (int) $s['id_agenda'] ?>"
                           class="btn btn-sm btn-outline-primary" title="Ver">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if (in_array($s['Estado'], ['Pendiente', 'Reprogramada'], true)): ?>
                        <form method="POST" action="<?= $config['base_url'] ?>/agenda-computadores/cancelar"
                              class="d-inline" onsubmit="return confirm('¿Cancelar esta solicitud?')">
                            <input type="hidden" name="id" value="<?= (int) $s['id_agenda'] ?>">
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
