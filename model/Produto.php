<?php

require_once __DIR__ . '/../config/database.php';

/**
 * @OA\Schema(
 *     schema="Produto",
 *     type="object",
 *     title="Produto",
 *     description="Modelo de produto",
 *     @OA\Property(property="id", type="integer", description="ID do produto"),
 *     @OA\Property(property="nome", type="string", description="Nome do produto"),
 *     @OA\Property(property="preco", type="number", format="float", description="Preço do produto"),
 *     @OA\Property(property="deletado", type="boolean", description="Indica se o produto está deletado"),
 *     @OA\Property(property="data_deletado", type="string", format="date-time", description="Data em que o produto foi deletado")
 * )
 */
class Produto {

    /**
     * @OA\Get(
     *     path="/produtos",
     *     summary="Listar todos os produtos",
     *     @OA\Response(response="200", description="Lista de produtos")
     * )
     */
    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Produto WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Get(
     *     path="/produtos/deletados",
     *     summary="Listar todos os produtos deletados",
     *     @OA\Response(response="200", description="Lista de produtos deletados")
     * )
     */
    public static function listarDeletados(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Produto WHERE deletado = 1");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Post(
     *     path="/produtos",
     *     summary="Cadastrar um novo produto",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "preco"},
     *             @OA\Property(property="nome", type="string", description="Nome do produto"),
     *             @OA\Property(property="preco", type="number", format="float", description="Preço do produto")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Produto cadastrado com sucesso"),
     *     @OA\Response(response="409", description="Nome do produto já está em uso")
     * )
     */
    public static function cadastrar($nome, $preco) {
        $connection = Connection::getConnection();

        if (self::existsByName($nome)) {
            throw new Exception("O nome do produto já está em uso.", 409);
        }

        $sql = $connection->prepare("INSERT INTO Produto (nome, preco) VALUES (?, ?)");
        $sql->execute([$nome, $preco]);

        return $connection->lastInsertId();
    }

    /**
     * @OA\Get(
     *     path="/produtos/{id}",
     *     summary="Obter detalhes de um produto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Detalhes do produto"),
     *     @OA\Response(response="404", description="Produto não encontrado")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/produtos/{id}",
     *     summary="Atualizar um produto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "preco"},
     *             @OA\Property(property="nome", type="string", description="Nome do produto"),
     *             @OA\Property(property="preco", type="number", format="float", description="Preço do produto")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Produto atualizado com sucesso"),
     *     @OA\Response(response="404", description="Produto não encontrado"),
     *     @OA\Response(response="409", description="Nome do produto já está em uso por outro produto")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/produtos/{id}",
     *     summary="Deletar um produto",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Produto deletado com sucesso"),
     *     @OA\Response(response="404", description="Produto não encontrado")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/produtos/exist",
     *     summary="Verificar se um produto existe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", description="ID do produto")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Produto encontrado"),
     *     @OA\Response(response="404", description="Produto não encontrado")
     * )
     */
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