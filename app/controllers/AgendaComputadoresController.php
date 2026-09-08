<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/AgendaComputadorModel.php';

class AgendaComputadoresController extends Controller
{
    private AgendaComputadorModel $model;

    public function __construct()
    {
        parent::__construct();
        Auth::requireLogin();
        $this->model = new AgendaComputadorModel();
    }

    public function index(): void
    {
        $vista = ($_GET['vista'] ?? 'mes') === 'semana' ? 'semana' : 'mes';
        $year = max(2000, min(2100, (int) ($_GET['year'] ?? date('Y'))));
        $month = max(1, min(12, (int) ($_GET['month'] ?? date('n'))));
        $fechaRef = trim($_GET['fecha'] ?? '');
        if ($fechaRef === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRef)) {
            $fechaRef = sprintf('%04d-%02d-%02d', $year, $month, (int) date('j'));
        }

        if ($vista === 'semana') {
            $rango = $this->rangoSemana($fechaRef);
            $inicio = $rango['inicio'];
            $fin = $rango['fin'];
            $year = (int) date('Y', strtotime($inicio));
            $month = (int) date('n', strtotime($inicio));
        } else {
            $inicio = sprintf('%04d-%02d-01', $year, $month);
            $fin = date('Y-m-t', strtotime($inicio));
        }

