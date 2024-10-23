<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");

class Mesa
{

    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Mesa");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function criar($numero) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("INSERT INTO Mesa(numero) VALUE (?)");
            $sql->execute([$numero]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Mesa WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function deleteById($id){
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("DELETE FROM Mesa WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Mesa WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetchColumn();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function existsByNumber($number) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE numero = ?");
            $sql->execute([$number]);
            return (bool)$sql->fetch();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function atualizar($id, $numero) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("UPDATE Mesa SET numero = ? WHERE id = ?");
            $sql->execute([$numero, $id]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

}