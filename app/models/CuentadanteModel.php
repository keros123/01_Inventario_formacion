<?php

require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/UsuarioModel.php';

/**
 * Compatibilidad: los cuentadantes ahora viven en la tabla Usuarios.
 */
class CuentadanteModel extends Model
{
    private UsuarioModel $usuarios;

    public function __construct()
    {
        parent::__construct();
        $this->usuarios = new UsuarioModel();
    }

    public function getAll(): array
    {
        return $this->usuarios->getAll();
    }

    public function findByCedula(string $cedula): ?array
    {
        return $this->usuarios->findByCedula($cedula);
    }

    public function create(array $data): bool
    {
        if ($this->usuarios->findByCedula($data['cedula'])) {
            return $this->usuarios->update($data['cedula'], [
                'nombres'  => $data['nombres'],
                'tipo'     => 'Usuario',
                'estado'   => $data['estado'],
                'password' => '',
            ]);
        }

        return $this->usuarios->create([
            'cedula'   => $data['cedula'],
            'nombres'  => $data['nombres'],
            'password' => bin2hex(random_bytes(8)),
            'tipo'     => 'Usuario',
            'estado'   => $data['estado'],
        ]);
    }

    public function update(string $cedula, array $data): bool
    {
        $user = $this->usuarios->findByCedula($cedula);
        if (!$user) {
            return false;
        }

        return $this->usuarios->update($cedula, [
            'nombres'  => $data['nombres'],
            'tipo'     => $user['Tipo'],
            'estado'   => $data['estado'],
            'password' => '',
        ]);
    }

    public function delete(string $cedula): bool
    {
        return $this->usuarios->delete($cedula);
    }

    public function count(): int
    {
        return count($this->usuarios->getAll());
    }

    public function getActivos(): array
    {
        return $this->usuarios->getActivosParaPrestamo();
    }
}
