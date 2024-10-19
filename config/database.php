<?php

class Connection {
    private static $instance;

    private function __construct()
    {
        $hostname = "localhost";
        $database = "restaurante";
        $username = "root";
        $password = "";

        $dsn = "mysql:host=$hostname;dbname=$database";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }

        public static function getConnection() {
            if  (!isset(self::$instance)) {
                new Connection();
            }
            return self::$instance;
        }
}

