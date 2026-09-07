<?php
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance();

try {
    // Update any existing DarDeBaja or similar to Dar_Baja
    $stmt = $db->prepare(Database::sql("UPDATE {Movimientos} SET Tipo = 'Dar_Baja' WHERE Tipo IN ('DarDeBaja', 'Dar de baja', 'Dar_de_baja')"));
    $rowsUpdated = $stmt->rowCount();
    
    echo "✅ Sistema actualizado correctamente!<br>";
    echo "Filas actualizadas: " . $rowsUpdated . "<br><br>";
    
    // Verify the change
    $stmt = $db->query(Database::sql("SELECT id_movimiento, Tipo, Consecutivo FROM {Movimientos} WHERE Tipo = 'Dar_Baja'"));
    $movimientos = $stmt->fetchAll();
    
    if ($movimientos) {
        echo "Movimientos de Dar_baja encontrados:<br>";
        foreach ($movimientos as $mov) {
            echo "- ID: " . $mov['id_movimiento'] . ", Tipo: " . $mov['Tipo'] . ", Consecutivo: " . $mov['Consecutivo'] . "<br>";
        }
    } else {
        echo "No hay movimientos de Dar_baja en la base de datos.<br>";
    }
    
    echo "<br><a href='/Inventario_cm/movimientos'>Volver a movimientos</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>