<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/InventarioModel.php';
require_once __DIR__ . '/../models/MovimientoModel.php';
require_once __DIR__ . '/../models/CuentadanteModel.php';
require_once __DIR__ . '/../models/CategoriaModel.php';

class MovimientosController extends Controller
{
    private InventarioModel $inventarioModel;
    private MovimientoModel $movimientoModel;
    private CuentadanteModel $cuentadanteModel;
    private CategoriaModel $categoriaModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin();
        $this->inventarioModel = new InventarioModel();
        $this->movimientoModel = new MovimientoModel();
        $this->cuentadanteModel = new CuentadanteModel();
        $this->categoriaModel = new CategoriaModel();
    }

    public function ingreso(): void
    {
        $this->view('movimientos/ingreso', [
            'pageTitle'    => 'Ingreso de inventario',
            'elementos'    => $this->inventarioModel->getActivos(),
            'categorias'   => $this->categoriaModel->getActivas(),
            'form'         => $this->defaultIngresoFormData(),
            'errors'       => [],
        ]);
    }

    public function ingresoStore(): void
    {
        $form = $this->getIngresoFormData();
        $errors = $this->validateIngreso($form);
        $lineas = [];

        try {
            $lineas = $this->parseLineasIngreso($form['lineas']);
            if (empty($lineas)) {
                $errors[] = 'Registre al menos un elemento con cantidad mayor a cero.';
            }
        } catch (InvalidArgumentException $e) {
            $errors = array_merge($errors, explode('|', $e->getMessage()));
        }

        if ($errors) {
            $this->view('movimientos/ingreso', [
                'pageTitle'    => 'Ingreso de inventario',
                'elementos'    => $this->inventarioModel->getActivos(),
                'categorias'   => $this->categoriaModel->getActivas(),
                'form'         => $form,
                'errors'       => $errors,
            ]);
            return;
        }

        try {
            $lastId = $this->movimientoModel->registrarIngreso(
                $this->extractIngresoMovimiento($form),
                $lineas
            );
            $this->setFlash('success', 'Ingreso registrado. Cantidad actualizada en inventario.');
            $this->redirect('/movimientos/ingreso/documento?id=' . $lastId);
        } catch (RuntimeException $e) {
            $this->view('movimientos/ingreso', [
                'pageTitle'    => 'Ingreso de inventario',
                'elementos'    => $this->inventarioModel->getActivos(),
                'categorias'   => $this->categoriaModel->getActivas(),
                'form'         => $form,
                'errors'       => [$e->getMessage()],
            ]);
        }
    }

    public function ingresoDocumento(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $ingreso = $this->movimientoModel->findIngresoById($id);
        if (!$ingreso) {
            $this->setFlash('danger', 'Ingreso no encontrado.');
            $this->redirect('/inventario');
            return;
        }

        $detalles = $this->movimientoModel->getDetalleByMovimientoId($id);
        $this->view('movimientos/ingreso-documento', [
            'pageTitle' => 'Documento de Ingreso #' . $ingreso['Consecutivo'],
            'ingreso' => $ingreso,
            'detalles' => $detalles
        ]);
    }

    public function prestamoDocumento(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $prestamo = $this->movimientoModel->findMovimientoById($id, 'Prestamo');
        if (!$prestamo) {
            $this->setFlash('danger', 'Préstamo no encontrado.');
            $this->redirect('/inventario');
            return;
        }

        $detalles = $this->movimientoModel->getDetalleByMovimientoId($id);
        $this->view('movimientos/prestamo-documento', [
            'pageTitle' => 'Documento de Préstamo #' . $prestamo['Consecutivo'],
            'prestamo' => $prestamo,
            'detalles' => $detalles
        ]);
    }

    public function devolucionDocumento(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $devolucion = $this->movimientoModel->findMovimientoById($id, 'Devolucion');
        if (!$devolucion) {
            $this->setFlash('danger', 'Devolución no encontrada.');
            $this->redirect('/inventario');
            return;
        }

        $detalles = $this->movimientoModel->getDetalleByMovimientoId($id);
        $this->view('movimientos/devolucion-documento', [
            'pageTitle' => 'Documento de Devolución #' . $devolucion['Consecutivo'],
            'devolucion' => $devolucion,
            'detalles' => $detalles
        ]);
    }

    public function prestamo(): void
    {
        $this->view('movimientos/prestamo', [
            'pageTitle'    => 'Préstamo de inventario',
            'elementos'    => $this->inventarioModel->getActivosConStock(),
            'cuentadantes' => $this->cuentadanteModel->getActivos(),
            'form'         => $this->defaultPrestamoFormData(),
            'errors'       => [],
        ]);
    }

    public function prestamoStore(): void
    {
        $form = $this->getPrestamoFormData();
        $errors = $this->validatePrestamo($form);
        $lineas = [];

        try {
            $lineas = $this->parseLineasPrestamo($form['lineas']);
            if (empty($lineas)) {
                $errors[] = 'Registre al menos un elemento con cantidad mayor a cero.';
            }
        } catch (InvalidArgumentException $e) {
            $errors = array_merge($errors, explode('|', $e->getMessage()));
        }

        if ($errors) {
            $this->view('movimientos/prestamo', [
                'pageTitle'    => 'Préstamo de inventario',
                'elementos'    => $this->inventarioModel->getActivosConStock(),
                'cuentadantes' => $this->cuentadanteModel->getActivos(),
                'form'         => $form,
                'errors'       => $errors,
            ]);
            return;
        }

        try {
            $this->movimientoModel->registrarPrestamo(
                $this->extractPrestamoMovimiento($form),
                $lineas
            );
            $this->setFlash('success', 'Préstamo registrado. Cantidad actualizada en inventario.');
            $this->redirect('/inventario');
        } catch (RuntimeException $e) {
            $this->view('movimientos/prestamo', [
                'pageTitle'    => 'Préstamo de inventario',
                'elementos'    => $this->inventarioModel->getActivosConStock(),
                'cuentadantes' => $this->cuentadanteModel->getActivos(),
                'form'         => $form,
                'errors'       => [$e->getMessage()],
            ]);
        }
    }

    public function index(): void
    {
        $tipo = trim($_GET['tipo'] ?? '');
        $filters = [
            'tipo'               => in_array($tipo, ['Ingreso', 'Prestamo', 'Devolucion', 'Dar_Baja', 'DarDeBaja'], true) ? $tipo : '',
            'cedula_cuentadante' => trim($_GET['cuentadante'] ?? ''),
            'estado'             => trim($_GET['estado'] ?? ''),
        ];

        $this->view('movimientos/index', [
            'pageTitle'    => 'Movimientos de inventario',
            'items'        => $this->movimientoModel->getMovimientos($filters),
            'cuentadantes' => $this->cuentadanteModel->getAll(),
            'filters'      => $filters,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function listPrestamos(): void
    {
        $filters = [
            'cedula_cuentadante' => trim($_GET['cuentadante'] ?? ''),
            'estado'             => trim($_GET['estado'] ?? ''),
        ];

        $this->view('movimientos/prestamos_list', [
            'pageTitle'    => 'Préstamos realizados',
            'items'        => $this->movimientoModel->getPrestamos($filters),
            'cuentadantes' => $this->cuentadanteModel->getAll(),
            'filters'      => $filters,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function updateEstadoPrestamo(): void
    {
        $id = (int) ($_POST['id_movimiento'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');
        $redirectTo = trim($_POST['redirect'] ?? '');

        try {
            if ($id <= 0) {
                throw new RuntimeException('Préstamo no válido.');
            }
            if (!$this->movimientoModel->updateEstadoPrestamo($id, $estado)) {
                throw new RuntimeException('No se pudo actualizar el estado del préstamo.');
            }
            $this->setFlash('success', 'Estado del préstamo actualizado.');
        } catch (RuntimeException $e) {
            $this->setFlash('danger', $e->getMessage());
        }

        if ($redirectTo === 'devolucion') {
            $this->redirect('/prestamos/devolucion?id=' . $id);
            return;
        }

        $this->redirect($this->prestamosListPath());
    }

    public function devolucion(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $prestamo = $id > 0 ? $this->movimientoModel->findPrestamoById($id) : null;

        if (!$prestamo) {
            $this->setFlash('danger', 'Préstamo no encontrado.');
            $this->redirect('/prestamos');
            return;
        }

        $lineas = $this->movimientoModel->getLineasPendientes($id);
        $pendienteTotal = array_sum(array_column($lineas, 'pendiente'));

        if ($pendienteTotal === 0) {
            $this->movimientoModel->sincronizarEstadoPrestamo($id);
            $prestamo = $this->movimientoModel->findPrestamoById($id) ?? $prestamo;
        }

        $this->view('movimientos/devolucion', [
            'pageTitle'      => ($pendienteTotal > 0 ? 'Devolución' : 'Gestión') . ' de préstamo #' . $id,
            'prestamo'       => $prestamo,
            'lineas'         => $lineas,
            'pendienteTotal' => $pendienteTotal,
            'form'           => [
                'descripcion' => '',
            ],
            'errors'         => [],
            'flash'          => $this->getFlash(),
        ]);
    }

    public function devolucionStore(): void
    {
        $id = (int) ($_POST['id_movimiento'] ?? 0);
        $prestamo = $id > 0 ? $this->movimientoModel->findPrestamoById($id) : null;

        if (!$prestamo) {
            $this->setFlash('danger', 'Préstamo no encontrado.');
            $this->redirect('/prestamos');
            return;
        }

        $descripcion = trim($_POST['descripcion'] ?? '');
        $codigos = $_POST['linea_codigo'] ?? [];
        $cantidades = $_POST['linea_cantidad'] ?? [];
        $lineas = [];

        if (is_array($codigos)) {
            foreach ($codigos as $i => $codigo) {
                $lineas[] = [
                    'codigo'   => trim($codigo),
                    'cantidad' => (int) ($cantidades[$i] ?? 0),
                ];
            }
        }

        try {
            $this->movimientoModel->registrarDevolucion($id, $lineas, $descripcion ?: null);
            $this->movimientoModel->sincronizarEstadoPrestamo($id);
            $mensaje = $this->movimientoModel->tienePendiente($id)
                ? 'Devolución registrada. Inventario actualizado.'
                : 'Devolución registrada. El préstamo quedó cerrado.';
            $this->setFlash('success', $mensaje);
            $this->redirect('/prestamos');
        } catch (RuntimeException $e) {
            $lineasError = $this->movimientoModel->getLineasPendientes($id);
            $this->view('movimientos/devolucion', [
                'pageTitle'      => 'Devolución de préstamo #' . $id,
                'prestamo'       => $prestamo,
                'lineas'         => $lineasError,
                'pendienteTotal' => array_sum(array_column($lineasError, 'pendiente')),
                'form'           => ['descripcion' => $descripcion],
                'errors'         => [$e->getMessage()],
                'flash'          => null,
            ]);
        }
    }

    public function darDeBaja(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $prestamo = $id > 0 ? $this->movimientoModel->findPrestamoById($id) : null;

        if (!$prestamo) {
            $this->setFlash('danger', 'Préstamo no encontrado.');
            $this->redirect('/prestamos');
            return;
        }

        $lineas = $this->movimientoModel->getLineasPendientes($id);
        $pendienteTotal = array_sum(array_column($lineas, 'pendiente'));

        if ($pendienteTotal === 0) {
            $this->setFlash('danger', 'No hay elementos pendientes por dar de baja.');
            $this->redirect('/prestamos');
            return;
        }

        $this->view('movimientos/dar-de-baja', [
            'pageTitle'      => 'Dar de baja préstamo #' . $prestamo['Consecutivo'],
            'prestamo'       => $prestamo,
            'lineas'         => $lineas,
            'pendienteTotal' => $pendienteTotal,
            'form'           => ['descripcion' => ''],
            'errors'         => [],
            'flash'          => $this->getFlash(),
        ]);
    }

    public function darDeBajaStore(): void
    {
        $id = (int) ($_POST['id_movimiento'] ?? 0);
        $prestamo = $id > 0 ? $this->movimientoModel->findPrestamoById($id) : null;

        if (!$prestamo) {
            $this->setFlash('danger', 'Préstamo no encontrado.');
            $this->redirect('/prestamos');
            return;
        }

        $descripcion = trim($_POST['descripcion'] ?? '');
        $codigos = $_POST['linea_codigo'] ?? [];
        $cantidades = $_POST['linea_cantidad'] ?? [];
        $lineas = [];

        if (is_array($codigos)) {
            foreach ($codigos as $i => $codigo) {
                $lineas[] = [
                    'codigo'   => trim($codigo),
                    'cantidad' => (int) ($cantidades[$i] ?? 0),
                ];
            }
        }

        if (empty($descripcion)) {
            $lineasError = $this->movimientoModel->getLineasPendientes($id);
            $this->view('movimientos/dar-de-baja', [
                'pageTitle'      => 'Dar de baja préstamo #' . $prestamo['Consecutivo'],
                'prestamo'       => $prestamo,
                'lineas'         => $lineasError,
                'pendienteTotal' => array_sum(array_column($lineasError, 'pendiente')),
                'form'           => ['descripcion' => $descripcion],
                'errors'         => ['Las observaciones son obligatorias.'],
                'flash'          => null,
            ]);
            return;
        }

        try {
            $fotos = $this->handleDarBajaUploads($id);
            $idMovimiento = $this->movimientoModel->registrarDarDeBaja($id, $lineas, $descripcion, $fotos);
            $this->movimientoModel->sincronizarEstadoPrestamo($id);
            $this->redirect('/prestamos/dar-de-baja/documento?id=' . $idMovimiento);
        } catch (RuntimeException $e) {
            $lineasError = $this->movimientoModel->getLineasPendientes($id);
            $this->view('movimientos/dar-de-baja', [
                'pageTitle'      => 'Dar de baja préstamo #' . $prestamo['Consecutivo'],
                'prestamo'       => $prestamo,
                'lineas'         => $lineasError,
                'pendienteTotal' => array_sum(array_column($lineasError, 'pendiente')),
                'form'           => ['descripcion' => $descripcion],
                'errors'         => [$e->getMessage()],
                'flash'          => null,
            ]);
        }
    }

    public function darDeBajaDocumento(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $movimiento = $this->movimientoModel->findDarDeBajaById($id);
        if (!$movimiento) {
            $this->setFlash('danger', 'Movimiento no encontrado.');
            $this->redirect('/movimientos');
            return;
        }

        $detalles = $this->movimientoModel->getDetalleByMovimientoId($id);
        $fotos = $this->movimientoModel->getFotosByMovimientoId($id);

        $this->view('movimientos/dar-de-baja-documento', [
            'pageTitle' => 'Documento de Dar de Baja #' . $movimiento['Consecutivo'],
            'movimiento' => $movimiento,
            'detalles' => $detalles,
            'fotos' => $fotos,
        ], layout: false);
    }

    public function devolucionTotal(): void
    {
        $id = (int) ($_POST['id_movimiento'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');

        try {
            if ($id <= 0) {
                throw new RuntimeException('Préstamo no válido.');
            }
            $this->movimientoModel->registrarDevolucionTotal(
                $id,
                $descripcion ?: 'Devolución total del préstamo #' . $id
            );
            $this->setFlash('success', 'Devolución total registrada. El préstamo quedó cerrado.');
        } catch (RuntimeException $e) {
            $this->setFlash('danger', $e->getMessage());
        }

        $this->redirect($this->prestamosListPath());
    }

    private function prestamosListPath(): string
    {
        $params = [];
        $cuentadante = trim($_POST['filter_cuentadante'] ?? $_GET['cuentadante'] ?? '');
        $estado = trim($_POST['filter_estado'] ?? $_GET['estado'] ?? '');

        if ($cuentadante !== '') {
            $params['cuentadante'] = $cuentadante;
        }
        if ($estado !== '') {
            $params['estado'] = $estado;
        }

        if (empty($params)) {
            return '/prestamos';
        }

        return '/prestamos?' . http_build_query($params);
    }

    private function defaultPrestamoFormData(): array
    {
        return [
            'fecha'               => date('Y-m-d'),
            'cedula_cuentadante'  => '',
            'nombres_cuentadante' => '',
            'descripcion'         => '',
            'lineas'              => [],
        ];
    }

    private function getPrestamoFormData(): array
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
            'fecha'               => trim($_POST['fecha'] ?? date('Y-m-d')),
            'cedula_cuentadante'  => trim($_POST['cedula_cuentadante'] ?? ''),
            'nombres_cuentadante' => trim($_POST['nombres_cuentadante'] ?? ''),
            'descripcion'         => trim($_POST['descripcion'] ?? ''),
            'lineas'              => $lineas,
        ];
    }

    private function parseLineasPrestamo(array $lineasForm): array
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
                $errors[] = 'El elemento ' . $codigo . ' está repetido en el préstamo.';
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
                $errors[] = 'Stock insuficiente para ' . $codigo . '. Disponible: ' . (int) $item['Cantidad'];
                continue;
            }
            if ((int) $item['Cantidad'] === 0) {
                $errors[] = 'El elemento ' . $codigo . ' no tiene stock disponible.';
                continue;
            }

            $lineas[] = [
                'codigo'   => $codigo,
                'cantidad' => $cantidad,
            ];
        }

        if ($errors) {
            throw new InvalidArgumentException(implode('|', $errors));
        }

        return $lineas;
    }

    private function validatePrestamo(array $form): array
    {
        return $this->validateIngreso($form);
    }

    private function extractPrestamoMovimiento(array $form): array
    {
        return [
            'fecha'              => $form['fecha'],
            'cedula_cuentadante' => $form['cedula_cuentadante'],
            'descripcion'        => $form['descripcion'],
        ];
    }

    private function defaultIngresoFormData(): array
    {
        return [
            'fecha'             => date('Y-m-d'),
            'descripcion'         => '',
            'lineas'              => [],
        ];
    }

    private function getIngresoFormData(): array
    {
        $lineas = [];
        $codigos = $_POST['linea_codigo'] ?? [];
        $cantidades = $_POST['linea_cantidad'] ?? [];
        $nuevos = $_POST['linea_nuevo'] ?? [];
        $elementosN = $_POST['linea_elemento_nuevo'] ?? [];
        $categoriasN = $_POST['linea_categoria_nuevo'] ?? [];
        $descripcionesN = $_POST['linea_descripcion_nuevo'] ?? [];

        if (is_array($codigos)) {
            foreach ($codigos as $i => $codigo) {
                $codigo = trim($codigo);
                $esNuevo = ($nuevos[$i] ?? '0') === '1';
                $linea = [
                    'codigo'            => $codigo,
                    'cantidad'          => trim($cantidades[$i] ?? ''),
                    'elemento_nuevo'    => trim($elementosN[$i] ?? ''),
                    'categoria_nuevo'   => (int) ($categoriasN[$i] ?? 0),
                    'descripcion_nuevo' => trim($descripcionesN[$i] ?? ''),
                ];

                if ($esNuevo) {
                    $linea['elemento'] = $linea['elemento_nuevo'];
                    $linea['stock'] = 0;
                    $linea['nuevo'] = true;
                } else {
                    $item = $this->inventarioModel->findByCodigo($codigo);
                    $linea['elemento'] = $item['Elemento'] ?? '';
                    $linea['stock'] = isset($item['Cantidad']) ? (int) $item['Cantidad'] : null;
                }

                $lineas[] = $linea;
            }
        }

        return [
            'fecha'              => trim($_POST['fecha'] ?? date('Y-m-d')),
            'descripcion'         => trim($_POST['descripcion'] ?? ''),
            'lineas'              => $lineas,
        ];
    }

    private function parseLineasIngreso(array $lineasForm): array
    {
        $lineas = [];
        $errors = [];
        $codigosVistos = [];

        foreach ($lineasForm as $index => $row) {
            $codigo = trim($row['codigo'] ?? '');
            $cantidad = (int) ($row['cantidad'] ?? 0);
            $esNuevo = !empty($row['nuevo']);

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
                $errors[] = 'El elemento ' . $codigo . ' está repetido en el ingreso.';
                continue;
            }
            $codigosVistos[$codigo] = true;

            if ($esNuevo) {
                if ($this->inventarioModel->findByCodigo($codigo)) {
                    $errors[] = 'El código ' . $codigo . ' ya existe en inventario.';
                    continue;
                }
                $lineas[] = [
                    'codigo'   => $codigo,
                    'cantidad' => $cantidad,
                    'nuevo'    => [
                        'codigo'       => $codigo,
                        'elemento'     => $row['elemento_nuevo'] ?? $row['elemento'] ?? '',
                        'id_categoria' => (int) ($row['categoria_nuevo'] ?? 0) ?: $this->categoriaModel->getDefaultId(),
                        'descripcion'  => $row['descripcion_nuevo'] ?? '',
                    ],
                ];
                continue;
            }

            $item = $this->inventarioModel->findByCodigo($codigo);
            if (!$item) {
                $errors[] = 'El elemento ' . $codigo . ' no existe.';
                continue;
            }
            if ($item['Estado'] !== 'Activo') {
                $errors[] = 'El elemento ' . $codigo . ' no está activo.';
                continue;
            }

            $lineas[] = [
                'codigo'   => $codigo,
                'cantidad' => $cantidad,
            ];
        }

        if ($errors) {
            throw new InvalidArgumentException(implode('|', $errors));
        }

        return $lineas;
    }

    private function validateIngreso(array $form): array
    {
        $errors = [];

        if ($form['fecha'] === '') {
            $errors[] = 'La fecha es obligatoria.';
        }

        return $errors;
    }

    private function extractIngresoMovimiento(array $form): array
    {
        $user = Auth::user();
        return [
            'fecha'              => $form['fecha'],
            'cedula_cuentadante' => $user['cedula'],
            'descripcion'        => $form['descripcion'],
        ];
    }

    private function handleDarBajaUploads(int $idPrestamo): array
    {
        if (empty($_FILES['fotos']['name'][0])) {
            throw new RuntimeException('Debe adjuntar al menos una fotografía del elemento.');
        }

        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
        $rutas = [];
        $files = $_FILES['fotos'];
        $count = is_array($files['name']) ? count($files['name']) : 0;

        if ($count > 3) {
            throw new RuntimeException('Máximo 3 fotografías permitidas.');
        }

        for ($i = 0; $i < $count; $i++) {
            if (empty($files['name'][$i]) || $files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!isset($allowed[$ext])) {
                throw new RuntimeException('Formato de imagen no permitido: ' . $files['name'][$i]);
            }

            $objectPath = 'dar-baja/baja_' . $idPrestamo . '_' . time() . '_' . $i . '.' . $ext;
            $rutas[] = Database::upload($objectPath, $files['tmp_name'][$i], $allowed[$ext]);
        }

        if (empty($rutas)) {
            throw new RuntimeException('Debe adjuntar al menos una fotografía del elemento.');
        }

        return $rutas;
    }

    public function exportCsv(): void
    {
        $tipo = trim($_GET['tipo'] ?? '');
        $filters = [
            'tipo'               => in_array($tipo, ['Ingreso', 'Prestamo', 'Devolucion', 'Dar_Baja', 'DarDeBaja'], true) ? $tipo : '',
            'cedula_cuentadante' => trim($_GET['cuentadante'] ?? ''),
            'estado'             => trim($_GET['estado'] ?? ''),
        ];

        $movimientos = $this->movimientoModel->getMovimientos($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=movimientos_' . date('YmdHis') . '.csv');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Helper function to get label from type
        $tipoLabel = function (string $tipo): string {
            return match ($tipo) {
                'Ingreso'    => 'Ingreso',
                'Prestamo'   => 'Préstamo',
                'Devolucion' => 'Devolución',
                'DarDeBaja', 'Dar_Baja'  => 'Dar_baja',
                default      => $tipo,
            };
        };

        // Header row
        fputcsv($output, [
            '#',
            'Tipo',
            'Fecha',
            'Cuentadante',
            'Referencia',
            'Elementos',
            'Total unidades',
            'Estado'
        ]);

        foreach ($movimientos as $mov) {
            fputcsv($output, [
                $mov['Consecutivo'],
                $tipoLabel($mov['Tipo']),
                date('d/m/Y', strtotime($mov['Fecha'])),
                $mov['Cuentadante'] ? $mov['Cuentadante'] . ' (' . $mov['Cedula_cuentadante'] . ')' : '—',
                $mov['Consecutivo_ref'] ? 'Préstamo #' . $mov['Consecutivo_ref'] : '—',
                $mov['Elementos'] ?? '—',
                (int)($mov['Total_unidades'] ?? 0),
                $mov['Estado']
            ]);
        }

        fclose($output);
        exit;
    }
}
