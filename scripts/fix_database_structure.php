<?php
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getConnection();

    echo "Corrigiendo estructura de la base de datos...\n";

    // 1. Drop the existing Movimientos table (after backing up data if needed)
    echo "1. Eliminando tabla Movimientos existente...\n";
    $db->exec(Database::sql("DROP TABLE IF EXISTS {Movimientos}"));
    echo "   ✓ Listo\n";

    // 2. Create Movimientos table with correct structure
    echo "2. Creando tabla Movimientos con estructura correcta...\n";
    $db->exec(Database::sql("
        CREATE TABLE {Movimientos} (
            id_movimiento INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            Tipo ENUM('Ingreso', 'Prestamo', 'Devolucion', 'Dar_Baja') NOT NULL,
            Consecutivo INT NOT NULL,
            Fecha DATE NOT NULL,
            Cedula_cuentadante VARCHAR(20) DEFAULT NULL,
            Uso ENUM('Formacion', 'TIC') NOT NULL,
            Ficha VARCHAR(50) DEFAULT NULL,
            Descripcion TEXT,
            Estado ENUM('Activo', 'Inactivo', 'Cerrado') NOT NULL DEFAULT 'Activo',
            id_movimiento_ref INT DEFAULT NULL,
            CONSTRAINT fk_mov_cuentadante FOREIGN KEY (Cedula_cuentadante)
                REFERENCES {Cuentadantes}(Cedula) ON UPDATE CASCADE,
            CONSTRAINT fk_mov_ref FOREIGN KEY (id_movimiento_ref)
                REFERENCES {Movimientos}(id_movimiento) ON DELETE SET NULL
        ) ENGINE=InnoDB
    "));
    echo "   ✓ Listo\n";

    // 3. Fix Inventario table if needed
    echo "3. Verificando tabla Inventario...\n";
    $checkInventario = $db->query(Database::sql("SHOW COLUMNS FROM {Inventario} LIKE 'Estado'"));
    $colInventario = $checkInventario->fetch();
    if (strpos($colInventario['Type'], 'Eliminado') === false) {
        $db->exec(Database::sql("ALTER TABLE {Inventario} MODIFY COLUMN Estado ENUM('Activo', 'Inactivo', 'Eliminado') NOT NULL DEFAULT 'Activo'"));
        echo "   ✓ Actualizado Estado en Inventario\n";
    } else {
        echo "   ✓ Ya está correcto\n";
    }

    echo "\n✅ Estructura de la base de datos corregida exitosamente!\n";
    echo "Ahora ejecuta el script scripts/reset_system.php para limpiar y empezar de nuevo!\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
