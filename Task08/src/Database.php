<?php
// src/Database.php

class Database {
    private static $pdo;

    public static function getConnection() {
        if (!self::$pdo) {
            $dbPath = __DIR__ . '/../data/database.sqlite';
            try {
                self::$pdo = new PDO("sqlite:" . $dbPath);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->exec("PRAGMA foreign_keys = ON;");
            } catch (PDOException $e) {
                die("Ошибка подключения к БД: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}