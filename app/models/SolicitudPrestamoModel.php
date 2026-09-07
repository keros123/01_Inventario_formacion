<?php

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/MovimientoModel.php';
require_once __DIR__ . '/InventarioModel.php';

class SolicitudPrestamoModel extends Model
{
    public function create(string $cedulaSolicitante, string $fecha, ?string $descripcion, array $lineas): int
    {
        if (empty($lineas)) {
            throw new RuntimeException('Debe registrar al menos un elemento.');
        }

        $inventario = new InventarioModel();
        foreach ($lineas as $index => $linea) {
            $item = $inventario->findByCodigo($linea['codigo']);
            if (!$item) {
                throw new RuntimeException('El elemento ' . $linea['codigo'] . ' no existe.');
            }
            if ($item['Estado'] !== 'Activo') {
                throw new RuntimeException('El elemento ' . $linea['codigo'] . ' no está activo.');
            }
            if ($linea['cantidad'] <= 0) {
                throw new RuntimeException('La cantidad debe ser mayor a cero en la línea ' . ($index + 1) . '.');
            }
            if ((int) $item['Cantidad'] < $linea['cantidad']) {
                throw new RuntimeException(
                    'Cantidad insuficiente para ' . $linea['codigo'] . '. Disponible: ' . (int) $item['Cantidad']
                );
            }
        }

        $row = $this->from('Solicitudes_Prestamo')->insert([
            'Cedula_solicitante' => $cedulaSolicitante,
            'Fecha_solicitud'    => $fecha,
            'Descripcion'        => $descripcion,
            'Estado'             => 'Pendiente',
        ]);
        $idSolicitud = (int) ($row['id_solicitud'] ?? 0);

        foreach ($lineas as $index => $linea) {
            $this->from('Det_Solicitudes')->insert([
                'id_solicitud'    => $idSolicitud,
                'id_linea'        => $index + 1,
                'Codigo_elemento' => $linea['codigo'],
                'Cantidad'        => $linea['cantidad'],
            ]);
        }

        return $idSolicitud;
    }

    public function findById(int $id): ?array
    {
        $row = $this->from('Solicitudes_Prestamo')->eq('id_solicitud', $id)->first();
        if (!$row) {
            return null;
        }
        return $this->hydrateSolicitud($row);
    }

    public function getDetalle(int $idSolicitud): array
    {
        $detalles = $this->from('Det_Solicitudes')->eq('id_solicitud', $idSolicitud)->order('id_linea')->get();
        $inventario = [];
        foreach ($this->from('Inventario')->get() as $item) {
            $inventario[$item['Codigo']] = $item;
        }
        foreach ($detalles as &$det) {
            $det['Elemento'] = $inventario[$det['Codigo_elemento']]['Elemento'] ?? '[Elemento eliminado]';
        }
        unset($det);
        return $detalles;
    }

    public function getForMonth(int $year, int $month, ?string $cedulaSolicitante = null): array
    {
        $inicio = sprintf('%04d-%02d-01', $year, $month);
        $fin = date('Y-m-t', strtotime($inicio));

        $q = $this->from('Solicitudes_Prestamo')
            ->gte('Fecha_solicitud', $inicio)
            ->lte('Fecha_solicitud', $fin)
            ->order('Fecha_solicitud')
            ->order('id_solicitud');

        if ($cedulaSolicitante !== null && $cedulaSolicitante !== '') {
            $q->eq('Cedula_solicitante', $cedulaSolicitante);
        }

        return $this->withLineas($q->get());
    }

    public function getBySolicitante(string $cedula): array
    {
        $rows = $this->from('Solicitudes_Prestamo')
            ->eq('Cedula_solicitante', $cedula)
            ->order('Fecha_solicitud', false)
            ->order('id_solicitud', false)
            ->get();
        return $this->withLineas($rows);
    }

    public function getPendientes(): array
    {
        $rows = $this->from('Solicitudes_Prestamo')
            ->eq('Estado', 'Pendiente')
            ->order('Fecha_solicitud')
            ->order('id_solicitud')
            ->get();
        return $this->withLineas($rows);
    }

    public function countPendientes(): int
    {
        return $this->from('Solicitudes_Prestamo')->eq('Estado', 'Pendiente')->count();
    }

