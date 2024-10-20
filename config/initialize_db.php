<?php

require_once(__DIR__ . "/../config/database.php");

function initializeDatabase()
{
    try {
        $connection = new PDO("mysql:host=localhost", "root", "");
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = file_get_contents(__DIR__ . "/../init.sql");

        $connection->exec($sql);

        echo "Banco de dados inicializado com sucesso!";
    } catch (PDOException $e) {
        die("Erro ao inicializar o banco de dados: " . $e->getMessage());
    }
}

initializeDatabase();