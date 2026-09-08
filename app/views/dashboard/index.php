<?php $base = $config['base_url']; ?>

<div class="page-header">
    <h2 class="page-title">Panel principal</h2>
    <p class="page-subtitle">
        <?= $esAdmin
            ? 'Resumen del sistema de inventario y solicitudes de préstamo'
            : 'Resumen de inventario y sus solicitudes de préstamo' ?>
    </p>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-archive"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= (int) $totalInventario ?></span>
                <span class="stat-label">Elementos en inventario</span>
            </div>
        </div>
    </div>

    <?php if ($esAdmin): ?>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-box-arrow-right"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= (int) $totalPrestamosActivos ?></span>
                <span class="stat-label">Préstamos activos</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <a href="<?= $base ?>/solicitudes" class="text-decoration-none">
            <div class="stat-card<?= ($solicitudesPendientes ?? 0) > 0 ? ' stat-card-alert' : '' ?>">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= (int) ($solicitudesPendientes ?? 0) ?></span>
                    <span class="stat-label">Solicitudes pendientes</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= $base ?>/agenda-computadores/pendientes" class="text-decoration-none">
            <div class="stat-card<?= ($agendaPendientes ?? 0) > 0 ? ' stat-card-alert' : '' ?>">
                <div class="stat-icon"><i class="bi bi-laptop"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= (int) ($agendaPendientes ?? 0) ?></span>
                    <span class="stat-label">Agenda computadores pendientes</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-shield-lock"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= (int) $totalUsuarios ?></span>
                <span class="stat-label">Usuarios del sistema</span>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-4">
        <a href="<?= $base ?>/solicitudes/mis" class="text-decoration-none">
            <div class="stat-card<?= ($misSolicitudesPendientes ?? 0) > 0 ? ' stat-card-alert' : '' ?>">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= (int) ($misSolicitudesPendientes ?? 0) ?></span>
                    <span class="stat-label">Mis solicitudes pendientes</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= $base ?>/agenda-computadores/mis" class="text-decoration-none">
            <div class="stat-card<?= ($misAgendaPendientes ?? 0) > 0 ? ' stat-card-alert' : '' ?>">
                <div class="stat-icon"><i class="bi bi-laptop"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= (int) ($misAgendaPendientes ?? 0) ?></span>
                    <span class="stat-label">Mis reservas de portátiles</span>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-box-arrow-right"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= (int) ($misPrestamosActivos ?? 0) ?></span>
                <span class="stat-label">Mis préstamos activos</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Accesos rápidos</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= $base ?>/inventario" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-archive"></i>
                <?= $esAdmin ? 'Gestionar inventario' : 'Consultar inventario' ?>
            </a>

            <?php if ($esAdmin): ?>
            <a href="<?= $base ?>/categorias" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-folder2"></i> Gestionar categorías
            </a>
            <a href="<?= $base ?>/movimientos" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left-right"></i> Ver movimientos
            </a>
            <a href="<?= $base ?>/prestamos" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-right"></i> Préstamos
            </a>
            <a href="<?= $base ?>/solicitudes" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-calendar3"></i> Calendario de solicitudes
            </a>
            <a href="<?= $base ?>/solicitudes/pendientes" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-hourglass-split"></i> Solicitudes pendientes
            </a>
            <a href="<?= $base ?>/agenda-computadores" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-laptop"></i> Agenda computadores
            </a>
            <a href="<?= $base ?>/agenda-computadores/nueva" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Reservar portátiles
            </a>
            <a href="<?= $base ?>/agenda-computadores/pendientes" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-hourglass-split"></i> Agenda pendientes
            </a>
            <a href="<?= $base ?>/reportes" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-bar-chart"></i> Ver reportes
            </a>
            <a href="<?= $base ?>/usuarios" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-people"></i> Gestionar usuarios
            </a>
            <?php else: ?>
            <a href="<?= $base ?>/solicitudes/nueva" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nueva solicitud
            </a>
            <a href="<?= $base ?>/solicitudes" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-calendar3"></i> Calendario
            </a>
            <a href="<?= $base ?>/solicitudes/mis" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-list-check"></i> Mis solicitudes
            </a>
            <a href="<?= $base ?>/agenda-computadores" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-laptop"></i> Agenda computadores
            </a>
            <a href="<?= $base ?>/agenda-computadores/nueva" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Reservar portátiles
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
