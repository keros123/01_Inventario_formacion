<?php
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getConnection();

    // Find all elements in Det_Movimientos that are missing from Inventario
    $stmt = $db->query(Database::sql('
        SELECT DISTINCT d.Codigo_elemento 
        FROM {Det_Movimientos} d
        LEFT JOIN {Inventario} i ON i.Codigo = d.Codigo_elemento
        WHERE i.Codigo IS NULL
    '));
    $missingElements = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($missingElements)) {
        echo "No hay elementos perdidos para restaurar.\n";
        exit;
    }

    echo "Restaurando " . count($missingElements) . " elementos...\n";

    foreach ($missingElements as $codigo) {
        echo "Restaurando: $codigo\n";
        
        $stmt = $db->prepare(Database::sql('
            INSERT INTO {Inventario} (Codigo, Elemento, Consumible, Descripcion, Cantidad, Fotografia, Estado)
            VALUES (:codigo, :elemento, :consumible, :descripcion, :cantidad, :fotografia, :estado)
        '));
        $stmt->execute([
            'codigo' => $codigo,
            'elemento' => "[Elemento restaurado] $codigo",
            'consumible' => 'no',
            'descripcion' => 'Elemento restaurado automáticamente para preservar historial de movimientos',
            'cantidad' => 0,
            'fotografia' => null,
            'estado' => 'Eliminado'
        ]);
    }

    echo "Restauración completada!\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
