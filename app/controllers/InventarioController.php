<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/InventarioModel.php';
require_once __DIR__ . '/../models/CategoriaModel.php';

class InventarioController extends Controller
{
    private InventarioModel $model;
    private CategoriaModel $categoriaModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        $this->model = new InventarioModel();
        $this->categoriaModel = new CategoriaModel();
    }

    public function index(): void
    {
        $busqueda = trim($_GET['q'] ?? '');
        $categoriaId = (int) ($_GET['categoria'] ?? 0) ?: null;

        $this->view('inventario/index', [
            'pageTitle'  => 'Inventario',
            'items'      => $this->model->getAll(
                $busqueda !== '' ? $busqueda : null,
                $categoriaId
            ),
            'categorias' => $this->categoriaModel->getActivas(),
            'busqueda'   => $busqueda,
            'categoriaId'=> $categoriaId,
            'flash'      => $this->getFlash(),
        ]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('inventario/form', [
            'pageTitle'  => 'Nuevo elemento',
            'item'       => null,
            'action'     => 'store',
            'categorias' => $this->categoriaModel->getActivas(),
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $data = $this->getFormData();
        $data['cantidad'] = 0;
        $errors = $this->validate($data);

        if ($this->model->findByCodigo($data['codigo'])) {
            $errors[] = 'El código ya existe.';
        }

        if ($errors) {
            $this->view('inventario/form', [
                'pageTitle'  => 'Nuevo elemento',
                'item'       => $data,
                'action'     => 'store',
                'categorias' => $this->categoriaModel->getActivas(),
                'errors'     => $errors,
            ]);
            return;
        }

        try {
            $data['fotografia'] = $this->handleUpload($data['codigo']);
        } catch (Throwable $e) {
            $this->view('inventario/form', [
                'pageTitle'  => 'Nuevo elemento',
                'item'       => $data,
                'action'     => 'store',
                'categorias' => $this->categoriaModel->getActivas(),
                'errors'     => [$e->getMessage()],
            ]);
            return;
        }

        $this->model->create($data);
        $this->setFlash('success', 'Elemento registrado correctamente.');
        $this->redirect('/inventario');
    }

    public function edit(): void
    {
        Auth::requireAdmin();
        $codigo = $_GET['codigo'] ?? '';
        $item = $this->model->findByCodigo($codigo);

        if (!$item) {
            $this->setFlash('danger', 'Elemento no encontrado.');
            $this->redirect('/inventario');
        }

        $this->view('inventario/form', [
            'pageTitle'  => 'Editar elemento',
            'item'       => $item,
            'action'     => 'update',
            'categorias' => $this->categoriaModel->getActivas(),
        ]);
    }

    public function update(): void
    {
        Auth::requireAdmin();
        $codigo = $_POST['codigo_original'] ?? '';
        $item = $this->model->findByCodigo($codigo);

        if (!$item) {
            $this->setFlash('danger', 'Elemento no encontrado.');
            $this->redirect('/inventario');
        }

        $data = $this->getFormData();
        $errors = $this->validate($data, false);

        if ($errors) {
            $data['Codigo'] = $codigo;
            $this->view('inventario/form', [
                'pageTitle'  => 'Editar elemento',
                'item'       => array_merge($item, $data),
                'action'     => 'update',
                'categorias' => $this->categoriaModel->getActivas(),
                'errors'     => $errors,
            ]);
            return;
        }

        try {
            $foto = $this->handleUpload($codigo);
        } catch (Throwable $e) {
            $data['Codigo'] = $codigo;
            $this->view('inventario/form', [
                'pageTitle'  => 'Editar elemento',
                'item'       => array_merge($item, $data),
                'action'     => 'update',
                'categorias' => $this->categoriaModel->getActivas(),
                'errors'     => [$e->getMessage()],
            ]);
            return;
        }

        $data['fotografia'] = $foto ?: ($item['Fotografia'] ?? $item['fotografia'] ?? null);
        $data['cantidad'] = (int) $item['Cantidad'];
        $this->model->update($codigo, $data);
        $this->setFlash('success', 'Elemento actualizado correctamente.');
        $this->redirect('/inventario');
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        $codigo = $_POST['codigo'] ?? '';
        if ($codigo) {
            $this->model->delete($codigo);
            $this->setFlash('success', 'Elemento eliminado.');
        }
        $this->redirect('/inventario');
    }

    public function exportCsv(): void
    {
        $busqueda = trim($_GET['q'] ?? '');
        $categoriaId = (int) ($_GET['categoria'] ?? 0) ?: null;
        $items = $this->model->getAll($busqueda !== '' ? $busqueda : null, $categoriaId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=inventario_' . date('YmdHis') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Código', 'Elemento', 'Categoría', 'Descripción', 'Cantidad', 'Estado']);

        foreach ($items as $item) {
            fputcsv($output, [
                $item['Codigo'],
                $item['Elemento'],
                $item['Categoria'] ?? '—',
                $item['Descripcion'],
                (int) $item['Cantidad'],
                $item['Estado'],
            ]);
        }

        fclose($output);
        exit;
    }

    private function getFormData(): array
    {
        return [
            'codigo'       => trim($_POST['codigo'] ?? ''),
            'elemento'     => trim($_POST['elemento'] ?? ''),
            'id_categoria' => (int) ($_POST['id_categoria'] ?? 0) ?: $this->categoriaModel->getDefaultId(),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'cantidad'     => (int) ($_POST['cantidad'] ?? 0),
            'estado'       => $_POST['estado'] ?? 'Activo',
        ];
    }

    private function validate(array $data, bool $requireCodigo = true): array
    {
        $errors = [];
        if ($requireCodigo && $data['codigo'] === '') {
            $errors[] = 'El código es obligatorio.';
        }
        if ($data['elemento'] === '') {
            $errors[] = 'El nombre del elemento es obligatorio.';
        }
        if (empty($data['id_categoria'])) {
            $errors[] = 'Seleccione una categoría.';
        }
        if ($data['cantidad'] < 0) {
            $errors[] = 'La cantidad no puede ser negativa.';
        }
        return $errors;
    }

    private function handleUpload(string $codigo): ?string
    {
        $file = $_FILES['fotografia'] ?? null;
        if (!is_array($file) || ($file['name'] ?? '') === '') {
            return null;
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo leer la fotografía (código de subida ' . $error . ').');
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
        if (!isset($allowed[$ext])) {
            throw new RuntimeException('Formato de imagen no permitido. Use JPG, PNG, GIF o WebP.');
        }

        $filename = 'inventario/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $codigo) . '.' . $ext;
        return Database::upload($filename, (string) $file['tmp_name'], $allowed[$ext]);
    }
}
