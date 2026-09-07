<?php
$s = $solicitud;
$estadoBadge = match ($s['Estado']) {
    'Pendiente' => 'warning',
    'Aprobada'  => 'success',
    'Rechazada' => 'danger',
    'Cancelada' => 'secondary',
    default     => 'secondary',
};
$volver = Auth::isAdmin() ? '/solicitudes' : '/solicitudes/mis';
?>

<div class="page-header">
    <h2 class="page-title">Solicitud #<?= (int) $s['id_solicitud'] ?></h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] . $volver ?>" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </p>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Solicitante</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Solicitante']) ?></dd>
                    <dt class="col-sm-5">Cédula</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($s['Cedula_solicitante']) ?></code></dd>
                    <dt class="col-sm-5">Fecha</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars(date('d/m/Y', strtotime($s['Fecha_solicitud']))) ?></dd>
                    <dt class="col-sm-5">Estado</dt>
                    <dd class="col-sm-7">
                        <span class="badge bg-<?= $estadoBadge ?>"><?= htmlspecialchars($s['Estado']) ?></span>
                    </dd>
                    <?php if (!empty($s['Descripcion'])): ?>
                    <dt class="col-sm-5">Descripción</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Descripcion']) ?></dd>
                    <?php endif; ?>
                    <?php if ($s['Estado'] === 'Aprobada' && !empty($s['id_movimiento'])): ?>
                    <dt class="col-sm-5">Préstamo</dt>
                    <dd class="col-sm-7">
                        <a href="<?= $config['base_url'] ?>/movimientos/prestamo/documento?id=<?= (int) $s['id_movimiento'] ?>">
                            Ver documento
                        </a>
                    </dd>
                    <?php endif; ?>
                    <?php if ($s['Estado'] === 'Rechazada' && !empty($s['Motivo_rechazo'])): ?>
                    <dt class="col-sm-5">Motivo rechazo</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Motivo_rechazo']) ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($s['Aprobador'])): ?>
                    <dt class="col-sm-5">Resuelto por</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Aprobador']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header bg-light py-2"><strong>Elementos solicitados</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Elemento</th>
                            <th class="text-center">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalle as $d): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($d['Codigo_elemento']) ?></code></td>
                            <td><?= htmlspecialchars($d['Elemento']) ?></td>
                            <td class="text-center"><?= (int) $d['Cantidad'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (Auth::isAdmin() && $s['Estado'] === 'Pendiente'): ?>
        <div class="card">
            <div class="card-body d-flex flex-wrap gap-2 align-items-start">
                <form method="POST" action="<?= $config['base_url'] ?>/solicitudes/aprobar"
                      onsubmit="return confirm('¿Aprobar esta solicitud y registrar el préstamo?')">
                    <input type="hidden" name="id" value="<?= (int) $s['id_solicitud'] ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Aprobar
                    </button>
                </form>
                <form method="POST" action="<?= $config['base_url'] ?>/solicitudes/rechazar" class="flex-grow-1">
                    <input type="hidden" name="id" value="<?= (int) $s['id_solicitud'] ?>">
                    <div class="input-group">
                        <input type="text" name="motivo" class="form-control" placeholder="Motivo del rechazo" required>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-lg"></i> Rechazar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
