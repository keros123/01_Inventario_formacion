<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/InventarioModel.php';
require_once __DIR__ . '/../models/MovimientoModel.php';
require_once __DIR__ . '/../models/CategoriaModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class ReportesController extends Controller
{
    private InventarioModel $inventarioModel;
    private MovimientoModel $movimientoModel;
    private CategoriaModel $categoriaModel;
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        Auth::requireAdmin();
        $this->inventarioModel = new InventarioModel();
        $this->movimientoModel = new MovimientoModel();
        $this->categoriaModel = new CategoriaModel();
        $this->usuarioModel = new UsuarioModel();
    }

    public function index(): void
    {
        $filters = $this->getFilters();
        $tipoReporte = $_GET['reporte'] ?? 'movimientos';
        $resultados = [];
        $categorias = [];
        $cuentadantes = [];
        $errors = [];

        try {
            if ($this->hasSearch($filters, $tipoReporte)) {
                $resultados = $tipoReporte === 'inventario'
                    ? $this->inventarioModel->getForReport($filters)
                    : $this->movimientoModel->getReporteMovimientos($filters);
            }
            $categorias = $this->categoriaModel->getActivas();
            $cuentadantes = $this->usuarioModel->getActivosParaPrestamo();
        } catch (RuntimeException $e) {
            $message = $e->getMessage();
            $errors[] = stripos($message, 'timeout') !== false
                ? 'Supabase tardó demasiado en responder. Espere unos segundos e intente de nuevo.'
                : $message;
        }

        $this->view('reportes/index', [
            'pageTitle'    => 'Reportes',
            'filters'      => $filters,
            'tipoReporte'  => $tipoReporte,
            'resultados'   => $resultados,
            'categorias'   => $categorias,
            'cuentadantes' => $cuentadantes,
            'errors'       => $errors,
        ]);
    }

    public function exportCsv(): void
    {
        $filters = $this->getFilters();
        $tipoReporte = $_GET['reporte'] ?? 'movimientos';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_' . $tipoReporte . '_' . date('YmdHis') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if ($tipoReporte === 'inventario') {
            fputcsv($output, ['Código', 'Elemento', 'Categoría', 'Descripción', 'Cantidad', 'Estado']);
            foreach ($this->inventarioModel->getForReport($filters) as $row) {
                fputcsv($output, [
                    $row['Codigo'],
                    $row['Elemento'],
                    $row['Categoria'] ?? '—',
                    $row['Descripcion'],
                    (int) $row['Cantidad'],
                    $row['Estado'],
                ]);
            }
        } else {
            fputcsv($output, [
                '#', 'Tipo', 'Fecha', 'Cuentadante', 'Cédula', 'Categoría', 'Código', 'Elemento',
                'Cantidad', 'Estado movimiento', 'Referencia', 'Descripción',
            ]);
            $tipoLabel = static function (string $tipo): string {
                return match ($tipo) {
                    'Ingreso'    => 'Ingreso',
                    'Prestamo'   => 'Préstamo',
                    'Devolucion' => 'Devolución',
                    'Dar_Baja'   => 'Dar de baja',
                    default      => $tipo,
                };
            };

            foreach ($this->movimientoModel->getReporteMovimientos($filters) as $row) {
                fputcsv($output, [
                    $row['Consecutivo'],
                    $tipoLabel($row['Tipo']),
                    date('d/m/Y', strtotime($row['Fecha'])),
                    $row['Cuentadante'] ?? '—',
                    $row['Cedula_cuentadante'] ?? '—',
                    $row['Categoria'] ?? '—',
                    $row['Codigo_elemento'],
                    $row['Elemento'],
                    (int) $row['Cantidad'],
                    $row['Estado'],
                    $row['Consecutivo_ref'] ? 'Préstamo #' . $row['Consecutivo_ref'] : '—',
                    $row['Descripcion'] ?? '',
                ]);
            }
        }

        fclose($output);
        exit;
    }

    private function getFilters(): array
    {
        $tipo = trim($_GET['tipo'] ?? '');
        $cantidadOperador = trim($_GET['cantidad_operador'] ?? '');
        $cantidadValor = trim($_GET['cantidad_valor'] ?? '');

        return [
            'fecha_desde'        => trim($_GET['fecha_desde'] ?? ''),
            'fecha_hasta'        => trim($_GET['fecha_hasta'] ?? ''),
            'id_categoria'       => (int) ($_GET['categoria'] ?? 0) ?: null,
            'elemento'           => trim($_GET['elemento'] ?? ''),
            'estado'             => trim($_GET['estado'] ?? ''),
            'tipo'               => in_array($tipo, ['Ingreso', 'Prestamo', 'Devolucion', 'Dar_Baja'], true) ? $tipo : '',
            'cedula_cuentadante' => trim($_GET['cuentadante'] ?? ''),
            'cantidad_operador'  => in_array($cantidadOperador, ['mayor', 'igual', 'menor'], true) ? $cantidadOperador : '',
            'cantidad_valor'     => $cantidadValor !== '' ? (int) $cantidadValor : null,
        ];
    }

    private function hasSearch(array $filters, string $tipoReporte): bool
    {
        if (!isset($_GET['buscar'])) {
            return false;
        }

        if ($tipoReporte === 'inventario') {
            return true;
        }

        foreach (['fecha_desde', 'fecha_hasta', 'id_categoria', 'elemento', 'estado', 'tipo', 'cedula_cuentadante'] as $key) {
            if (($filters[$key] ?? '') !== '' && ($filters[$key] ?? null) !== null) {
                return true;
            }
        }

        return true;
    }
}
