<?php

require_once __DIR__ . '/../config/database.php';

/**
 * @OA\Schema(
 *     schema="Mesa",
 *     type="object",
 *     title="Mesa",
 *     description="Modelo de mesa",
 *     @OA\Property(property="id", type="integer", description="ID da mesa"),
 *     @OA\Property(property="numero", type="integer", description="Número da mesa"),
 *     @OA\Property(property="deletado", type="boolean", description="Indica se a mesa está deletada"),
 *     @OA\Property(property="data_deletado", type="string", format="date-time", description="Data em que a mesa foi deletada")
 * )
 */
class Mesa {

    /**
     * @OA\Get(
     *     path="/mesas",
     *     summary="Listar todas as mesas",
     *     @OA\Response(response="200", description="Lista de mesas")
     * )
     */
    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Mesa WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Get(
     *     path="/mesas/deletadas",
     *     summary="Listar todas as mesas deletadas",
     *     @OA\Response(response="200", description="Lista de mesas deletadas")
     * )
     */
    public static function listarDeletadas(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Mesa WHERE deletado = 1");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Post(
     *     path="/mesas",
     *     summary="Criar uma nova mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero"},
     *             @OA\Property(property="numero", type="integer", description="Número da mesa")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Mesa criada com sucesso"),
     *     @OA\Response(response="400", description="Número da mesa inválido ou já em uso")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/mesas/{id}",
     *     summary="Obter detalhes de uma mesa",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Detalhes da mesa"),
     *     @OA\Response(response="404", description="Mesa não encontrada")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/mesas/{id}",
     *     summary="Atualizar uma mesa",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numero"},
     *             @OA\Property(property="numero", type="integer", description="Número da mesa")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Mesa atualizada com sucesso"),
     *     @OA\Response(response="400", description="Número da mesa inválido ou já em uso"),
     *     @OA\Response(response="404", description="Mesa não encontrada")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/mesas/{id}",
     *     summary="Deletar uma mesa",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Mesa deletada com sucesso"),
     *     @OA\Response(response="404", description="Mesa não encontrada")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/mesas/exist",
     *     summary="Verificar se uma mesa existe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", description="ID da mesa")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Mesa encontrada"),
     *     @OA\Response(response="404", description="Mesa não encontrada")
     * )
     */
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