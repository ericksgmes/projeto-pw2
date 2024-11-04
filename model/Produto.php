<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");

class Produto
{
    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Produto WHERE deletado = 0");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function cadastrar($nome, $preco) {
        try {
            $connection = Connection::getConnection();

            // Verifica se o nome já existe entre os produtos ativos
            if (self::existsByName($nome)) {
                throw new Exception("Nome do produto já está em uso.");
            }

            $sql = $connection->prepare("INSERT INTO Produto(nome, preco) VALUES (?, ?)");
            $sql->execute([$nome, $preco]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Produto WHERE id = ? AND deletado = 0");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function deleteById($id){
        try {
            $connection = Connection::getConnection();

            // Marca o produto como deletado
            $sql = $connection->prepare("UPDATE Produto SET deletado = 1, data_deletado = NOW() WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Produto WHERE id = ? AND deletado = 0");
            $sql->execute([$id]);

            return $sql->fetchColumn() > 0;
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function existsByName($nome) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT id FROM Produto WHERE nome = ? AND deletado = 0");
            $sql->execute([$nome]);
            return (bool)$sql->fetch();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function atualizar($id, $nome, $preco) {
        try {
            $connection = Connection::getConnection();

            // Verifica se o produto existe e não está deletado
            if (!self::exist($id)) {
                throw new Exception("Produto não encontrado ou está deletado.");
            }

            // Verifica se o nome já está em uso por outro produto ativo
            $sql = $connection->prepare("SELECT id FROM Produto WHERE nome = ? AND deletado = 0 AND id != ?");
            $sql->execute([$nome, $id]);
            if ($sql->fetch()) {
                throw new Exception("Nome do produto já está em uso.");
            }

            $sql = $connection->prepare("UPDATE Produto SET nome = ?, preco = ? WHERE id = ? AND deletado = 0");
            $sql->execute([$nome, $preco, $id]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function restoreById($id){
        try {
            $connection = Connection::getConnection();

            // Verifica se o produto existe e está deletado
            $sql = $connection->prepare("SELECT nome FROM Produto WHERE id = ? AND deletado = 1");
            $sql->execute([$id]);
            $nome = $sql->fetchColumn();

            if (!$nome) {
                throw new Exception("Produto não encontrado ou não está deletado.");
            }

            // Verifica se o nome do produto já está em uso por outro produto ativo
            $sql = $connection->prepare("SELECT id FROM Produto WHERE nome = ? AND deletado = 0");
            $sql->execute([$nome]);
            if ($sql->fetch()) {
                throw new Exception("Nome do produto já está em uso por outro produto ativo.");
            }

            // Restaura o produto
            $sql = $connection->prepare("UPDATE Produto SET deletado = 0, data_deletado = NULL WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }
}
