<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");

class Funcionario
{
    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Funcionario WHERE deletado = 0");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public function login($username, $senha) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Funcionario WHERE username = ? AND deletado = 0");
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

            return $connection->lastInsertId();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Funcionario WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function deleteById($id){
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("UPDATE Funcionario SET deletado = 1, data_deletado = NOW() WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Funcionario WHERE id = ? AND deletado = 0");
            $sql->execute([$id]);
            return $sql->fetchColumn();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function existsByUsername($username) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT id FROM Funcionario WHERE username = ? AND deletado = 0");
            $sql->execute([$username]);
            return (bool)$sql->fetch();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function atualizar($id, $nome, $username) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("UPDATE Funcionario SET nome = ?, username = ? WHERE id = ?");
            $sql->execute([$nome, $username, $id]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function restoreById($id){
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("UPDATE Funcionario SET deletado = 0, data_deletado = NULL WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getByUsername($username) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Funcionario WHERE username = ? AND deletado = 0");
            $sql->execute([$username]);
            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }
}