<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/InventarioModel.php';
require_once __DIR__ . '/../models/MovimientoModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/SolicitudPrestamoModel.php';
require_once __DIR__ . '/../models/AgendaComputadorModel.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $inventarioModel = new InventarioModel();
        $movimientoModel = new MovimientoModel();
        $usuarioModel = new UsuarioModel();
        $solicitudModel = new SolicitudPrestamoModel();
        $agendaModel = new AgendaComputadorModel();
        $user = Auth::user();
        $esAdmin = Auth::isAdmin();

        $data = [
            'pageTitle'       => 'Panel principal',
            'esAdmin'         => $esAdmin,
            'totalInventario' => $inventarioModel->count(),
        ];

        if ($esAdmin) {
            $data['totalPrestamosActivos'] = $movimientoModel->countPrestamosActivos();
            $data['totalUsuarios'] = count($usuarioModel->getAll());
            $data['solicitudesPendientes'] = $solicitudModel->countPendientes();
            $data['agendaPendientes'] = $agendaModel->countPendientes();
        } else {
            $data['misSolicitudesPendientes'] = $solicitudModel->countBySolicitante($user['cedula'], 'Pendiente');
            $data['misAgendaPendientes'] = $agendaModel->countBySolicitante($user['cedula'], 'Pendiente')
                + $agendaModel->countBySolicitante($user['cedula'], 'Reprogramada');
            $data['misPrestamosActivos'] = $movimientoModel->countPrestamosActivosPorCedula($user['cedula']);
        }

        $this->view('dashboard/index', $data);
    }
}
