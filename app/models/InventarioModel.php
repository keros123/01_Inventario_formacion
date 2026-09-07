<?php

require_once __DIR__ . '/../core/Model.php';

class InventarioModel extends Model
{
    public function getAll(?string $busqueda = null, ?int $categoriaId = null): array
    {
        $q = $this->from('Inventario')->neq('Estado', 'Eliminado')->order('Elemento');

        if ($categoriaId !== null && $categoriaId > 0) {
            $q->eq('id_categoria', $categoriaId);
        }

        $items = $q->get();
        $items = $this->withCategoria($items);

        if ($busqueda !== null && $busqueda !== '') {
            $term = mb_strtolower($busqueda);
            $items = array_values(array_filter($items, static function (array $item) use ($term) {
                $haystack = mb_strtolower(implode(' ', [
                    $item['Codigo'] ?? '',
                    $item['Elemento'] ?? '',
                    $item['Descripcion'] ?? '',
                    $item['Categoria'] ?? '',
                ]));
                return str_contains($haystack, $term);
            }));
        }

        usort($items, static function (array $a, array $b) {
            $cmp = strcasecmp((string) ($a['Categoria'] ?? ''), (string) ($b['Categoria'] ?? ''));
            if ($cmp !== 0) {
                return $cmp;
            }
            return strcasecmp((string) ($a['Elemento'] ?? ''), (string) ($b['Elemento'] ?? ''));
        });

        return $items;
    }

    public function getGroupedByCategoria(?string $busqueda = null, ?int $categoriaId = null): array
    {
        $items = $this->getAll($busqueda, $categoriaId);
        $grouped = [];
        foreach ($items as $item) {
            $cat = $item['Categoria'] ?? 'Sin categoría';
            $grouped[$cat][] = $item;
        }
        return $grouped;
    }

    public function findByCodigo(string $codigo): ?array
    {
        $row = $this->from('Inventario')->eq('Codigo', $codigo)->first();
        if (!$row) {
            return null;
        }
        $hydrated = $this->withCategoria([$row]);
        return $hydrated[0];
    }

    public function create(array $data): bool
    {
        $this->from('Inventario')->insert([
            'Codigo'       => $data['codigo'],
            'Elemento'     => $data['elemento'],
            'id_categoria' => $data['id_categoria'],
            'Descripcion'  => $data['descripcion'],
            'Cantidad'     => $data['cantidad'],
            'Fotografia'   => $data['fotografia'] ?? null,
            'Estado'       => $data['estado'],
        ]);
        return true;
    }

    public function update(string $codigo, array $data): bool
    {
        $this->from('Inventario')->eq('Codigo', $codigo)->update([
            'Elemento'     => $data['elemento'],
            'id_categoria' => $data['id_categoria'],
            'Descripcion'  => $data['descripcion'],
            'Cantidad'     => $data['cantidad'],
            'Fotografia'   => $data['fotografia'] ?? null,
            'Estado'       => $data['estado'],
        ]);
        return true;
    }

    public function delete(string $codigo): bool
    {
        $this->from('Inventario')->eq('Codigo', $codigo)->update(['Estado' => 'Eliminado']);
        return true;
    }

    public function count(): int
    {
        return $this->from('Inventario')->neq('Estado', 'Eliminado')->count();
    }

    public function getActivos(): array
    {
        $items = $this->from('Inventario')
            ->select('Codigo,Elemento,Cantidad,id_categoria')
            ->eq('Estado', 'Activo')
            ->order('Elemento')
            ->get();
        return $this->withCategoria($items);
    }

    public function getActivosConStock(): array
    {
        $items = $this->from('Inventario')
            ->select('Codigo,Elemento,Cantidad,id_categoria')
            ->eq('Estado', 'Activo')
            ->gt('Cantidad', 0)
            ->order('Elemento')
            ->get();
        return $this->withCategoria($items);
    }

    public function ajustarCantidad(string $codigo, int $delta): bool
    {
        $item = $this->from('Inventario')->select('Cantidad')->eq('Codigo', $codigo)->first();
        if (!$item) {
            return false;
        }
        $nueva = (int) $item['Cantidad'] + $delta;
        $this->from('Inventario')->eq('Codigo', $codigo)->update(['Cantidad' => $nueva]);
        return true;
    }

    public function getForReport(array $filters = []): array
    {
        $q = $this->from('Inventario')
            ->select('Codigo,Elemento,Cantidad,Estado,Descripcion,id_categoria')
            ->neq('Estado', 'Eliminado');

        if (!empty($filters['id_categoria'])) {
            $q->eq('id_categoria', $filters['id_categoria']);
        }
        if (!empty($filters['estado'])) {
            $q->eq('Estado', $filters['estado']);
        }

        $items = $this->withCategoria($q->get());

        if (!empty($filters['elemento'])) {
            $term = mb_strtolower((string) $filters['elemento']);
            $items = array_values(array_filter($items, static function (array $item) use ($term) {
                $haystack = mb_strtolower(($item['Codigo'] ?? '') . ' ' . ($item['Elemento'] ?? ''));
                return str_contains($haystack, $term);
            }));
        }

        if (!empty($filters['cantidad_operador']) && $filters['cantidad_valor'] !== null && $filters['cantidad_valor'] !== '') {
            $valor = (int) $filters['cantidad_valor'];
            $items = array_values(array_filter($items, static function (array $item) use ($filters, $valor) {
                $cantidad = (int) ($item['Cantidad'] ?? 0);
                return match ($filters['cantidad_operador']) {
                    'mayor' => $cantidad > $valor,
                    'menor' => $cantidad < $valor,
                    'igual' => $cantidad === $valor,
                    default => true,
                };
            }));
        }

        usort($items, static function (array $a, array $b) {
            $cmp = strcasecmp((string) ($a['Categoria'] ?? ''), (string) ($b['Categoria'] ?? ''));
            if ($cmp !== 0) {
                return $cmp;
            }
            return strcasecmp((string) ($a['Elemento'] ?? ''), (string) ($b['Elemento'] ?? ''));
        });

        return $items;
    }

    private function withCategoria(array $items): array
    {
        if ($items === []) {
            return [];
        }
        $cats = Database::from('Categorias')->select('id_categoria,Nombre')->get();
        $map = [];
        foreach ($cats as $cat) {
            $map[(int) $cat['id_categoria']] = $cat['Nombre'] ?? 'Sin categoría';
        }
        foreach ($items as &$item) {
            $item['Categoria'] = $map[(int) ($item['id_categoria'] ?? 0)] ?? 'Sin categoría';
        }
        unset($item);
        return $items;
    }
}
