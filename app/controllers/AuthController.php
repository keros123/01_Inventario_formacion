<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $error = null;

        if ($this->isPost()) {
            $cedula = trim($_POST['cedula'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($cedula === '' || $password === '') {
                $error = 'Ingrese cédula y contraseña.';
            } else {
                $model = new UsuarioModel();
                $user = $model->findByCedula($cedula);

                if (!$user) {
                    $error = 'Credenciales incorrectas.';
                } elseif ($user['Estado'] !== 'Activo') {
                    $error = 'Su cuenta está inactiva. Contacte al administrador.';
                } elseif (!$model->authenticate($cedula, $password)) {
                    $error = 'Credenciales incorrectas.';
                } else {
                    Auth::login($user);
                    $this->redirect('/dashboard');
                }
            }
        }

        $this->view('auth/login', ['error' => $error, 'pageTitle' => 'Iniciar sesión'], null);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
