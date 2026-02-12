<?php
declare(strict_types=1);

/**
 * Centralized DB connection.
 * Recommended: set environment variables in Apache/Nginx or .env loader:
 * DB_HOST, DB_NAME, DB_USER, DB_PASS
 */

final class DB {
    private static ?PDO $pdo = null;

    public static function conn(): PDO {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $name = getenv('DB_NAME') ?: 'deenlink_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        self::$pdo = new PDO($dsn, $user, $pass, $options);
        return self::$pdo;
    }
}