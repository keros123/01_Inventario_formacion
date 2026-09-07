<?php

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Migrator.php';

Migrator::run();

$db = Database::getConnection();
$tables = $db->query("SHOW TABLES LIKE " . $db->quote('%' . Database::prefix() . '%solicitud%'))->fetchAll(PDO::FETCH_COLUMN);

echo "Migración ejecutada.\n";
echo "Tablas: " . (empty($tables) ? '(ninguna)' : implode(', ', $tables)) . "\n";
