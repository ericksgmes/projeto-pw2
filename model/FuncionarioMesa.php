<?php

require_once(__DIR__ . "/../config/database.php");

class FuncionarioMesa {

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT fm.*
            FROM FuncionarioMesa fm
            INNER JOIN Funcionario f ON fm.id_funcionario = f.id
            INNER JOIN Mesa m ON fm.id_mesa = m.id
            WHERE f.deletado = 0 AND m.deletado = 0
        ");
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function associar($id_funcionario, $id_mesa): int {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("INSERT INTO FuncionarioMesa (id_funcionario, id_mesa) VALUES (?, ?)");
        $sql->execute([$id_funcionario, $id_mesa]);

        return $sql->rowCount();
    }

    public static function desassociar($id_funcionario, $id_mesa): int {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("DELETE FROM FuncionarioMesa WHERE id_funcionario = ? AND id_mesa = ?");
        $sql->execute([$id_funcionario, $id_mesa]);

        return $sql->rowCount();
    }

    public static function getByFuncionarioId($id_funcionario): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT fm.*
            FROM FuncionarioMesa fm
            INNER JOIN Funcionario f ON fm.id_funcionario = f.id
            INNER JOIN Mesa m ON fm.id_mesa = m.id
            WHERE fm.id_funcionario = ? AND f.deletado = 0 AND m.deletado = 0
        ");
        $sql->execute([$id_funcionario]);

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function exist($id_funcionario, $id_mesa): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT COUNT(*)
            FROM FuncionarioMesa fm
            INNER JOIN Funcionario f ON fm.id_funcionario = f.id
            INNER JOIN Mesa m ON fm.id_mesa = m.id
            WHERE fm.id_funcionario = ? AND fm.id_mesa = ? AND f.deletado = 0 AND m.deletado = 0
        ");
        $sql->execute([$id_funcionario, $id_mesa]);

        return $sql->fetchColumn() > 0;
    }
}