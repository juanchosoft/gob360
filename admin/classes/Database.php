<?php

class Database {
    private static $connection;

    public static function getConnection() {
        if (!self::$connection) {
            self::$connection = new PDO('mysql:host=localhost;dbname=mi_base_datos', 'usuario', 'contraseña', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$connection;
    }
}
