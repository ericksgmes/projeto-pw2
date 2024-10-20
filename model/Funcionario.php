<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");

class Funcionario
{

    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Funcionario");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public function login($username, $senha) {
        try {
            $connection = Connection::getConnection();

            $sql = $connection->prepare("SELECT * FROM Funcionario WHERE username = ?");
            $sql->execute([$username]);

            $funcionario = $sql->fetch(PDO::FETCH_ASSOC);

            if ($funcionario && password_verify($senha, $funcionario['senha'])) {
                return true;
            } else {
                return false;
            }

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function cadastrar($nome, $username, $senha) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("INSERT INTO Funcionario(nome, username, senha) VALUES (?,?,?)");
            $senhaHashed = password_hash($senha, PASSWORD_BCRYPT);
            $sql->execute([$nome, $username, $senhaHashed]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Funcionario WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public function deleteById($id){
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("DELETE FROM Funcionario WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Funcionario WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetchColumn();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function existsByUsername($username) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT id FROM Funcionario WHERE username = ?");
            $sql->execute([$username]);
            return (bool)$sql->fetch();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

}