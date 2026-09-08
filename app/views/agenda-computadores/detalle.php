<?php
$s = $solicitud;
$estadoBadge = match ($s['Estado']) {
    'Pendiente'     => 'warning',
    'Reprogramada'  => 'info',
    'Aprobada'      => 'success',
    'Rechazada'     => 'danger',
    'Cancelada'     => 'secondary',
    default         => 'secondary',
};
$puedeReprogramar = Auth::isAdmin() && in_array($s['Estado'], ['Pendiente', 'Reprogramada', 'Aprobada'], true);
$puedeResolver = Auth::isAdmin() && in_array($s['Estado'], ['Pendiente', 'Reprogramada'], true);
?>

<div class="page-header">
    <h2 class="page-title">Agenda computadores #<?= (int) $s['id_agenda'] ?></h2>
    <p class="page-subtitle">
        <a href="<?= $config['base_url'] ?>/agenda-computadores" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Volver al calendario
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
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Solicitante</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Nombres']) ?></dd>
                    <dt class="col-sm-5">Cédula</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($s['Cedula_solicitante']) ?></code></dd>
                    <dt class="col-sm-5">Teléfono móvil</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Telefono']) ?></dd>
                    <dt class="col-sm-5">Fecha</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars(date('d/m/Y', strtotime($s['Fecha_solicitud']))) ?></dd>
                    <dt class="col-sm-5">Horario</dt>
                    <dd class="col-sm-7">
                        <?= htmlspecialchars($s['Hora_inicio_fmt']) ?> — <?= htmlspecialchars($s['Hora_final_fmt']) ?>
                    </dd>
                    <dt class="col-sm-5">Sede</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Sede']) ?></dd>
                    <dt class="col-sm-5">Portátiles</dt>
                    <dd class="col-sm-7"><?= (int) $s['Cantidad_portatiles'] ?></dd>
                    <dt class="col-sm-5">Ficha - Programa</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Ficha_programa']) ?></dd>
                    <dt class="col-sm-5">Estado</dt>
                    <dd class="col-sm-7">
                        <span class="badge bg-<?= $estadoBadge ?>"><?= htmlspecialchars($s['Estado']) ?></span>
                    </dd>
                    <?php if (!empty($s['Fecha_original'])): ?>
                    <dt class="col-sm-5">Horario original</dt>
                    <dd class="col-sm-7">
                        <?= htmlspecialchars(date('d/m/Y', strtotime($s['Fecha_original']))) ?>
                        <?= htmlspecialchars($s['Hora_inicio_original_fmt']) ?>–<?= htmlspecialchars($s['Hora_final_original_fmt']) ?>
                    </dd>
                    <?php endif; ?>
                    <?php if (!empty($s['Motivo_reprogramacion'])): ?>
                    <dt class="col-sm-5">Motivo reprogramación</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($s['Motivo_reprogramacion']) ?></dd>
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

    <div class="col-lg-6">
        <?php if ($puedeResolver): ?>
        <div class="card mb-3">
            <div class="card-header bg-light py-2"><strong>Aprobar o rechazar</strong></div>
            <div class="card-body d-flex flex-wrap gap-2 align-items-start">
                <form method="POST" action="<?= $config['base_url'] ?>/agenda-computadores/aprobar"
                      onsubmit="return confirm('¿Aprobar esta solicitud de computadores?')">
                    <input type="hidden" name="id" value="<?= (int) $s['id_agenda'] ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Aprobar
                    </button>
                </form>
                <form method="POST" action="<?= $config['base_url'] ?>/agenda-computadores/rechazar" class="flex-grow-1">
                    <input type="hidden" name="id" value="<?= (int) $s['id_agenda'] ?>">
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

        <?php if ($puedeReprogramar): ?>
        <div class="card">
            <div class="card-header bg-light py-2"><strong>Reprogramar</strong></div>
            <div class="card-body">
                <form method="POST" action="<?= $config['base_url'] ?>/agenda-computadores/reprogramar">
                    <input type="hidden" name="id" value="<?= (int) $s['id_agenda'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nueva fecha *</label>
                            <input type="date" name="fecha" class="form-control"
                                   value="<?= htmlspecialchars($s['Fecha_solicitud']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sede *</label>
                            <select name="sede" class="form-select" required>
                                <option value="Ternera" <?= $s['Sede'] === 'Ternera' ? 'selected' : '' ?>>Ternera</option>
                                <option value="Emprender" <?= $s['Sede'] === 'Emprender' ? 'selected' : '' ?>>Emprender</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora inicio *</label>
                            <input type="time" name="hora_inicio" class="form-control"
                                   value="<?= htmlspecialchars($s['Hora_inicio_fmt']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora final *</label>
                            <input type="time" name="hora_final" class="form-control"
                                   value="<?= htmlspecialchars($s['Hora_final_fmt']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motivo (opcional)</label>
                            <input type="text" name="motivo" class="form-control" maxlength="200"
                                   placeholder="Motivo de la reprogramación">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-calendar2-week"></i> Guardar reprogramación
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
