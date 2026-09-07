<?php

require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getConnection();

    // Check if old admin exists
    $stmt = $db->prepare(Database::sql("SELECT * FROM {Usuarios} WHERE Cedula = '1234567890'"));
    $stmt->execute();
    $oldAdmin = $stmt->fetch();

    if ($oldAdmin) {
        // Update the old admin to new credentials
        $updateStmt = $db->prepare(Database::sql("
            UPDATE {Usuarios}
            SET Cedula = 'admin', 
                Password = :password 
            WHERE Cedula = '1234567890'
        "));
        $updateStmt->execute([
            'password' => password_hash('123456', PASSWORD_DEFAULT)
        ]);
        echo "Admin actualizado exitosamente!\n";
        echo "Nuevo usuario: admin\n";
        echo "Nueva contraseña: 123456\n";
    } else {
        // Check if new admin already exists
        $stmt = $db->prepare(Database::sql("SELECT * FROM {Usuarios} WHERE Cedula = 'admin'"));
        $stmt->execute();
        $newAdmin = $stmt->fetch();

        if ($newAdmin) {
            // Update password
            $updateStmt = $db->prepare(Database::sql("
                UPDATE {Usuarios}
                SET Password = :password 
                WHERE Cedula = 'admin'
            "));
            $updateStmt->execute([
                'password' => password_hash('123456', PASSWORD_DEFAULT)
            ]);
            echo "Contraseña del admin actualizada exitosamente!\n";
            echo "Usuario: admin\n";
            echo "Contraseña: 123456\n";
        } else {
            // Insert new admin
            $insertStmt = $db->prepare(Database::sql("
                INSERT INTO {Usuarios} (Cedula, Nombres, Password, Tipo, Estado)
                VALUES ('admin', 'Administrador Sistema', :password, 'Admin', 'Activo')
            "));
            $insertStmt->execute([
                'password' => password_hash('123456', PASSWORD_DEFAULT)
            ]);
            echo "Admin creado exitosamente!\n";
            echo "Usuario: admin\n";
            echo "Contraseña: 123456\n";
        }
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