        $this->view('agenda-computadores/calendario', [
            'pageTitle'  => 'Agenda computadores',
            'vista'      => $vista,
            'year'       => $year,
            'month'      => $month,
            'fechaRef'   => $fechaRef,
            'inicio'     => $inicio,
            'fin'        => $fin,
            'solicitudes'=> $this->model->getForRange($inicio, $fin),
            'flash'      => $this->getFlash(),
            'puedeCrear' => true,
        ]);
    }

    public function nueva(): void
    {
        $this->view('agenda-computadores/form', [
            'pageTitle' => 'Solicitud de computadores',
            'form'      => $this->formDefaults(),
            'errors'    => [],
        ]);
    }

    public function store(): void
    {
        $form = $this->getFormData();
        $errors = $this->validateForm($form);

        if ($errors) {
            $this->view('agenda-computadores/form', [
                'pageTitle' => 'Solicitud de computadores',
                'form'      => $form,
                'errors'    => $errors,
            ]);
            return;
        }

        $user = Auth::user();

        try {
            $id = $this->model->create([
                'cedula'          => $user['cedula'],
                'nombres'         => $form['nombres'],
                'telefono'        => $form['telefono'],
                'fecha'           => $form['fecha'],
                'cantidad'        => (int) $form['cantidad'],
                'sede'            => $form['sede'],
                'hora_inicio'     => $form['hora_inicio'],
                'hora_final'      => $form['hora_final'],
                'ficha_programa'  => $form['ficha_programa'],
            ]);
            $this->setFlash('success', 'Solicitud #' . $id . ' enviada. Un administrador debe aprobarla.');
            $this->redirect('/agenda-computadores/mis');
        } catch (Throwable $e) {
            $this->view('agenda-computadores/form', [
                'pageTitle' => 'Solicitud de computadores',
                'form'      => $form,
                'errors'    => [$e->getMessage()],
            ]);
        }
    }

    public function mis(): void
    {
        $user = Auth::user();
        $this->view('agenda-computadores/mis', [
            'pageTitle'   => 'Mis solicitudes de computadores',
            'solicitudes' => $this->model->getBySolicitante($user['cedula']),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function pendientes(): void
    {
        Auth::requireAdmin();
        $this->view('agenda-computadores/pendientes', [
            'pageTitle'   => 'Agenda computadores pendientes',
            'solicitudes' => $this->model->getPendientes(),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function ver(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $solicitud = $this->model->findById($id);

        if (!$solicitud) {
            $this->setFlash('danger', 'Solicitud no encontrada.');
            $this->redirect('/agenda-computadores');
        }

        $this->view('agenda-computadores/detalle', [
            'pageTitle' => 'Agenda computadores #' . $id,
            'solicitud' => $solicitud,
            'flash'     => $this->getFlash(),
        ]);
    }

    public function aprobar(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $user = Auth::user();

        try {
            $this->model->aprobar($id, $user['cedula']);
            $this->setFlash('success', 'Solicitud de computadores aprobada.');
        } catch (Throwable $e) {
            $this->setFlash('danger', $e->getMessage());
        }
        $this->redirect('/agenda-computadores/ver?id=' . $id);
    }

    public function rechazar(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $user = Auth::user();

        if ($motivo === '') {
            $this->setFlash('danger', 'Indique el motivo del rechazo.');
            $this->redirect('/agenda-computadores/ver?id=' . $id);
        }

        if ($this->model->rechazar($id, $user['cedula'], $motivo)) {
            $this->setFlash('success', 'Solicitud de computadores rechazada.');
        } else {
            $this->setFlash('danger', 'No se pudo rechazar la solicitud.');
        }
        $this->redirect('/agenda-computadores');
    }

    public function reprogramar(): void
    {
        Auth::requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $datos = [
            'fecha'       => trim($_POST['fecha'] ?? ''),
            'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
            'hora_final'  => trim($_POST['hora_final'] ?? ''),
            'sede'        => trim($_POST['sede'] ?? ''),
            'motivo'      => trim($_POST['motivo'] ?? ''),
        ];
        $errors = $this->validateReprogramacion($datos);

        if ($errors) {
            $this->setFlash('danger', $errors[0]);
            $this->redirect('/agenda-computadores/ver?id=' . $id);
        }

        try {
            $this->model->reprogramar($id, Auth::user()['cedula'], $datos);
            $this->setFlash('success', 'Solicitud reprogramada.');
        } catch (Throwable $e) {
            $this->setFlash('danger', $e->getMessage());
        }
        $this->redirect('/agenda-computadores/ver?id=' . $id);
    }

    public function cancelar(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $user = Auth::user();

        if ($this->model->cancelar($id, $user['cedula'])) {
            $this->setFlash('success', 'Solicitud cancelada.');
        } else {
            $this->setFlash('danger', 'No se pudo cancelar la solicitud.');
        }
        $this->redirect('/agenda-computadores/mis');
    }

    private function formDefaults(): array
    {
        $user = Auth::user();
        $fecha = trim($_GET['fecha'] ?? '');
        if ($fecha !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = '';
        }

        return [
            'nombres'        => $user['nombres'] ?? '',
            'telefono'       => '',
            'fecha'          => $fecha !== '' ? $fecha : date('Y-m-d'),
            'cantidad'       => '1',
            'sede'           => 'Ternera',
            'hora_inicio'    => '07:00',
            'hora_final'     => '12:00',
            'ficha_programa' => '',
        ];
    }

    private function getFormData(): array
    {
        $defaults = $this->formDefaults();
        return [
            'nombres'        => trim($_POST['nombres'] ?? $defaults['nombres']),
            'telefono'       => trim($_POST['telefono'] ?? ''),
            'fecha'          => trim($_POST['fecha'] ?? $defaults['fecha']),
            'cantidad'       => trim($_POST['cantidad'] ?? '1'),
            'sede'           => trim($_POST['sede'] ?? ''),
            'hora_inicio'    => trim($_POST['hora_inicio'] ?? ''),
            'hora_final'     => trim($_POST['hora_final'] ?? ''),
            'ficha_programa' => trim($_POST['ficha_programa'] ?? ''),
        ];
    }

    private function validateForm(array $form): array
    {
        $errors = [];
        if ($form['nombres'] === '') {
            $errors[] = 'Los nombres y apellidos son obligatorios.';
        }
        if ($form['telefono'] === '' || !preg_match('/^[0-9+\s-]{7,20}$/', $form['telefono'])) {
            $errors[] = 'Ingrese un teléfono móvil válido.';
        }
        if ($form['fecha'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['fecha'])) {
            $errors[] = 'La fecha de solicitud no es válida.';
        }
        $cantidad = (int) $form['cantidad'];
        if ($cantidad < 1) {
            $errors[] = 'La cantidad de portátiles debe ser al menos 1.';
        }
        if (!in_array($form['sede'], AgendaComputadorModel::SEDES, true)) {
            $errors[] = 'Seleccione una sede válida (Ternera o Emprender).';
        }
        if (!$this->horaValida($form['hora_inicio']) || !$this->horaValida($form['hora_final'])) {
            $errors[] = 'Las horas de inicio y final son obligatorias.';
        } elseif ($form['hora_final'] <= $form['hora_inicio']) {
            $errors[] = 'La hora final debe ser posterior a la hora de inicio.';
        }
        if ($form['ficha_programa'] === '') {
            $errors[] = 'La ficha y el programa son obligatorios.';
        }
        return $errors;
    }

    private function validateReprogramacion(array $datos): array
    {
        $errors = [];
        if ($datos['fecha'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha'])) {
            $errors[] = 'La nueva fecha no es válida.';
        }
        if (!$this->horaValida($datos['hora_inicio']) || !$this->horaValida($datos['hora_final'])) {
            $errors[] = 'Indique hora de inicio y hora final.';
        } elseif ($datos['hora_final'] <= $datos['hora_inicio']) {
            $errors[] = 'La hora final debe ser posterior a la hora de inicio.';
        }
        if (!in_array($datos['sede'], AgendaComputadorModel::SEDES, true)) {
            $errors[] = 'Seleccione una sede válida.';
        }
        return $errors;
    }

    private function horaValida(string $hora): bool
    {
        return (bool) preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora);
    }

    /** @return array{inicio:string,fin:string} */
    private function rangoSemana(string $fecha): array
    {
        $ts = strtotime($fecha) ?: time();
        $dow = (int) date('N', $ts);
        $lunes = strtotime('-' . ($dow - 1) . ' days', $ts);
        return [
            'inicio' => date('Y-m-d', $lunes),
            'fin'    => date('Y-m-d', strtotime('+6 days', $lunes)),
        ];
    }
}
