<?php

require_once __DIR__ . '/../config/database.php';

/**
 * @OA\Schema(
 *     schema="ProdutosMesa",
 *     type="object",
 *     title="ProdutosMesa",
 *     description="Modelo de produtos associados a uma mesa",
 *     @OA\Property(property="id", type="integer", description="ID da associação produto-mesa"),
 *     @OA\Property(property="id_mesa", type="integer", description="ID da mesa"),
 *     @OA\Property(property="id_prod", type="integer", description="ID do produto"),
 *     @OA\Property(property="quantidade", type="integer", description="Quantidade do produto")
 * )
 */
class ProdutosMesa {

    /**
     * @OA\Get(
     *     path="/produtos-mesa",
     *     summary="Listar todos os produtos associados a mesas",
     *     @OA\Response(response="200", description="Lista de produtos associados a mesas")
     * )
     */
    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT pm.* FROM ProdutosMesa pm INNER JOIN Mesa m ON pm.id_mesa = m.id INNER JOIN Produto p ON pm.id_prod = p.id WHERE m.deletado = 0 AND p.deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Get(
     *     path="/produtos-mesa/deletados",
     *     summary="Listar todos os produtos deletados de mesas",
     *     @OA\Response(response="200", description="Lista de produtos deletados de mesas")
     * )
     */
    public static function listarDeletadas(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT pm.* FROM ProdutosMesa pm INNER JOIN Mesa m ON pm.id_mesa = m.id INNER JOIN Produto p ON pm.id_prod = p.id WHERE pm.deletado = 1");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Post(
     *     path="/produtos-mesa",
     *     summary="Adicionar um produto a uma mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_mesa", "id_prod", "quantidade"},
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa"),
     *             @OA\Property(property="id_prod", type="integer", description="ID do produto"),
     *             @OA\Property(property="quantidade", type="integer", description="Quantidade do produto")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Produto adicionado à mesa com sucesso"),
     *     @OA\Response(response="404", description="Mesa ou produto não encontrado ou deletado")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/produtos-mesa/{id}",
     *     summary="Remover um produto de uma mesa",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Produto removido da mesa com sucesso"),
     *     @OA\Response(response="500", description="Erro ao remover o produto da mesa")
     * )
     */
    public static function removerProduto($id) {
        $connection = Connection::getConnection();

        $sql = $connection->prepare("DELETE FROM ProdutosMesa WHERE id = ?");
        $sql->execute([$id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível remover o produto da mesa", 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/produtos-mesa/{id}",
     *     summary="Atualizar a quantidade de um produto em uma mesa",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantidade"},
     *             @OA\Property(property="quantidade", type="integer", description="Quantidade do produto")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Quantidade do produto atualizada com sucesso"),
     *     @OA\Response(response="500", description="Erro ao atualizar a quantidade")
     * )
     */
    public static function atualizarQuantidade($id, $quantidade) {
        $connection = Connection::getConnection();

        $sql = $connection->prepare("UPDATE ProdutosMesa SET quantidade = ? WHERE id = ?");
        $sql->execute([$quantidade, $id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível atualizar a quantidade", 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/produtos-mesa/mesa/{id_mesa}",
     *     summary="Obter todos os produtos associados a uma mesa",
     *     @OA\Parameter(
     *         name="id_mesa",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Lista de produtos associados a uma mesa")
     * )
     */
    public static function getByMesaId($id_mesa): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT pm.*, p.nome AS nome_produto, p.preco AS preco_produto FROM ProdutosMesa pm INNER JOIN Mesa m ON pm.id_mesa = m.id INNER JOIN Produto p ON pm.id_prod = p.id WHERE pm.id_mesa = ? AND m.deletado = 0 AND p.deletado = 0");
        $sql->execute([$id_mesa]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Get(
     *     path="/produtos-mesa/{id}",
     *     summary="Obter detalhes de um produto associado a uma mesa",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Detalhes do produto associado a uma mesa"),
     *     @OA\Response(response="404", description="Produto na mesa não encontrado")
     * )
     */
    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT pm.* FROM ProdutosMesa pm INNER JOIN Mesa m ON pm.id_mesa = m.id INNER JOIN Produto p ON pm.id_prod = p.id WHERE pm.id = ? AND m.deletado = 0 AND p.deletado = 0");
        $sql->execute([$id]);
        $produtoMesa = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$produtoMesa) {
            throw new Exception("Produto na mesa não encontrado", 404);
        }

        return $produtoMesa;
    }
}