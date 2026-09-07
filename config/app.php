<?php

require_once __DIR__ . '/../app/core/App.php';

return [
    'name'              => 'Inventarios Ambientes de Formación CCyS',
    'base_url'          => App::baseUrl(),
    'upload_dir'        => __DIR__ . '/../public/uploads/inventario/',
    'upload_dar_baja'   => __DIR__ . '/../public/uploads/dar-baja/',
];
