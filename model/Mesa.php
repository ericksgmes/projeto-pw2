<?php

require_once __DIR__ . '/../config/database.php';

class Mesa {

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Mesa WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarDeletadas(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Mesa WHERE deletado = 1");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function criar($numero) {
        $connection = Connection::getConnection();

        if ($numero <= 0) {
            throw new Exception("O número da mesa deve ser maior que zero.", 400);
        }

        if (self::existsByNumber($numero)) {
            throw new Exception("O número da mesa já está em uso.", 409);
        }

        $sql = $connection->prepare("INSERT INTO Mesa (numero) VALUES (?)");
        $sql->execute([$numero]);

        return $connection->lastInsertId();
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Mesa WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);
        $mesa = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$mesa) {
            throw new Exception("Mesa não encontrada", 404);
        }

        return $mesa;
    }

    public static function atualizar($id, $numero) {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Mesa não encontrada", 404);
        }

        if ($numero <= 0) {
            throw new Exception("O número da mesa deve ser maior que zero.", 400);
        }

        $sql = $connection->prepare("SELECT id FROM Mesa WHERE numero = ? AND deletado = 0 AND id != ?");
        $sql->execute([$numero, $id]);
        if ($sql->fetch()) {
            throw new Exception("O número da mesa já está em uso por outra mesa.", 409);
        }

        $sql = $connection->prepare("UPDATE Mesa SET numero = ? WHERE id = ? AND deletado = 0");
        $sql->execute([$numero, $id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível atualizar a mesa", 500);
        }
    }

    public static function deleteById($id): int {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Mesa não encontrada", 404);
        }

        $sql = $connection->prepare("UPDATE Mesa SET deletado = 1, data_deletado = NOW() WHERE id = ?");
        $sql->execute([$id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível deletar a mesa", 500);
        }

        return $sql->rowCount();
    }

    public static function exist($id): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT COUNT(*) FROM Mesa WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);

        return $sql->fetchColumn() > 0;
    }

    public static function existsByNumber($numero) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT id FROM Mesa WHERE numero = ? AND deletado = 0");
        $sql->execute([$numero]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }
}
