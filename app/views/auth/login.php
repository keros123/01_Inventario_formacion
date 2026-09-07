<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Iniciar sesión') ?> - <?= htmlspecialchars($config['name']) ?></title>
    <link rel="icon" href="<?= $config['base_url'] ?>/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= $config['base_url'] ?>/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $config['base_url'] ?>/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header text-center mb-4">
                <div class="login-logo"><i class="bi bi-box-seam"></i></div>
                <h2><?= htmlspecialchars($config['name']) ?></h2>
                <p class="text-muted mb-0">Inicie sesión para continuar</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= $config['base_url'] ?>/login" novalidate>
                <div class="mb-3">
                    <label for="cedula" class="form-label">Cédula</label>
                    <input type="text" class="form-control" id="cedula" name="cedula"
                           required autofocus autocomplete="username">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               required autocomplete="current-password">
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                                aria-label="Mostrar contraseña" title="Mostrar contraseña">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Ingresar
                </button>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            const visible = input.type === 'text';

            input.type = visible ? 'password' : 'text';
            icon.classList.toggle('bi-eye', visible);
            icon.classList.toggle('bi-eye-slash', !visible);
            this.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            this.setAttribute('title', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    </script>
</body>
</html>
