<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? '') ?> - <?= htmlspecialchars($config['name'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 20px;
            }
        }
        .document-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 40px;
            text-align: center;
        }
        .document-title {
            color: #2b579a;
            font-weight: bold;
            font-size: 3rem;
        }
        .document-subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .document-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
        }
        .table-document th {
            background-color: #f3f3f3;
        }
        .label-col {
            width: 180px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="no-print mb-4 d-flex justify-content-end gap-2">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir PDF
            </button>
            <a href="<?= $config['base_url'] ?>/movimientos" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="document-header">
            <h1 class="document-title"><?= htmlspecialchars($config['name'] ?? '') ?></h1>
            <p class="document-subtitle">Sistema de Gestión de Inventario</p>
            <h2 class="document-number">Documento de Ingreso #<?= $ingreso['Consecutivo'] ?></h2>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <table class="table table-borderless">
                    <tr>
                        <td class="label-col">Fecha:</td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($ingreso['Fecha']))) ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Cédula</td>
                        <td><?= htmlspecialchars($ingreso['Cedula_cuentadante']) ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Nombre</td>
                        <td><?= htmlspecialchars($ingreso['Cuentadante']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <h3 class="mb-3">Detalles del Ingreso:</h3>
        <table class="table table-bordered table-document">
            <thead>
                <tr>
                    <th style="width: 150px;">CÓDIGO</th>
                    <th>ELEMENTO</th>
                    <th style="width: 150px;" class="text-end">CANTIDAD</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><code style="color: #2b579a;"><?= htmlspecialchars($detalle['Codigo_elemento']) ?></code></td>
                    <td><?= htmlspecialchars($detalle['Elemento']) ?></td>
                    <td class="text-end"><?= $detalle['Cantidad'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Total:</th>
                    <th class="text-end"><?= array_sum(array_column($detalles, 'Cantidad')) ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-5 pt-5">
            <div class="col-md-6 text-center">
                <div style="border-top: 1px solid #000; width: 300px; margin: 0 auto; margin-top: 80px;">
                    <p class="mt-2 mb-0" style="font-size: 1.1rem;">Responsable</p>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <div style="border-top: 1px solid #000; width: 300px; margin: 0 auto; margin-top: 80px;">
                    <p class="mt-2 mb-0" style="font-size: 1.1rem;">Recibido Por</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>