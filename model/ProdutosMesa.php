<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");

class ProdutosMesa
{
    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("
                SELECT pm.*
                FROM ProdutosMesa pm
                INNER JOIN Mesa m ON pm.id_mesa = m.id
                INNER JOIN Produto p ON pm.id_prod = p.id
                WHERE m.deletado = 0 AND p.deletado = 0
            ");
            $sql->execute();

            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function adicionarProduto($id_mesa, $id_prod, $quantidade) {
        try {
            $connection = Connection::getConnection();

            // Verifica se a Mesa existe e não está deletada
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
            $sql->execute([$id_mesa]);
            if (!$sql->fetch()) {
                throw new Exception("Mesa não encontrada ou está deletada.");
            }

            // Verifica se o Produto existe e não está deletado
            $sql = $connection->prepare("SELECT id FROM Produto WHERE id = ? AND deletado = 0");
            $sql->execute([$id_prod]);
            if (!$sql->fetch()) {
                throw new Exception("Produto não encontrado ou está deletado.");
            }

            // Insere o produto na mesa
            $sql = $connection->prepare("INSERT INTO ProdutosMesa(id_mesa, id_prod, quantidade) VALUES (?, ?, ?)");
            $sql->execute([$id_mesa, $id_prod, $quantidade]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function removerProduto($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("DELETE FROM ProdutosMesa WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function atualizarQuantidade($id, $quantidade) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("UPDATE ProdutosMesa SET quantidade = ? WHERE id = ?");
            $sql->execute([$quantidade, $id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getByMesaId($id_mesa) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("
                SELECT pm.*
                FROM ProdutosMesa pm
                INNER JOIN Mesa m ON pm.id_mesa = m.id
                INNER JOIN Produto p ON pm.id_prod = p.id
                WHERE pm.id_mesa = ? AND m.deletado = 0 AND p.deletado = 0
            ");
            $sql->execute([$id_mesa]);

            return $sql->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("
                SELECT pm.*
                FROM ProdutosMesa pm
                INNER JOIN Mesa m ON pm.id_mesa = m.id
                INNER JOIN Produto p ON pm.id_prod = p.id
                WHERE pm.id = ? AND m.deletado = 0 AND p.deletado = 0
            ");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }
}
