<?php

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/InventarioModel.php';

class MovimientoModel extends Model
{
    public static function esDarDeBaja(?string $tipo): bool
    {
        if ($tipo === null || trim($tipo) === '') {
            return false;
        }

        $normalizado = strtolower(preg_replace('/[\s\-.]+/', '_', trim($tipo)));

        return in_array($normalizado, [
            'dar_baja',
            'dardebaja',
            'darde_baja',
            'dar_de_baja',
        ], true);
    }

    public static function etiquetaTipo(string $tipo): string
    {
        if (self::esDarDeBaja($tipo)) {
            return 'Dar de baja';
        }

        return match ($tipo) {
            'Ingreso'    => 'Ingreso',
            'Prestamo'   => 'Préstamo',
            'Devolucion' => 'Devolución',
            default      => $tipo,
        };
    }

    public function findDarDeBajaById(int $id): ?array
    {
        $movimiento = $this->findMovimientoById($id);
        if (!$movimiento || !self::esDarDeBaja($movimiento['Tipo'] ?? '')) {
            return null;
        }

        $movimiento['Tipo'] = 'Dar_Baja';
        return $movimiento;
    }

    public function registrarIngreso(array $movimiento, array $lineas): int
    {
        if (empty($lineas)) {
            throw new RuntimeException('Debe registrar al menos un elemento.');
        }

        $inventario = new InventarioModel();

        foreach ($lineas as $index => &$linea) {
            if (!empty($linea['nuevo'])) {
                $nuevo = $linea['nuevo'];
                if ($inventario->findByCodigo($nuevo['codigo'])) {
                    throw new RuntimeException('El código ' . $nuevo['codigo'] . ' ya existe.');
                }
                $inventario->create([
                    'codigo'       => $nuevo['codigo'],
                    'elemento'     => $nuevo['elemento'],
                    'id_categoria' => $nuevo['id_categoria'],
                    'descripcion'  => $nuevo['descripcion'],
                    'cantidad'     => 0,
                    'fotografia'   => null,
                    'estado'       => 'Activo',
                ]);
                $linea['codigo'] = $nuevo['codigo'];
            }

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
        }
        unset($linea);

        $idMovimiento = $this->crearMovimiento('Ingreso', $movimiento);

        foreach ($lineas as $index => $linea) {
            $this->crearDetalle($idMovimiento, $index + 1, $linea['codigo'], $linea['cantidad']);
            if (!$inventario->ajustarCantidad($linea['codigo'], $linea['cantidad'])) {
                throw new RuntimeException('No se pudo actualizar la cantidad del inventario.');
            }
        }

        return $idMovimiento;
    }

