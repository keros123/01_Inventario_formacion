<?php
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getConnection();

    echo "Aplicando cambios a la base de datos...\n";

    // 1. Add 'Dar_Baja' to Movimientos.Tipo enum
    echo "1. Actualizando enum Tipo en Movimientos...\n";
    $db->exec(Database::sql("ALTER TABLE {Movimientos} MODIFY COLUMN Tipo ENUM('Ingreso', 'Prestamo', 'Devolucion', 'Dar_Baja') NOT NULL"));
    echo "   ✓ Listo\n";

    // 2. Add 'Eliminado' to Inventario.Estado enum
    echo "2. Actualizando enum Estado en Inventario...\n";
    $db->exec(Database::sql("ALTER TABLE {Inventario} MODIFY COLUMN Estado ENUM('Activo', 'Inactivo', 'Eliminado') NOT NULL DEFAULT 'Activo'"));
    echo "   ✓ Listo\n";

    echo "\n✅ Todos los cambios aplicados exitosamente!\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
