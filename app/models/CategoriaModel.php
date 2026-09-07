<?php

require_once __DIR__ . '/../core/Model.php';

class CategoriaModel extends Model
{
    public function getAll(bool $includeDeleted = false): array
    {
        $q = $this->from('Categorias')->order('Nombre');
        if (!$includeDeleted) {
            $q->neq('Estado', 'Eliminado');
        }
        return $q->get();
    }

    public function getActivas(): array
    {
        return $this->from('Categorias')
            ->select('id_categoria,Nombre')
            ->eq('Estado', 'Activo')
            ->order('Nombre')
            ->get();
    }

    public function findById(int $id): ?array
    {
        return $this->from('Categorias')->eq('id_categoria', $id)->first();
    }

    public function create(array $data): bool
    {
        $this->from('Categorias')->insert([
            'Nombre' => $data['nombre'],
            'Estado' => $data['estado'] ?? 'Activo',
        ]);
        return true;
    }

    public function update(int $id, array $data): bool
    {
        $this->from('Categorias')->eq('id_categoria', $id)->update([
            'Nombre' => $data['nombre'],
            'Estado' => $data['estado'],
        ]);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->from('Categorias')->eq('id_categoria', $id)->update([
            'Estado' => 'Eliminado',
        ]);
        return true;
    }

    public function countItems(int $id): int
    {
        return $this->from('Inventario')
            ->eq('id_categoria', $id)
            ->neq('Estado', 'Eliminado')
            ->count();
    }

    public function getDefaultId(): int
    {
        $row = $this->from('Categorias')
            ->select('id_categoria')
            ->eq('Estado', 'Activo')
            ->order('id_categoria')
            ->first();
        return $row ? (int) $row['id_categoria'] : 1;
    }
}
