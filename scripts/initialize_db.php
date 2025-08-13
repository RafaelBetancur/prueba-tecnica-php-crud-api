<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$config = include __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']}",
        $config['username'],
        $config['password']
    );
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Usar la base de datos
    $pdo->exec("USE {$config['database']}");
    
    // Crear tabla api_results
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS api_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            value INT NOT NULL,
            category ENUM('bad', 'medium', 'good') NOT NULL,
            attempt_number INT NOT NULL DEFAULT 1,
            is_improved TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Crear tabla execution_logs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS execution_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            total_initial_calls INT NOT NULL,
            total_sweeps INT NOT NULL,
            total_calls INT NOT NULL,
            bad_count INT NOT NULL,
            medium_count INT NOT NULL,
            good_count INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    echo "Database and tables created successfully!\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}