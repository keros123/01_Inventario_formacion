<?php

require_once __DIR__ . '/../core/Model.php';

class UsuarioModel extends Model
{
    public function findByCedula(string $cedula): ?array
    {
        return $this->from('Usuarios')->eq('Cedula', $cedula)->first();
    }

    public function authenticate(string $cedula, string $password): ?array
    {
        $user = $this->findByCedula($cedula);
        if (!$user) {
            return null;
        }
        if (($user['Estado'] ?? '') !== 'Activo') {
            return null;
        }
        if (!password_verify($password, (string) ($user['Password'] ?? ''))) {
            return null;
        }
        return $user;
    }

    public function getAll(): array
    {
        return $this->from('Usuarios')
            ->select('Cedula,Nombres,Tipo,Estado')
            ->order('Nombres')
            ->get();
    }

    public function getActivos(): array
    {
        return $this->from('Usuarios')
            ->select('Cedula,Nombres,Tipo,Estado')
            ->eq('Estado', 'Activo')
            ->order('Nombres')
            ->get();
    }

    public function getActivosParaPrestamo(): array
    {
        return $this->from('Usuarios')
            ->select('Cedula,Nombres')
            ->eq('Estado', 'Activo')
            ->order('Nombres')
            ->get();
    }

    public function create(array $data): bool
    {
        $this->from('Usuarios')->insert([
            'Cedula'   => $data['cedula'],
            'Nombres'  => $data['nombres'],
            'Password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'Tipo'     => $data['tipo'],
            'Estado'   => $data['estado'],
        ]);
        return true;
    }

    public function update(string $cedula, array $data): bool
    {
        $payload = [
            'Nombres' => $data['nombres'],
            'Tipo'    => $data['tipo'],
            'Estado'  => $data['estado'],
        ];
        if (!empty($data['password'])) {
            $payload['Password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->from('Usuarios')->eq('Cedula', $cedula)->update($payload);
        return true;
    }

    public function delete(string $cedula): bool
    {
        $this->from('Usuarios')->eq('Cedula', $cedula)->delete();
        return true;
    }
}
