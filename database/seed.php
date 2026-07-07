<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;charset=utf8mb4',
        $config['db']['host'],
        $config['db']['port']
    );

    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db']['name']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$config['db']['name']}`");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(255) NOT NULL,
            email      VARCHAR(255) NOT NULL UNIQUE,
            password   VARCHAR(255) NOT NULL,
            role       ENUM('admin', 'user') NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute(['email' => 'admin@example.com']);

    if ($stmt->fetchColumn() > 0) {
        echo "Admin user already exists. Skipping.\n";
        exit(0);
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (:name, :email, :password, :role)
    ");

    $stmt->execute([
        'name'     => 'Admin',
        'email'    => 'admin@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'role'     => 'admin',
    ]);

    echo "Default admin user created successfully.\n";
    echo "Email:    admin@example.com\n";
    echo "Password: password123\n";
} catch (PDOException $e) {
    echo "Seed failed: " . $e->getMessage() . "\n";
    exit(1);
}
