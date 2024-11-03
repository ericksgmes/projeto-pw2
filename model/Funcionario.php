<?php

require_once __DIR__ . '/../config/database.php';

class Funcionario {

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Funcionario WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function cadastrar($nome, $username, $senha) {
        $connection = Connection::getConnection();

        if (self::existsByUsername($username)) {
            throw new Exception("O username já existe. Tente outro.", 409);
        }

        $senhaHashed = password_hash($senha, PASSWORD_BCRYPT);
        $sql = $connection->prepare("INSERT INTO Funcionario (nome, username, senha) VALUES (?, ?, ?)");
        $sql->execute([$nome, $username, $senhaHashed]);

        return $connection->lastInsertId();
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Funcionario WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);
        $funcionario = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$funcionario) {
            throw new Exception("Funcionário não encontrado", 404);
        }

        return $funcionario;
    }

    public static function atualizar($id, $nome, $username) {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Funcionário não encontrado", 404);
        }

        $funcionarioExistente = self::getByUsername($username);
        if ($funcionarioExistente && $funcionarioExistente['id'] != $id) {
            throw new Exception("O username já está em uso por outro funcionário.", 409);
        }

        $sql = $connection->prepare("UPDATE Funcionario SET nome = ?, username = ? WHERE id = ?");
        $sql->execute([$nome, $username, $id]);

        return $sql->rowCount();
    }

    public static function deleteById($id): int {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Funcionário não encontrado", 404);
        }

        $sql = $connection->prepare("UPDATE Funcionario SET deletado = 1, data_deletado = NOW() WHERE id = ?");
        $sql->execute([$id]);

        return $sql->rowCount();
    }

    public static function exist($id): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT COUNT(*) FROM Funcionario WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);
        return $sql->fetchColumn() > 0;
    }

    public static function existsByUsername($username): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT id FROM Funcionario WHERE username = ? AND deletado = 0");
        $sql->execute([$username]);
        return (bool) $sql->fetch();
    }

    public static function getByUsername($username) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Funcionario WHERE username = ? AND deletado = 0");
        $sql->execute([$username]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }
}