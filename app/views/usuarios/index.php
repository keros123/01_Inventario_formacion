<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h2 class="page-title">Usuarios</h2>
        <p class="page-subtitle">Usuarios del sistema con rol Admin o Usuario (responsables de préstamos)</p>
    </div>
    <a href="<?= $config['base_url'] ?>/usuarios/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nuevo usuario
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
                    <th>Cédula</th>
                    <th>Nombres</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><code><?= htmlspecialchars($item['Cedula']) ?></code></td>
                    <td><?= htmlspecialchars($item['Nombres']) ?></td>
                    <td>
                        <span class="badge bg-<?= $item['Tipo'] === 'Admin' ? 'primary' : 'info' ?>">
                            <?= htmlspecialchars($item['Tipo']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?= $item['Estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($item['Estado']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= $config['base_url'] ?>/usuarios/edit?cedula=<?= urlencode($item['Cedula']) ?>"
                           class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="<?= $config['base_url'] ?>/usuarios/delete"
                              class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                            <input type="hidden" name="cedula" value="<?= htmlspecialchars($item['Cedula']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
