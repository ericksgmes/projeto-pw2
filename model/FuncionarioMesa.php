<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");

class FuncionarioMesa
{
    public static function listar() {
        try {
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
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function associar($id_funcionario, $id_mesa) {
        try {
            $connection = Connection::getConnection();

            // Verifica se o Funcionário existe e não está deletado
            $sql = $connection->prepare("SELECT id FROM Funcionario WHERE id = ? AND deletado = 0");
            $sql->execute([$id_funcionario]);
            if (!$sql->fetch()) {
                throw new Exception("Funcionário não encontrado ou está deletado.");
            }

            // Verifica se a Mesa existe e não está deletada
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
            $sql->execute([$id_mesa]);
            if (!$sql->fetch()) {
                throw new Exception("Mesa não encontrada ou está deletada.");
            }

            // Insere a associação
            $sql = $connection->prepare("INSERT INTO FuncionarioMesa(id_funcionario, id_mesa) VALUES (?, ?)");
            $sql->execute([$id_funcionario, $id_mesa]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function desassociar($id_funcionario, $id_mesa){
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("DELETE FROM FuncionarioMesa WHERE id_funcionario = ? AND id_mesa = ?");
            $sql->execute([$id_funcionario, $id_mesa]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getByFuncionarioId($id_funcionario) {
        try {
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
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getByMesaId($id_mesa) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("
                SELECT fm.*
                FROM FuncionarioMesa fm
                INNER JOIN Funcionario f ON fm.id_funcionario = f.id
                INNER JOIN Mesa m ON fm.id_mesa = m.id
                WHERE fm.id_mesa = ? AND f.deletado = 0 AND m.deletado = 0
            ");
            $sql->execute([$id_mesa]);

            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id_funcionario, $id_mesa) {
        try {
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
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }
}