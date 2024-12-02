<?php

require_once(__DIR__ . "/../config/database.php");

/**
 * @OA\Schema(
 *     schema="UsuarioMesa",
 *     type="object",
 *     title="UsuarioMesa",
 *     description="Modelo de associação entre Usuário e Mesa",
 *     @OA\Property(property="id_usuario", type="integer", description="ID do usuário associado"),
 *     @OA\Property(property="id_mesa", type="integer", description="ID da mesa associada")
 * )
 */
class UsuarioMesa
{

    /**
     * @OA\Get(
     *     path="/usuario-mesa",
     *     summary="Listar todas as associações entre usuários e mesas",
     *     @OA\Response(response="200", description="Lista de associações entre usuários e mesas")
     * )
     */
    public static function listar(): array
    {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT um.* FROM UsuarioMesa um INNER JOIN Usuario u ON um.id_usuario = u.id INNER JOIN Mesa m ON um.id_mesa = m.id WHERE u.deletado = 0 AND m.deletado = 0");
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Post(
     *     path="/usuario-mesa",
     *     summary="Associar um usuário a uma mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_usuario", "id_mesa"},
     *             @OA\Property(property="id_usuario", type="integer", description="ID do usuário a ser associado"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa a ser associada")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Associação criada com sucesso"),
     *     @OA\Response(response="404", description="Usuário ou mesa não encontrado")
     * )
     */
    public static function associar($id_usuario, $id_mesa): int
    {
        $connection = Connection::getConnection();

        $usuarioCheck = $connection->prepare("SELECT id FROM Usuario WHERE id = ? AND deletado = 0");
        $usuarioCheck->execute([$id_usuario]);
        if (!$usuarioCheck->fetch()) {
            throw new Exception("Usuário não encontrado ou deletado.", 404);
        }

        $mesaCheck = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
        $mesaCheck->execute([$id_mesa]);
        if (!$mesaCheck->fetch()) {
            throw new Exception("Mesa não encontrada ou deletada.", 404);
        }

        $sql = $connection->prepare("INSERT INTO UsuarioMesa (id_usuario, id_mesa) VALUES (?, ?)");
        $sql->execute([$id_usuario, $id_mesa]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Erro ao associar o usuário à mesa.", 500);
        }

        return $sql->rowCount();
    }

    /**
     * @OA\Delete(
     *     path="/usuario-mesa",
     *     summary="Desassociar um usuário de uma mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_usuario", "id_mesa"},
     *             @OA\Property(property="id_usuario", type="integer", description="ID do usuário a ser desassociado"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa a ser desassociada")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Associação removida com sucesso"),
     *     @OA\Response(response="404", description="Associação não encontrada")
     * )
     */
    public static function desassociar($id_usuario, $id_mesa): int
    {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("DELETE FROM UsuarioMesa WHERE id_usuario = ? AND id_mesa = ?");
        $sql->execute([$id_usuario, $id_mesa]);

        return $sql->rowCount();
    }

    /**
     * @OA\Get(
     *     path="/usuario-mesa/{id_usuario}",
     *     summary="Obter todas as mesas associadas a um usuário",
     *     @OA\Parameter(
     *         name="id_usuario",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Lista de mesas associadas ao usuário"),
     *     @OA\Response(response="404", description="Nenhuma associação encontrada para o usuário")
     * )
     */
    public static function getByUsuarioId($id_usuario): array
    {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT um.* FROM UsuarioMesa um INNER JOIN Usuario u ON um.id_usuario = u.id INNER JOIN Mesa m ON um.id_mesa = m.id WHERE um.id_usuario = ? AND u.deletado = 0 AND m.deletado = 0");
        $sql->execute([$id_usuario]);

        $result = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            throw new Exception("Nenhuma associação encontrada para o usuário", 404);
        }

        return $result;
    }

    /**
     * @OA\Get(
     *     path="/usuario-mesa/exist",
     *     summary="Verificar se uma associação entre usuário e mesa existe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_usuario", "id_mesa"},
     *             @OA\Property(property="id_usuario", type="integer", description="ID do usuário"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Associação encontrada"),
     *     @OA\Response(response="404", description="Associação não encontrada")
     * )
     */
    public static function exist($id_usuario, $id_mesa): bool
    {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT COUNT(*) FROM UsuarioMesa um INNER JOIN Usuario u ON um.id_usuario = u.id INNER JOIN Mesa m ON um.id_mesa = m.id WHERE um.id_usuario = ? AND um.id_mesa = ? AND u.deletado = 0 AND m.deletado = 0");
        $sql->execute([$id_usuario, $id_mesa]);

        return $sql->fetchColumn() > 0;
    }
}
