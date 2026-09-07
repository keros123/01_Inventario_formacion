<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/CategoriaModel.php';

class CategoriasController extends Controller
{
    private CategoriaModel $model;

    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin();
        $this->model = new CategoriaModel();
    }

    public function index(): void
    {
        $this->view('categorias/index', [
            'pageTitle' => 'Categorías',
            'items'     => $this->model->getAll(),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function create(): void
    {
        $this->view('categorias/form', [
            'pageTitle' => 'Nueva categoría',
            'item'      => null,
            'action'    => 'store',
        ]);
    }

    public function store(): void
    {
        $data = $this->getFormData();
        $errors = $this->validate($data);

        if ($errors) {
            $this->view('categorias/form', [
                'pageTitle' => 'Nueva categoría',
                'item'      => $data,
                'action'    => 'store',
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->create($data);
        $this->setFlash('success', 'Categoría registrada correctamente.');
        $this->redirect('/categorias');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $item = $this->model->findById($id);

        if (!$item || $item['Estado'] === 'Eliminado') {
            $this->setFlash('danger', 'Categoría no encontrada.');
            $this->redirect('/categorias');
        }

        $this->view('categorias/form', [
            'pageTitle' => 'Editar categoría',
            'item'      => $item,
            'action'    => 'update',
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id_original'] ?? 0);
        $item = $this->model->findById($id);

        if (!$item || $item['Estado'] === 'Eliminado') {
            $this->setFlash('danger', 'Categoría no encontrada.');
            $this->redirect('/categorias');
        }

        $data = $this->getFormData();
        $errors = $this->validate($data);

        if ($errors) {
            $this->view('categorias/form', [
                'pageTitle' => 'Editar categoría',
                'item'      => array_merge($item, $data),
                'action'    => 'update',
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->update($id, $data);
        $this->setFlash('success', 'Categoría actualizada correctamente.');
        $this->redirect('/categorias');
    }

    public function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($this->model->countItems($id) > 0) {
                $this->setFlash('danger', 'No se puede eliminar: hay elementos asignados a esta categoría.');
            } else {
                $this->model->delete($id);
                $this->setFlash('success', 'Categoría eliminada.');
            }
        }
        $this->redirect('/categorias');
    }

    private function getFormData(): array
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'estado' => $_POST['estado'] ?? 'Activo',
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if ($data['nombre'] === '') {
            $errors[] = 'El nombre es obligatorio.';
        }
        return $errors;
    }
}
