<?php

require_once __DIR__ . '/../config/database.php';

/**
 * @OA\Schema(
 *     schema="ProdutosMesa",
 *     type="object",
 *     title="ProdutosMesa",
 *     description="Modelo de produtos associados a uma mesa",
 *     @OA\Property(property="id", type="integer", description="ID da associação produto-mesa"),
 *     @OA\Property(property="numero_mesa", type="string", description="Número da mesa"),
 *     @OA\Property(property="id_prod", type="integer", description="ID do produto"),
 *     @OA\Property(property="quantidade", type="integer", description="Quantidade do produto")
 * )
 */
class ProdutosMesa {

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*, m.numero AS numero_mesa, p.nome AS nome_produto, p.preco AS preco_produto 
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE m.deletado = 0 AND p.deletado = 0
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarDeletadas(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*, m.numero AS numero_mesa, p.nome AS nome_produto
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE m.deletado = 1 OR p.deletado = 1
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function adicionarProduto($numero_mesa, $id_prod, $quantidade) {
        $connection = Connection::getConnection();

        // Verifica se a Mesa existe e não está deletada
        $sql = $connection->prepare("SELECT numero FROM Mesa WHERE numero = ? AND deletado = 0");
        $sql->execute([$numero_mesa]);
        if (!$sql->fetch()) {
            throw new Exception("Mesa não encontrada ou está deletada.", 404);
        }

        // Verifica se o Produto existe e não está deletado
        $sql = $connection->prepare("SELECT id FROM Produto WHERE id = ? AND deletado = 0");
        $sql->execute([$id_prod]);
        if (!$sql->fetch()) {
            throw new Exception("Produto não encontrado ou está deletado.", 404);
        }

        $sql = $connection->prepare("INSERT INTO ProdutosMesa (numero_mesa, id_prod, quantidade) VALUES (?, ?, ?)");
        $sql->execute([$numero_mesa, $id_prod, $quantidade]);

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

    public static function getByMesaNumero($numero_mesa): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*, p.nome AS nome_produto, p.preco AS preco_produto 
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE pm.numero_mesa = ? AND m.deletado = 0 AND p.deletado = 0
        ");
        $sql->execute([$numero_mesa]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*, m.numero AS numero_mesa, p.nome AS nome_produto, p.preco AS preco_produto
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
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
