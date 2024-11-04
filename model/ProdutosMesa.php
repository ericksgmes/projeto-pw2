<?php

require_once __DIR__ . '/../config/database.php';

class ProdutosMesa {

    public static function listar(): array {
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
    }
    public static function listarDeletadas(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.id_mesa = m.id
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE pm.deletado = 1
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function adicionarProduto($id_mesa, $id_prod, $quantidade) {
        $connection = Connection::getConnection();

        // Verifica se a Mesa existe e não está deletada
        $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
        $sql->execute([$id_mesa]);
        if (!$sql->fetch()) {
            throw new Exception("Mesa não encontrada ou está deletada.", 404);
        }

        // Verifica se o Produto existe e não está deletado
        $sql = $connection->prepare("SELECT id FROM Produto WHERE id = ? AND deletado = 0");
        $sql->execute([$id_prod]);
        if (!$sql->fetch()) {
            throw new Exception("Produto não encontrado ou está deletado.", 404);
        }

        $sql = $connection->prepare("INSERT INTO ProdutosMesa (id_mesa, id_prod, quantidade) VALUES (?, ?, ?)");
        $sql->execute([$id_mesa, $id_prod, $quantidade]);

        return $connection->lastInsertId();
    }

    public static function removerProduto($id) {
        $connection = Connection::getConnection();

        $sql = $connection->prepare("DELETE FROM ProdutosMesa WHERE id = ?");
        $sql->execute([$id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível remover o produto da mesa", 500);
        }
    }

    public static function atualizarQuantidade($id, $quantidade) {
        $connection = Connection::getConnection();

        $sql = $connection->prepare("UPDATE ProdutosMesa SET quantidade = ? WHERE id = ?");
        $sql->execute([$quantidade, $id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível atualizar a quantidade", 500);
        }
    }

    public static function getByMesaId($id_mesa): array {
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
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.id_mesa = m.id
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE pm.id = ? AND m.deletado = 0 AND p.deletado = 0
        ");
        $sql->execute([$id]);
        $produtoMesa = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$produtoMesa) {
            throw new Exception("Produto na mesa não encontrado", 404);
        }

        return $produtoMesa;
    }
}