    public function registrarPrestamo(array $movimiento, array $lineas): int
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
                    'Stock insuficiente para ' . $linea['codigo'] . '. Disponible: ' . (int) $item['Cantidad']
                );
            }
        }

        $idMovimiento = $this->crearMovimiento('Prestamo', $movimiento);

        foreach ($lineas as $index => $linea) {
            $this->crearDetalle($idMovimiento, $index + 1, $linea['codigo'], $linea['cantidad']);
            if (!$inventario->ajustarCantidad($linea['codigo'], -$linea['cantidad'])) {
                throw new RuntimeException('No se pudo actualizar la cantidad del inventario.');
            }
        }

        return $idMovimiento;
    }

    public function getPrestamos(array $filters = []): array
    {
        $filters['tipo'] = 'Prestamo';
        $rows = $this->getMovimientos($filters);

        foreach ($rows as &$row) {
            $pendientes = $this->getLineasPendientes((int) $row['id_movimiento']);
            $row['Pendiente_total'] = array_sum(array_column($pendientes, 'pendiente'));
            if ($row['Pendiente_total'] === 0 && $row['Estado'] !== 'Cerrado') {
                $this->sincronizarEstadoPrestamo((int) $row['id_movimiento']);
                $row['Estado'] = 'Cerrado';
            }
        }
        unset($row);

        return $rows;
    }

    public function getMovimientos(array $filters = []): array
    {
        $movimientos = $this->queryMovimientos($filters);
        if ($movimientos === []) {
            return [];
        }

        $ids = array_column($movimientos, 'id_movimiento');
        $detalles = $this->from('Det_Movimientos')->in('id_movimiento', $ids)->eq('Estado', 'Activo')->order('id_Detalle')->get();
        $inventario = $this->mapBy($this->from('Inventario')->get(), 'Codigo');
        $usuarios = $this->mapBy($this->from('Usuarios')->select('Cedula,Nombres')->get(), 'Cedula');
        $refs = $this->mapBy($movimientos, 'id_movimiento');

        $refIds = array_filter(array_column($movimientos, 'id_movimiento_ref'));
        if ($refIds) {
            foreach ($this->from('Movimientos')->in('id_movimiento', array_values($refIds))->get() as $ref) {
                $refs[(int) $ref['id_movimiento']] = $ref;
            }
        }

        $detallesPorMov = [];
        foreach ($detalles as $det) {
            $detallesPorMov[(int) $det['id_movimiento']][] = $det;
        }

        $rows = [];
        foreach ($movimientos as $m) {
            $id = (int) $m['id_movimiento'];
            $lineas = $detallesPorMov[$id] ?? [];
            $partes = [];
            $total = 0;
            foreach ($lineas as $det) {
                $codigo = $det['Codigo_elemento'];
                $nombre = $inventario[$codigo]['Elemento'] ?? '[Elemento eliminado]';
                $cantidad = (int) $det['Cantidad'];
                $total += $cantidad;
                $partes[] = $codigo . ' - ' . $nombre . ' (' . $cantidad . ')';
            }
            $refId = $m['id_movimiento_ref'] ?? null;
            $m['Cuentadante'] = $usuarios[$m['Cedula_cuentadante'] ?? '']['Nombres'] ?? null;
            $m['Consecutivo_ref'] = $refId ? ($refs[(int) $refId]['Consecutivo'] ?? null) : null;
            $m['Elementos'] = implode('; ', $partes);
            $m['Total_unidades'] = $total;
            $rows[] = $m;
        }

        return $rows;
    }

    public function getReporteMovimientos(array $filters = []): array
    {
        $movimientos = $this->queryMovimientos($filters);
        if ($movimientos === []) {
            return [];
        }

        $ids = array_column($movimientos, 'id_movimiento');
        $detalles = $this->from('Det_Movimientos')->in('id_movimiento', $ids)->eq('Estado', 'Activo')->order('id_Detalle')->get();
        $inventario = $this->mapBy($this->from('Inventario')->get(), 'Codigo');
        $categorias = $this->mapBy($this->from('Categorias')->get(), 'id_categoria');
        $usuarios = $this->mapBy($this->from('Usuarios')->select('Cedula,Nombres')->get(), 'Cedula');
        $movMap = $this->mapBy($movimientos, 'id_movimiento');

        $refIds = array_filter(array_column($movimientos, 'id_movimiento_ref'));
        if ($refIds) {
            foreach ($this->from('Movimientos')->in('id_movimiento', array_values($refIds))->get() as $ref) {
                $movMap[(int) $ref['id_movimiento']] = $ref;
            }
        }

        $rows = [];
        foreach ($detalles as $det) {
            $m = $movMap[(int) $det['id_movimiento']] ?? null;
            if (!$m) {
                continue;
            }
            $item = $inventario[$det['Codigo_elemento']] ?? null;
            $catId = $item['id_categoria'] ?? null;

            if (!empty($filters['id_categoria']) && (int) $catId !== (int) $filters['id_categoria']) {
                continue;
            }
            if (!empty($filters['elemento'])) {
                $term = mb_strtolower((string) $filters['elemento']);
                $haystack = mb_strtolower(($det['Codigo_elemento'] ?? '') . ' ' . ($item['Elemento'] ?? ''));
                if (!str_contains($haystack, $term)) {
                    continue;
                }
            }

            $refId = $m['id_movimiento_ref'] ?? null;
            $rows[] = [
                'id_movimiento'      => $m['id_movimiento'],
                'Tipo'               => $m['Tipo'],
                'Consecutivo'        => $m['Consecutivo'],
                'Fecha'              => $m['Fecha'],
                'Descripcion'        => $m['Descripcion'],
                'Estado'             => $m['Estado'],
                'Cedula_cuentadante' => $m['Cedula_cuentadante'],
                'id_movimiento_ref'  => $m['id_movimiento_ref'],
                'Cuentadante'        => $usuarios[$m['Cedula_cuentadante'] ?? '']['Nombres'] ?? null,
                'Consecutivo_ref'    => $refId ? ($movMap[(int) $refId]['Consecutivo'] ?? null) : null,
                'Codigo_elemento'    => $det['Codigo_elemento'],
                'Elemento'           => $item['Elemento'] ?? '[Elemento eliminado]',
                'Categoria'          => $catId ? ($categorias[(int) $catId]['Nombre'] ?? null) : null,
                'Cantidad'           => $det['Cantidad'],
            ];
        }

        return $rows;
    }

    public function countPrestamosActivos(): int
    {
        return $this->from('Movimientos')->eq('Tipo', 'Prestamo')->eq('Estado', 'Activo')->count();
    }

    public function countPrestamosActivosPorCedula(string $cedula): int
    {
        return $this->from('Movimientos')
            ->eq('Tipo', 'Prestamo')
            ->eq('Estado', 'Activo')
            ->eq('Cedula_cuentadante', $cedula)
            ->count();
    }

    public function findPrestamoById(int $id): ?array
    {
        return $this->findMovimientoById($id, 'Prestamo');
    }

    public function findIngresoById(int $id): ?array
    {
        return $this->findMovimientoById($id, 'Ingreso');
    }

    public function findMovimientoById(int $id, ?string $tipo = null): ?array
    {
        $q = $this->from('Movimientos')->eq('id_movimiento', $id);
        if ($tipo) {
            $q->eq('Tipo', $tipo);
        }
        $row = $q->first();
        if (!$row) {
            return null;
        }
        if (!empty($row['Cedula_cuentadante'])) {
            $user = $this->from('Usuarios')->select('Nombres')->eq('Cedula', $row['Cedula_cuentadante'])->first();
            $row['Cuentadante'] = $user['Nombres'] ?? null;
        } else {
            $row['Cuentadante'] = null;
        }
        $row['Consecutivo_ref'] = null;
        if (!empty($row['id_movimiento_ref'])) {
            $ref = $this->from('Movimientos')->select('Consecutivo')->eq('id_movimiento', $row['id_movimiento_ref'])->first();
            $row['Consecutivo_ref'] = $ref['Consecutivo'] ?? null;
        }
        return $row;
    }

    public function getDetalleByMovimientoId(int $id): array
    {
        $detalles = $this->from('Det_Movimientos')->eq('id_movimiento', $id)->order('id_Detalle')->get();
        $inventario = $this->mapBy($this->from('Inventario')->get(), 'Codigo');
        $categorias = $this->mapBy($this->from('Categorias')->get(), 'id_categoria');

        foreach ($detalles as &$det) {
            $item = $inventario[$det['Codigo_elemento']] ?? null;
            $det['Elemento'] = $item['Elemento'] ?? null;
            $catId = $item['id_categoria'] ?? null;
            $det['Categoria'] = $catId ? ($categorias[(int) $catId]['Nombre'] ?? null) : null;
        }
        unset($det);

        return $detalles;
    }

    public function getFotosByMovimientoId(int $id): array
    {
        return $this->from('Movimiento_Fotos')->eq('id_movimiento', $id)->order('Orden')->get();
    }

    public function guardarFotos(int $idMovimiento, array $rutas): void
    {
        foreach ($rutas as $index => $ruta) {
            $this->from('Movimiento_Fotos')->insert([
                'id_movimiento' => $idMovimiento,
                'Ruta'          => $ruta,
                'Orden'         => $index + 1,
            ]);
        }
    }

    public function getLineasPendientes(int $idPrestamo): array
    {
        $prestadas = $this->from('Det_Movimientos')
            ->eq('id_movimiento', $idPrestamo)
            ->eq('Estado', 'Activo')
            ->order('id_Detalle')
            ->get();
        $inventario = $this->mapBy($this->from('Inventario')->get(), 'Codigo');
        $devueltas = $this->getCantidadesDevueltas($idPrestamo);
        $lineas = [];

        foreach ($prestadas as $linea) {
            $codigo = $linea['Codigo_elemento'];
            $prestado = (int) $linea['Cantidad'];
            $devuelto = (int) ($devueltas[$codigo] ?? 0);
            $lineas[] = [
                'codigo'    => $codigo,
                'elemento'  => $inventario[$codigo]['Elemento'] ?? '[Elemento eliminado]',
                'prestado'  => $prestado,
                'devuelto'  => $devuelto,
                'pendiente' => max(0, $prestado - $devuelto),
            ];
        }

        return $lineas;
    }

    public function tienePendiente(int $idPrestamo): bool
    {
        foreach ($this->getLineasPendientes($idPrestamo) as $linea) {
            if ($linea['pendiente'] > 0) {
                return true;
            }
        }
        return false;
    }

    public function updateEstadoPrestamo(int $id, string $estado): bool
    {
        if (!in_array($estado, ['Activo', 'Inactivo', 'Cerrado'], true)) {
            throw new RuntimeException('Estado no válido.');
        }

        $prestamo = $this->findPrestamoById($id);
        if (!$prestamo) {
            return false;
        }

        if (!$this->tienePendiente($id)) {
            $estado = 'Cerrado';
        }

        if ($prestamo['Estado'] === $estado) {
            return true;
        }

        $updated = $this->from('Movimientos')->eq('id_movimiento', $id)->eq('Tipo', 'Prestamo')->update([
            'Estado' => $estado,
        ]);
        return $updated !== [];
    }

    public function sincronizarEstadoPrestamo(int $idPrestamo): void
    {
        $prestamo = $this->findPrestamoById($idPrestamo);
        if (!$prestamo) {
            return;
        }

        if (!$this->tienePendiente($idPrestamo) && $prestamo['Estado'] !== 'Cerrado') {
            $this->updateEstadoPrestamo($idPrestamo, 'Cerrado');
        }
    }

    public function registrarDevolucion(int $idPrestamo, array $lineas, ?string $descripcion = null): void
    {
        $prestamo = $this->findPrestamoById($idPrestamo);
        if (!$prestamo) {
            throw new RuntimeException('Préstamo no encontrado.');
        }

        $pendientePorCodigo = [];
        foreach ($this->getLineasPendientes($idPrestamo) as $p) {
            $pendientePorCodigo[$p['codigo']] = $p['pendiente'];
        }

        $lineasValidas = [];
        foreach ($lineas as $linea) {
            $codigo = $linea['codigo'] ?? '';
            $cantidad = (int) ($linea['cantidad'] ?? 0);
            $maxPendiente = $pendientePorCodigo[$codigo] ?? 0;

            if ($cantidad <= 0) {
                continue;
            }
            if ($maxPendiente <= 0) {
                throw new RuntimeException('No hay pendiente para el elemento ' . $codigo . '.');
            }
            if ($cantidad > $maxPendiente) {
                throw new RuntimeException(
                    'Cantidad a devolver excede lo pendiente para ' . $codigo . '. Pendiente: ' . $maxPendiente
                );
            }
            $lineasValidas[] = ['codigo' => $codigo, 'cantidad' => $cantidad];
        }

        if (empty($lineasValidas)) {
            throw new RuntimeException('Debe indicar al menos una cantidad a devolver.');
        }

        $inventario = new InventarioModel();
        $idMovimiento = $this->crearMovimiento('Devolucion', [
            'fecha'              => date('Y-m-d'),
            'cedula_cuentadante' => $prestamo['Cedula_cuentadante'],
            'descripcion'        => $descripcion,
            'id_movimiento_ref'  => $idPrestamo,
        ]);

        foreach ($lineasValidas as $index => $linea) {
            $this->crearDetalle($idMovimiento, $index + 1, $linea['codigo'], $linea['cantidad']);
            if (!$inventario->ajustarCantidad($linea['codigo'], $linea['cantidad'])) {
                throw new RuntimeException('No se pudo actualizar la cantidad del inventario.');
            }
        }

        $this->sincronizarEstadoPrestamo($idPrestamo);
    }

    public function registrarDevolucionTotal(int $idPrestamo, ?string $descripcion = null): void
    {
        $lineas = [];
        foreach ($this->getLineasPendientes($idPrestamo) as $p) {
            if ($p['pendiente'] > 0) {
                $lineas[] = ['codigo' => $p['codigo'], 'cantidad' => $p['pendiente']];
            }
        }

        if (empty($lineas)) {
            throw new RuntimeException('No hay elementos pendientes por devolver.');
        }

        $this->registrarDevolucion(
            $idPrestamo,
            $lineas,
            $descripcion ?: 'Devolución total del préstamo #' . $idPrestamo
        );
    }

    public function registrarDarDeBaja(int $idPrestamo, array $lineas, string $descripcion, array $fotos = []): int
    {
        $prestamo = $this->findPrestamoById($idPrestamo);
        if (!$prestamo) {
            throw new RuntimeException('Préstamo no encontrado.');
        }
        if (empty($fotos)) {
            throw new RuntimeException('Debe adjuntar al menos una fotografía del elemento.');
        }
        if (count($fotos) > 3) {
            throw new RuntimeException('Máximo 3 fotografías permitidas.');
        }

        $pendientePorCodigo = [];
        foreach ($this->getLineasPendientes($idPrestamo) as $p) {
            $pendientePorCodigo[$p['codigo']] = $p['pendiente'];
        }

        $lineasValidas = [];
        foreach ($lineas as $linea) {
            $codigo = $linea['codigo'] ?? '';
            $cantidad = (int) ($linea['cantidad'] ?? 0);
            $maxPendiente = $pendientePorCodigo[$codigo] ?? 0;
            if ($cantidad <= 0) {
                continue;
            }
            if ($maxPendiente <= 0) {
                throw new RuntimeException('No hay pendiente para el elemento ' . $codigo . '.');
            }
            if ($cantidad > $maxPendiente) {
                throw new RuntimeException(
                    'Cantidad a dar de baja excede lo pendiente para ' . $codigo . '. Pendiente: ' . $maxPendiente
                );
            }
            $lineasValidas[] = ['codigo' => $codigo, 'cantidad' => $cantidad];
        }

        if (empty($lineasValidas)) {
            throw new RuntimeException('Debe indicar al menos una cantidad a dar de baja.');
        }

        $idMovimiento = $this->crearMovimiento('Dar_Baja', [
            'fecha'              => date('Y-m-d'),
            'cedula_cuentadante' => $prestamo['Cedula_cuentadante'],
            'descripcion'        => $descripcion,
            'id_movimiento_ref'  => $idPrestamo,
        ]);

        foreach ($lineasValidas as $index => $linea) {
            $this->crearDetalle($idMovimiento, $index + 1, $linea['codigo'], $linea['cantidad']);
        }

        $this->guardarFotos($idMovimiento, $fotos);
        $this->sincronizarEstadoPrestamo($idPrestamo);
        return $idMovimiento;
    }

    private function queryMovimientos(array $filters): array
    {
        $q = $this->from('Movimientos')->order('Fecha', false)->order('id_movimiento', false);

        if (!empty($filters['tipo'])) {
            if ($filters['tipo'] === 'Dar_Baja' || $filters['tipo'] === 'DarDeBaja') {
                $q->orIn('Tipo', ['Dar_Baja', 'DarDeBaja']);
            } else {
                $q->eq('Tipo', $filters['tipo']);
            }
        }
        if (!empty($filters['cedula_cuentadante'])) {
            $q->eq('Cedula_cuentadante', $filters['cedula_cuentadante']);
        }
        if (!empty($filters['estado'])) {
            $q->eq('Estado', $filters['estado']);
        }
        if (!empty($filters['fecha_desde'])) {
            $q->gte('Fecha', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $q->lte('Fecha', $filters['fecha_hasta']);
        }

        return $q->get();
    }

    private function getCantidadesDevueltas(int $idPrestamo): array
    {
        $movimientos = $this->from('Movimientos')
            ->eq('id_movimiento_ref', $idPrestamo)
            ->eq('Estado', 'Activo')
            ->get();

        $ids = [];
        foreach ($movimientos as $m) {
            if (($m['Tipo'] ?? '') === 'Devolucion' || self::esDarDeBaja($m['Tipo'] ?? '')) {
                $ids[] = $m['id_movimiento'];
            }
        }
        if ($ids === []) {
            return [];
        }

        $map = [];
        foreach ($this->from('Det_Movimientos')->in('id_movimiento', $ids)->eq('Estado', 'Activo')->get() as $row) {
            $codigo = $row['Codigo_elemento'];
            $map[$codigo] = ($map[$codigo] ?? 0) + (int) $row['Cantidad'];
        }
        return $map;
    }

    private function crearMovimiento(string $tipo, array $data): int
    {
        $ultimo = $this->from('Movimientos')
            ->select('Consecutivo')
            ->eq('Tipo', $tipo)
            ->order('Consecutivo', false)
            ->first();
        $consecutivo = (int) ($ultimo['Consecutivo'] ?? 0) + 1;

        $row = $this->from('Movimientos')->insert([
            'Tipo'               => $tipo,
            'Consecutivo'        => $consecutivo,
            'Fecha'              => $data['fecha'],
            'Cedula_cuentadante' => $data['cedula_cuentadante'] ?? null,
            'Descripcion'        => $data['descripcion'] ?? null,
            'Estado'             => 'Activo',
            'id_movimiento_ref'  => $data['id_movimiento_ref'] ?? null,
        ]);

        return (int) ($row['id_movimiento'] ?? 0);
    }

    private function crearDetalle(int $idMovimiento, int $idDetalle, string $codigoElemento, int $cantidad): void
    {
        $this->from('Det_Movimientos')->insert([
            'id_movimiento'   => $idMovimiento,
            'id_Detalle'      => $idDetalle,
            'Codigo_elemento' => $codigoElemento,
            'Cantidad'        => $cantidad,
            'Estado'          => 'Activo',
        ]);
    }

    private function mapBy(array $rows, string $key): array
    {
        $map = [];
        foreach ($rows as $row) {
            if (!isset($row[$key])) {
                continue;
            }
            $map[$row[$key]] = $row;
        }
        return $map;
    }
}
