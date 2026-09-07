<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuariosController extends Controller
{
    private UsuarioModel $model;

    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin();
        $this->model = new UsuarioModel();
    }

    public function index(): void
    {
        $this->view('usuarios/index', [
            'pageTitle' => 'Usuarios',
            'items'     => $this->model->getAll(),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function create(): void
    {
        $this->view('usuarios/form', [
            'pageTitle' => 'Nuevo usuario',
            'item'      => null,
            'action'    => 'store',
        ]);
    }

    public function store(): void
    {
        $data = $this->getFormData();
        $errors = $this->validate($data, true);

        if ($this->model->findByCedula($data['cedula'])) {
            $errors[] = 'La cédula ya está registrada.';
        }

        if ($errors) {
            $this->view('usuarios/form', [
                'pageTitle' => 'Nuevo usuario',
                'item'      => $data,
                'action'    => 'store',
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->create($data);
        $this->setFlash('success', 'Usuario registrado correctamente.');
        $this->redirect('/usuarios');
    }

    public function edit(): void
    {
        $cedula = $_GET['cedula'] ?? '';
        $item = $this->model->findByCedula($cedula);

        if (!$item) {
            $this->setFlash('danger', 'Usuario no encontrado.');
            $this->redirect('/usuarios');
        }

        unset($item['Password']);
        $this->view('usuarios/form', [
            'pageTitle' => 'Editar usuario',
            'item'      => $item,
            'action'    => 'update',
        ]);
    }

    public function update(): void
    {
        $cedula = $_POST['cedula_original'] ?? '';
        $item = $this->model->findByCedula($cedula);

        if (!$item) {
            $this->setFlash('danger', 'Usuario no encontrado.');
            $this->redirect('/usuarios');
        }

        $data = $this->getFormData(false);
        $errors = $this->validate($data, false);

        if ($errors) {
            $this->view('usuarios/form', [
                'pageTitle' => 'Editar usuario',
                'item'      => array_merge($item, $data),
                'action'    => 'update',
                'errors'    => $errors,
            ]);
            return;
        }

        $this->model->update($cedula, $data);
        $this->setFlash('success', 'Usuario actualizado correctamente.');
        $this->redirect('/usuarios');
    }

    public function delete(): void
    {
        $cedula = $_POST['cedula'] ?? '';
        $current = Auth::user();

        if ($cedula && $current && $cedula === $current['cedula']) {
            $this->setFlash('danger', 'No puede eliminar su propio usuario.');
            $this->redirect('/usuarios');
        }

        if ($cedula) {
            $this->model->delete($cedula);
            $this->setFlash('success', 'Usuario eliminado.');
        }
        $this->redirect('/usuarios');
    }

    private function getFormData(bool $includeCedula = true): array
    {
        $data = [
            'nombres'  => trim($_POST['nombres'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'tipo'     => $_POST['tipo'] ?? 'Usuario',
            'estado'   => $_POST['estado'] ?? 'Activo',
        ];
        if ($includeCedula) {
            $data['cedula'] = trim($_POST['cedula'] ?? '');
        }
        return $data;
    }

    private function validate(array $data, bool $isNew): array
    {
        $errors = [];
        if ($isNew && ($data['cedula'] ?? '') === '') {
            $errors[] = 'La cédula es obligatoria.';
        }
        if ($data['nombres'] === '') {
            $errors[] = 'Los nombres son obligatorios.';
        }
        if ($isNew && $data['password'] === '') {
            $errors[] = 'La contraseña es obligatoria para nuevos usuarios.';
        }
        return $errors;
    }
}