    public function countBySolicitante(string $cedula, ?string $estado = null): int
    {
        $q = $this->from('Solicitudes_Prestamo')->eq('Cedula_solicitante', $cedula);
        if ($estado !== null && $estado !== '') {
            $q->eq('Estado', $estado);
        }
        return $q->count();
    }

    public function cancelar(int $id, string $cedula): bool
    {
        $solicitud = $this->findById($id);
        if (!$solicitud || $solicitud['Estado'] !== 'Pendiente') {
            return false;
        }
        if ($solicitud['Cedula_solicitante'] !== $cedula) {
            return false;
        }

        $updated = $this->from('Solicitudes_Prestamo')
            ->eq('id_solicitud', $id)
            ->eq('Estado', 'Pendiente')
            ->update([
                'Estado'           => 'Cancelada',
                'Fecha_resolucion' => date('c'),
            ]);
        return $updated !== [];
    }

    public function rechazar(int $id, string $cedulaAprobador, string $motivo): bool
    {
        $solicitud = $this->findById($id);
        if (!$solicitud || $solicitud['Estado'] !== 'Pendiente') {
            return false;
        }

        $updated = $this->from('Solicitudes_Prestamo')
            ->eq('id_solicitud', $id)
            ->eq('Estado', 'Pendiente')
            ->update([
                'Estado'            => 'Rechazada',
                'Cedula_aprobador'  => $cedulaAprobador,
                'Fecha_resolucion'  => date('c'),
                'Motivo_rechazo'    => $motivo,
            ]);
        return $updated !== [];
    }

    public function aprobar(int $id, string $cedulaAprobador): int
    {
        $solicitud = $this->findById($id);
        if (!$solicitud || $solicitud['Estado'] !== 'Pendiente') {
            throw new RuntimeException('La solicitud no está pendiente de aprobación.');
        }

        $lineas = [];
        foreach ($this->getDetalle($id) as $det) {
            $lineas[] = [
                'codigo'   => $det['Codigo_elemento'],
                'cantidad' => (int) $det['Cantidad'],
            ];
        }

        $idMovimiento = (new MovimientoModel())->registrarPrestamo([
            'fecha'              => $solicitud['Fecha_solicitud'],
            'cedula_cuentadante' => $solicitud['Cedula_solicitante'],
            'descripcion'        => $solicitud['Descripcion'] ?: ('Solicitud de préstamo #' . $id),
        ], $lineas);

        $updated = $this->from('Solicitudes_Prestamo')
            ->eq('id_solicitud', $id)
            ->eq('Estado', 'Pendiente')
            ->update([
                'Estado'           => 'Aprobada',
                'id_movimiento'    => $idMovimiento,
                'Cedula_aprobador' => $cedulaAprobador,
                'Fecha_resolucion' => date('c'),
            ]);

        if ($updated === []) {
            throw new RuntimeException('No se pudo actualizar la solicitud.');
        }

        return $idMovimiento;
    }

    private function hydrateSolicitud(array $row): array
    {
        $solicitante = $this->from('Usuarios')->select('Nombres')->eq('Cedula', $row['Cedula_solicitante'])->first();
        $row['Solicitante'] = $solicitante['Nombres'] ?? null;
        $row['Aprobador'] = null;
        if (!empty($row['Cedula_aprobador'])) {
            $aprobador = $this->from('Usuarios')->select('Nombres')->eq('Cedula', $row['Cedula_aprobador'])->first();
            $row['Aprobador'] = $aprobador['Nombres'] ?? null;
        }
        return $row;
    }

    private function withLineas(array $rows): array
    {
        if ($rows === []) {
            return [];
        }
        $ids = array_column($rows, 'id_solicitud');
        $detalles = $this->from('Det_Solicitudes')->in('id_solicitud', $ids)->get();
        $conteo = [];
        foreach ($detalles as $det) {
            $id = (int) $det['id_solicitud'];
            $conteo[$id] = ($conteo[$id] ?? 0) + 1;
        }
        $usuarios = [];
        foreach ($this->from('Usuarios')->select('Cedula,Nombres')->get() as $u) {
            $usuarios[$u['Cedula']] = $u['Nombres'];
        }
        foreach ($rows as &$row) {
            $row['lineas'] = $conteo[(int) $row['id_solicitud']] ?? 0;
            $row['Solicitante'] = $usuarios[$row['Cedula_solicitante'] ?? ''] ?? null;
        }
        unset($row);
        return $rows;
    }
}
