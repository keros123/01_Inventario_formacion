<?php
/**
 * Convierte a MAYÚSCULAS los datos de texto en la base de datos.
 *
 * Excluye:
 *   - Usuarios.Password (y cualquier columna llamada Password)
 *   - Columnas ENUM (Tipo, Estado, etc.) para no romper la aplicación
 *   - Rutas de archivos (Ruta, Fotografia)
 *   - Columnas numéricas, fechas y binarias
 *
 * Uso:
 *   php scripts/uppercase_database.php           (simulación)
 *   php scripts/uppercase_database.php --apply   (aplica cambios)
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/core/Database.php';

$apply = in_array('--apply', $argv ?? [], true);
$skippedTypes = [
    'tinyint', 'smallint', 'mediumint', 'int', 'bigint',
    'decimal', 'float', 'double', 'bit',
    'date', 'datetime', 'timestamp', 'time', 'year',
    'enum', 'set', 'json',
    'blob', 'tinyblob', 'mediumblob', 'longblob',
    'binary', 'varbinary',
];

$skippedColumns = [
    'password',
    'ruta',
    'fotografia',
];

echo "=== Mayúsculas en base de datos Inventario_cm ===\n";
echo $apply ? "Modo: APLICAR cambios\n\n" : "Modo: SIMULACIÓN (use --apply para ejecutar)\n\n";

$db = Database::getConnection();
$prefix = Database::prefix();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

if ($prefix !== '') {
    $tables = array_values(array_filter(
        $tables,
        static fn (string $table): bool => str_starts_with($table, $prefix)
    ));
}

if (empty($tables)) {
    echo "No se encontraron tablas.\n";
    exit(1);
}

$plan = [];

foreach ($tables as $table) {
    $stmt = $db->query('SHOW FULL COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        $field = $column['Field'];
        $type = strtolower((string) $column['Type']);

        if (in_array(strtolower($field), $skippedColumns, true)) {
            continue;
        }

        $isText = false;
        foreach (['char', 'varchar', 'text'] as $typePrefix) {
            if (str_starts_with($type, $typePrefix)) {
                $isText = true;
                break;
            }
        }

        if (!$isText) {
            continue;
        }

        foreach ($skippedTypes as $skipped) {
            if (str_starts_with($type, $skipped)) {
                $isText = false;
                break;
            }
        }

        if (!$isText) {
            continue;
        }

        $countStmt = $db->query(
            'SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`
             WHERE `' . str_replace('`', '``', $field) . '` IS NOT NULL
               AND `' . str_replace('`', '``', $field) . '` != \'\''
        );
        $total = (int) $countStmt->fetchColumn();

        if ($total === 0) {
            continue;
        }

        $plan[] = [
            'table'  => $table,
            'column' => $field,
            'total'  => $total,
        ];
    }
}

if (empty($plan)) {
    echo "No hay columnas de texto para actualizar.\n";
    exit(0);
}

foreach ($plan as $item) {
    echo sprintf(
        "- %s.%s (%d registro(s) con valor)\n",
        $item['table'],
        $item['column'],
        $item['total']
    );
}

if (!$apply) {
    echo "\nNingún cambio aplicado. Ejecute: php scripts/uppercase_database.php --apply\n";
    exit(0);
}

echo "\nAplicando cambios...\n";

$db->exec('SET FOREIGN_KEY_CHECKS = 0');
$db->beginTransaction();

try {
    $updatedTotal = 0;

    foreach ($plan as $item) {
        $table = str_replace('`', '``', $item['table']);
        $column = str_replace('`', '``', $item['column']);

        $sql = "UPDATE `{$table}`
                SET `{$column}` = UPPER(`{$column}`)
                WHERE `{$column}` IS NOT NULL
                  AND `{$column}` != ''
                  AND BINARY `{$column}` != BINARY UPPER(`{$column}`)";

        $affected = $db->exec($sql);
        $updatedTotal += max(0, (int) $affected);

        echo sprintf(
            "  %s.%s → %d fila(s) actualizada(s)\n",
            $item['table'],
            $item['column'],
            max(0, (int) $affected)
        );
    }

    $db->commit();
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "\nListo. Total de filas actualizadas: {$updatedTotal}\n";
    echo "Nota: contraseñas, ENUMs y rutas de archivos no fueron modificados.\n";
} catch (Throwable $e) {
    $db->rollBack();
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
