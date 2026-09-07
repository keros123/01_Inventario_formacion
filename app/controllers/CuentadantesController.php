<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';

class CuentadantesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin();
    }

    public function index(): void
    {
        $this->setFlash('info', 'Los cuentadantes ahora se gestionan como usuarios del sistema.');
        $this->redirect('/usuarios');
    }

    public function create(): void
    {
        $this->redirect('/usuarios/create');
    }

    public function store(): void
    {
        $this->redirect('/usuarios/create');
    }

    public function edit(): void
    {
        $cedula = $_GET['cedula'] ?? '';
        $this->redirect('/usuarios/edit?cedula=' . urlencode($cedula));
    }

    public function update(): void
    {
        $this->redirect('/usuarios');
    }

    public function delete(): void
    {
        $this->redirect('/usuarios');
    }
}
