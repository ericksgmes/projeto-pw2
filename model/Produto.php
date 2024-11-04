<?php

require_once __DIR__ . '/../config/database.php';

class Produto {

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Produto WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function listarDeletados(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Produto WHERE deletado = 1");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function cadastrar($nome, $preco) {
        $connection = Connection::getConnection();

        if (self::existsByName($nome)) {
            throw new Exception("O nome do produto já está em uso.", 409);
        }

        $sql = $connection->prepare("INSERT INTO Produto (nome, preco) VALUES (?, ?)");
        $sql->execute([$nome, $preco]);

        return $connection->lastInsertId();
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Produto WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);
        $produto = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            throw new Exception("Produto não encontrado", 404);
        }

        return $produto;
    }

    public static function atualizar($id, $nome, $preco) {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Produto não encontrado", 404);
        }

        $produtoExistente = self::getByName($nome);
        if ($produtoExistente && $produtoExistente['id'] != $id) {
            throw new Exception("O nome do produto já está em uso por outro produto.", 409);
        }

        $sql = $connection->prepare("UPDATE Produto SET nome = ?, preco = ? WHERE id = ? AND deletado = 0");
        $sql->execute([$nome, $preco, $id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível atualizar o produto", 500);
        }
    }

    public static function deleteById($id) {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Produto não encontrado", 404);
        }

        $sql = $connection->prepare("UPDATE Produto SET deletado = 1, data_deletado = NOW() WHERE id = ?");
        $sql->execute([$id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível deletar o produto", 500);
        }
    }

    public static function exist($id): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT COUNT(*) FROM Produto WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);

        return $sql->fetchColumn() > 0;
    }

    public static function existsByName($nome) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT id FROM Produto WHERE nome = ? AND deletado = 0");
        $sql->execute([$nome]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByName($nome) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Produto WHERE nome = ? AND deletado = 0");
        $sql->execute([$nome]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }
}
