<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/SolicitudPrestamoModel.php';
require_once __DIR__ . '/../models/InventarioModel.php';

class SolicitudesController extends Controller
{
    private SolicitudPrestamoModel $solicitudModel;
    private InventarioModel $inventarioModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        $this->solicitudModel = new SolicitudPrestamoModel();
        $this->inventarioModel = new InventarioModel();
    }

    public function index(): void
    {
        $year = max(2000, min(2100, (int) ($_GET['year'] ?? date('Y'))));
        $month = max(1, min(12, (int) ($_GET['month'] ?? date('n'))));
        $user = Auth::user();
        $soloPropio = !Auth::isAdmin();

        $this->view('solicitudes/calendario', [
            'pageTitle'   => 'Solicitudes de préstamo',
            'year'        => $year,
            'month'       => $month,
            'solicitudes' => $this->solicitudModel->getForMonth(
                $year,
                $month,
                $soloPropio ? $user['cedula'] : null
            ),
            'flash'       => $this->getFlash(),
            'puedeCrear'  => !Auth::isAdmin(),
        ]);
    }

    public function nueva(): void
    {
        if (Auth::isAdmin()) {
            $this->setFlash('info', 'Los administradores registran préstamos directamente desde Movimientos.');
            $this->redirect('/prestamos');
        }

        $fecha = trim($_GET['fecha'] ?? '');
        if ($fecha !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = '';
        }

        $this->view('solicitudes/form', [
            'pageTitle' => 'Solicitud de préstamo',
            'elementos' => $this->inventarioModel->getActivosConStock(),
            'form'      => [
                'fecha'       => $fecha !== '' ? $fecha : date('Y-m-d'),
                'descripcion' => '',
                'lineas'      => [],
            ],
            'errors'    => [],
        ]);
    }

    public function store(): void
    {
        if (Auth::isAdmin()) {
            $this->setFlash('danger', 'Los administradores registran préstamos directamente.');
            $this->redirect('/solicitudes');
        }

        $form = $this->getFormData();
        $errors = $this->validateForm($form);
        $lineas = [];

        try {
            $lineas = $this->parseLineas($form['lineas']);
            if (empty($lineas)) {
                $errors[] = 'Registre al menos un elemento con cantidad mayor a cero.';
            }
        } catch (InvalidArgumentException $e) {
            $errors = array_merge($errors, explode('|', $e->getMessage()));
        }

        if ($errors) {
            $this->view('solicitudes/form', [
                'pageTitle' => 'Solicitud de préstamo',
                'elementos' => $this->inventarioModel->getActivosConStock(),
                'form'      => $form,
                'errors'    => $errors,
            ]);
            return;
        }

        $user = Auth::user();

        try {
            $id = $this->solicitudModel->create(
                $user['cedula'],
                $form['fecha'],
                $form['descripcion'] !== '' ? $form['descripcion'] : null,
                $lineas
            );
            $this->setFlash('success', 'Solicitud #' . $id . ' enviada. Un administrador debe aprobarla.');
            $this->redirect('/solicitudes/mis');
        } catch (Throwable $e) {
            $this->view('solicitudes/form', [
                'pageTitle' => 'Solicitud de préstamo',
                'elementos' => $this->inventarioModel->getActivosConStock(),
                'form'      => $form,
                'errors'    => [$e->getMessage()],
            ]);
        }
    }

    public function mis(): void
    {
        if (Auth::isAdmin()) {
            $this->redirect('/solicitudes');
        }

        $user = Auth::user();
        $this->view('solicitudes/mis', [
            'pageTitle'   => 'Mis solicitudes',
            'solicitudes' => $this->solicitudModel->getBySolicitante($user['cedula']),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function pendientes(): void
    {
        Auth::requireAdmin();
        $this->view('solicitudes/pendientes', [
            'pageTitle'   => 'Solicitudes pendientes',
            'solicitudes' => $this->solicitudModel->getPendientes(),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function ver(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $solicitud = $this->solicitudModel->findById($id);

        if (!$solicitud) {
            $this->setFlash('danger', 'Solicitud no encontrada.');
            $this->redirect(Auth::isAdmin() ? '/solicitudes' : '/solicitudes/mis');
        }

        $user = Auth::user();
        if (!Auth::isAdmin() && $solicitud['Cedula_solicitante'] !== $user['cedula']) {
            $this->setFlash('danger', 'No tiene permiso para ver esta solicitud.');
            $this->redirect('/solicitudes/mis');
        }

        $this->view('solicitudes/detalle', [
            'pageTitle' => 'Solicitud #' . $id,
            'solicitud' => $solicitud,
            'detalle'   => $this->solicitudModel->getDetalle($id),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function aprobar(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $user = Auth::user();

        try {
            $idMovimiento = $this->solicitudModel->aprobar($id, $user['cedula']);
            $this->setFlash('success', 'Solicitud aprobada. Préstamo registrado.');
            $this->redirect('/movimientos/prestamo/documento?id=' . $idMovimiento);
        } catch (Throwable $e) {
            $this->setFlash('danger', $e->getMessage());
            $this->redirect('/solicitudes/ver?id=' . $id);
        }
    }

    public function rechazar(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $user = Auth::user();

        if ($motivo === '') {
            $this->setFlash('danger', 'Indique el motivo del rechazo.');
            $this->redirect('/solicitudes/ver?id=' . $id);
        }

        if ($this->solicitudModel->rechazar($id, $user['cedula'], $motivo)) {
            $this->setFlash('success', 'Solicitud rechazada.');
        } else {
            $this->setFlash('danger', 'No se pudo rechazar la solicitud.');
        }
        $this->redirect('/solicitudes');
    }

    public function cancelar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $user = Auth::user();

        if ($this->solicitudModel->cancelar($id, $user['cedula'])) {
            $this->setFlash('success', 'Solicitud cancelada.');
        } else {
            $this->setFlash('danger', 'No se pudo cancelar la solicitud.');
        }
        $this->redirect('/solicitudes/mis');
    }

    private function getFormData(): array
    {
        $lineas = [];
        $codigos = $_POST['linea_codigo'] ?? [];
        $cantidades = $_POST['linea_cantidad'] ?? [];

        if (is_array($codigos)) {
            foreach ($codigos as $i => $codigo) {
                $codigo = trim($codigo);
                $item = $codigo !== '' ? $this->inventarioModel->findByCodigo($codigo) : null;
                $lineas[] = [
                    'codigo'   => $codigo,
                    'cantidad' => trim($cantidades[$i] ?? ''),
                    'elemento' => $item['Elemento'] ?? '',
                    'stock'    => isset($item['Cantidad']) ? (int) $item['Cantidad'] : null,
                ];
            }
        }

        return [
            'fecha'       => trim($_POST['fecha'] ?? date('Y-m-d')),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'lineas'      => $lineas,
        ];
    }

    private function validateForm(array $form): array
    {
        $errors = [];
        if ($form['fecha'] === '') {
            $errors[] = 'La fecha es obligatoria.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['fecha'])) {
            $errors[] = 'La fecha no es válida.';
        }
        return $errors;
    }

    private function parseLineas(array $lineasForm): array
    {
        $lineas = [];
        $errors = [];
        $codigosVistos = [];

        foreach ($lineasForm as $index => $row) {
            $codigo = trim($row['codigo'] ?? '');
            $cantidad = (int) ($row['cantidad'] ?? 0);

            if ($codigo === '' && $cantidad === 0) {
                continue;
            }

            if ($codigo === '') {
                $errors[] = 'Complete el elemento en la línea ' . ($index + 1) . '.';
                continue;
            }

            if ($cantidad <= 0) {
                $errors[] = 'La cantidad debe ser mayor a cero en la línea ' . ($index + 1) . '.';
                continue;
            }

            if (isset($codigosVistos[$codigo])) {
                $errors[] = 'El elemento ' . $codigo . ' está repetido en la solicitud.';
                continue;
            }
            $codigosVistos[$codigo] = true;

            $item = $this->inventarioModel->findByCodigo($codigo);
            if (!$item) {
                $errors[] = 'El elemento ' . $codigo . ' no existe en inventario.';
                continue;
            }
            if ($item['Estado'] !== 'Activo') {
                $errors[] = 'El elemento ' . $codigo . ' no está activo.';
                continue;
            }
            if ((int) $item['Cantidad'] < $cantidad) {
                $errors[] = 'Cantidad insuficiente para ' . $codigo . '. Disponible: ' . (int) $item['Cantidad'];
                continue;
            }

            $lineas[] = ['codigo' => $codigo, 'cantidad' => $cantidad];
        }

        if ($errors) {
            throw new InvalidArgumentException(implode('|', $errors));
        }

        return $lineas;
    }
}
