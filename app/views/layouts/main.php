<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Inventarios Ambientes de Formación CCyS') ?> - <?= htmlspecialchars($config['name']) ?></title>
    <link rel="icon" href="<?= $config['base_url'] ?>/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= $config['base_url'] ?>/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $config['base_url'] ?>/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="app-header">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <span class="app-logo"><i class="bi bi-box-seam"></i></span>
                <div>
                    <h1 class="app-title mb-0"><?= htmlspecialchars($config['name']) ?></h1>
                    <small class="app-subtitle">Sistema de gestión</small>
                </div>
            </div>
            <?php $user = Auth::user(); ?>
            <?php if ($user): ?>
            <div class="d-flex align-items-center gap-3">
                <span class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($user['nombres']) ?>
                    <span class="badge badge-role"><?= htmlspecialchars($user['tipo']) ?></span>
                </span>
                <a href="<?= $config['base_url'] ?>/logout" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="app-body">
        <?php if ($user): ?>
        <nav class="app-sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/dashboard">
                        <i class="bi bi-grid"></i> Panel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/inventario">
                        <i class="bi bi-archive"></i> Inventario
                    </a>
                </li>
                <?php if (Auth::isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/categorias">
                        <i class="bi bi-folder2"></i> Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/movimientos">
                        <i class="bi bi-arrow-left-right"></i> Movimientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/prestamos">
                        <i class="bi bi-box-arrow-right"></i> Préstamos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/solicitudes">
                        <i class="bi bi-calendar3"></i> Solicitudes
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/solicitudes/nueva">
                        <i class="bi bi-plus-circle"></i> Nueva solicitud
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/solicitudes">
                        <i class="bi bi-calendar3"></i> Calendario
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/solicitudes/mis">
                        <i class="bi bi-list-check"></i> Mis solicitudes
                    </a>
                </li>
                <?php endif; ?>
                <?php if (Auth::isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/reportes">
                        <i class="bi bi-bar-chart"></i> Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $config['base_url'] ?>/usuarios">
                        <i class="bi bi-shield-lock"></i> Usuarios
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <main class="app-content">
            <?= $content ?>
        </main>
    </div>

    <footer class="app-footer">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div>
                <small class="footer-text-left">© <?= date('Y') ?> Designed by Fabio Perez Marquez, Dinamizador TIC</small>
                <br>
                <small class="footer-text-left">Centro de Formación de Comercio y Servicios</small>
            </div>
            <div class="footer-links">
                <a href="#" class="footer-link">Términos de uso</a>
                <a href="#" class="footer-link">Privacidad</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $config['base_url'] ?>/js/app.js"></script>
</body>
</html>
