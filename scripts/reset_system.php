<?php
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getConnection();
    $db->beginTransaction();

    echo "Limpiando tablas...\n";

    // 1. Delete all from Det_Movimientos first (foreign key constraint)
    $db->exec(Database::sql('DELETE FROM {Det_Movimientos}'));
    echo "  ✓ Det_Movimientos vaciada\n";

    // 2. Delete all from Movimientos
    $db->exec(Database::sql('DELETE FROM {Movimientos}'));
    echo "  ✓ Movimientos vaciada\n";

    // 3. Reset all inventory quantities to 0
    $stmt = $db->prepare(Database::sql('UPDATE {Inventario} SET Cantidad = 0'));
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "  ✓ Cantidades de inventario reiniciadas a 0 ($count elementos)\n";

    $db->commit();

    echo "\n✅ Operación completada exitosamente!\n";
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}
