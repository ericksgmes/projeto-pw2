<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");


class Produto
{

    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Produto");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function cadastrar($preco, $nome) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("INSERT INTO Produto(preco, nome) VALUES (?,?)");
            $sql->execute([$preco, $nome]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Produto WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function deleteById($id){
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("DELETE FROM Produto WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Produto WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetchColumn();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function existsByName($nome) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT id FROM Produto WHERE nome = ?");
            $sql->execute([$nome]);
            return (bool)$sql->fetch();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }
    public static function atualizar($id, paymentMethodEnum $metodo, $preco) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("UPDATE Produto SET metodo = ?, preco = ? WHERE id = ?");
            $sql->execute([$metodo->value, $preco, $id]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

}