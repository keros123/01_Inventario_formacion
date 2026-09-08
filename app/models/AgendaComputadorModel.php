<?php

require_once __DIR__ . '/../core/Model.php';

class AgendaComputadorModel extends Model
{
    public const SEDES = ['Ternera', 'Emprender'];
    public const ESTADOS = ['Pendiente', 'Aprobada', 'Rechazada', 'Cancelada', 'Reprogramada'];

    public function create(array $data): int
    {
        $row = $this->from('Agenda_Computadores')->insert([
            'Cedula_solicitante'  => $data['cedula'],
            'Nombres'             => $data['nombres'],
            'Telefono'            => $data['telefono'],
            'Fecha_solicitud'     => $data['fecha'],
            'Cantidad_portatiles' => $data['cantidad'],
            'Sede'                => $data['sede'],
            'Hora_inicio'         => $data['hora_inicio'],
            'Hora_final'          => $data['hora_final'],
            'Ficha_programa'      => $data['ficha_programa'],
            'Estado'              => 'Pendiente',
        ]);

        return (int) ($row['id_agenda'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        $row = $this->from('Agenda_Computadores')->eq('id_agenda', $id)->first();
        return $row ? $this->hydrate($row, true) : null;
    }

    public function getForRange(string $inicio, string $fin): array
    {
        $rows = $this->from('Agenda_Computadores')
            ->gte('Fecha_solicitud', $inicio)
            ->lte('Fecha_solicitud', $fin)
            ->order('Fecha_solicitud')
            ->order('Hora_inicio')
            ->get();

        return array_map(fn (array $row): array => $this->hydrate($row, false), $rows);
    }

    public function getBySolicitante(string $cedula): array
    {
        $rows = $this->from('Agenda_Computadores')
            ->eq('Cedula_solicitante', $cedula)
            ->order('Fecha_solicitud', false)
            ->order('Hora_inicio', false)
            ->get();

        return array_map(fn (array $row): array => $this->hydrate($row, false), $rows);
    }

    public function getPendientes(): array
    {
        $rows = $this->from('Agenda_Computadores')
            ->in('Estado', ['Pendiente', 'Reprogramada'])
            ->order('Fecha_solicitud')
            ->order('Hora_inicio')
            ->get();

        return array_map(fn (array $row): array => $this->hydrate($row, false), $rows);
    }

    public function countPendientes(): int
    {
        $pendiente = $this->from('Agenda_Computadores')->eq('Estado', 'Pendiente')->count();
        $reprogramada = $this->from('Agenda_Computadores')->eq('Estado', 'Reprogramada')->count();
        return $pendiente + $reprogramada;
    }

    public function countBySolicitante(string $cedula, ?string $estado = null): int
    {
        $q = $this->from('Agenda_Computadores')->eq('Cedula_solicitante', $cedula);
        if ($estado !== null && $estado !== '') {
            $q->eq('Estado', $estado);
        }
        return $q->count();
    }

    public function cancelar(int $id, string $cedula): bool
    {
        $item = $this->findById($id);
        if (!$item || $item['Cedula_solicitante'] !== $cedula) {
            return false;
        }
        if (!in_array($item['Estado'], ['Pendiente', 'Reprogramada'], true)) {
            return false;
        }

        $updated = $this->from('Agenda_Computadores')
            ->eq('id_agenda', $id)
            ->in('Estado', ['Pendiente', 'Reprogramada'])
            ->update([
                'Estado'           => 'Cancelada',
                'Fecha_resolucion' => date('c'),
            ]);

        return $updated !== [];
    }

    public function rechazar(int $id, string $cedulaAprobador, string $motivo): bool
    {
        $item = $this->findById($id);
        if (!$item || !in_array($item['Estado'], ['Pendiente', 'Reprogramada'], true)) {
            return false;
        }

        $updated = $this->from('Agenda_Computadores')
            ->eq('id_agenda', $id)
            ->in('Estado', ['Pendiente', 'Reprogramada'])
            ->update([
                'Estado'           => 'Rechazada',
                'Cedula_aprobador' => $cedulaAprobador,
                'Fecha_resolucion' => date('c'),
                'Motivo_rechazo'   => $motivo,
            ]);

        return $updated !== [];
    }

    public function aprobar(int $id, string $cedulaAprobador): bool
    {
        $item = $this->findById($id);
        if (!$item || !in_array($item['Estado'], ['Pendiente', 'Reprogramada'], true)) {
            throw new RuntimeException('La solicitud no está pendiente de aprobación.');
        }

        $updated = $this->from('Agenda_Computadores')
            ->eq('id_agenda', $id)
            ->in('Estado', ['Pendiente', 'Reprogramada'])
            ->update([
                'Estado'           => 'Aprobada',
                'Cedula_aprobador' => $cedulaAprobador,
                'Fecha_resolucion' => date('c'),
            ]);

        if ($updated === []) {
            throw new RuntimeException('No se pudo aprobar la solicitud.');
        }

        return true;
    }

    public function reprogramar(int $id, string $cedulaAprobador, array $datos): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            throw new RuntimeException('Solicitud no encontrada.');
        }
        if (!in_array($item['Estado'], ['Pendiente', 'Reprogramada', 'Aprobada'], true)) {
            throw new RuntimeException('Solo se pueden reprogramar solicitudes pendientes o aprobadas.');
        }

        $payload = [
            'Fecha_solicitud'       => $datos['fecha'],
            'Hora_inicio'           => $datos['hora_inicio'],
            'Hora_final'            => $datos['hora_final'],
            'Sede'                  => $datos['sede'],
            'Estado'                => $item['Estado'] === 'Aprobada' ? 'Aprobada' : 'Reprogramada',
            'Cedula_aprobador'      => $cedulaAprobador,
            'Fecha_resolucion'      => date('c'),
            'Motivo_reprogramacion' => $datos['motivo'] !== '' ? $datos['motivo'] : null,
        ];

        if (empty($item['Fecha_original'])) {
            $payload['Fecha_original'] = $item['Fecha_solicitud'];
            $payload['Hora_inicio_original'] = $item['Hora_inicio'];
            $payload['Hora_final_original'] = $item['Hora_final'];
        }

        $updated = $this->from('Agenda_Computadores')
            ->eq('id_agenda', $id)
            ->update($payload);

        if ($updated === []) {
            throw new RuntimeException('No se pudo reprogramar la solicitud.');
        }

        return true;
    }

    private function hydrate(array $row, bool $withAprobador = false): array
    {
        $row['Hora_inicio_fmt'] = self::formatHora($row['Hora_inicio'] ?? '');
        $row['Hora_final_fmt'] = self::formatHora($row['Hora_final'] ?? '');
        $row['Hora_inicio_original_fmt'] = self::formatHora($row['Hora_inicio_original'] ?? '');
        $row['Hora_final_original_fmt'] = self::formatHora($row['Hora_final_original'] ?? '');
        $row['Aprobador'] = null;
        if ($withAprobador && !empty($row['Cedula_aprobador'])) {
            $aprobador = $this->from('Usuarios')->select('Nombres')->eq('Cedula', $row['Cedula_aprobador'])->first();
            $row['Aprobador'] = $aprobador['Nombres'] ?? null;
        }
        return $row;
    }

    public static function formatHora(string $hora): string
    {
        $hora = trim($hora);
        if ($hora === '') {
            return '';
        }
        if (preg_match('/^(\d{1,2}:\d{2})/', $hora, $m)) {
            return $m[1];
        }
        return $hora;
    }
}
