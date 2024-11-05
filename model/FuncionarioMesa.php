<?php

require_once(__DIR__ . "/../config/database.php");

/**
 * @OA\Schema(
 *     schema="FuncionarioMesa",
 *     type="object",
 *     title="FuncionarioMesa",
 *     description="Modelo de associação entre Funcionário e Mesa",
 *     @OA\Property(property="id_funcionario", type="integer", description="ID do funcionário associado"),
 *     @OA\Property(property="id_mesa", type="integer", description="ID da mesa associada")
 * )
 */
class FuncionarioMesa {

    /**
     * @OA\Get(
     *     path="/funcionario-mesa",
     *     summary="Listar todas as associações entre funcionários e mesas",
     *     @OA\Response(response="200", description="Lista de associações entre funcionários e mesas")
     * )
     */
    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT fm.* FROM FuncionarioMesa fm INNER JOIN Funcionario f ON fm.id_funcionario = f.id INNER JOIN Mesa m ON fm.id_mesa = m.id WHERE f.deletado = 0 AND m.deletado = 0");
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Post(
     *     path="/funcionario-mesa",
     *     summary="Associar um funcionário a uma mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_funcionario", "id_mesa"},
     *             @OA\Property(property="id_funcionario", type="integer", description="ID do funcionário a ser associado"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa a ser associada")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Associação criada com sucesso"),
     *     @OA\Response(response="404", description="Funcionário ou mesa não encontrado")
     * )
     */
    public static function associar($id_funcionario, $id_mesa): int {
        $connection = Connection::getConnection();

        $funcionarioCheck = $connection->prepare("SELECT id FROM Funcionario WHERE id = ? AND deletado = 0");
        $funcionarioCheck->execute([$id_funcionario]);
        if (!$funcionarioCheck->fetch()) {
            throw new Exception("Funcionário não encontrado ou deletado.", 404);
        }

        $mesaCheck = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
        $mesaCheck->execute([$id_mesa]);
        if (!$mesaCheck->fetch()) {
            throw new Exception("Mesa não encontrada ou deletada.", 404);
        }

        $sql = $connection->prepare("INSERT INTO FuncionarioMesa (id_funcionario, id_mesa) VALUES (?, ?)");
        $sql->execute([$id_funcionario, $id_mesa]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Erro ao associar o funcionário à mesa.", 500);
        }

        return $sql->rowCount();
    }

    /**
     * @OA\Delete(
     *     path="/funcionario-mesa",
     *     summary="Desassociar um funcionário de uma mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_funcionario", "id_mesa"},
     *             @OA\Property(property="id_funcionario", type="integer", description="ID do funcionário a ser desassociado"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa a ser desassociada")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Associação removida com sucesso"),
     *     @OA\Response(response="404", description="Associação não encontrada")
     * )
     */
    public static function desassociar($id_funcionario, $id_mesa): int {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("DELETE FROM FuncionarioMesa WHERE id_funcionario = ? AND id_mesa = ?");
        $sql->execute([$id_funcionario, $id_mesa]);

        return $sql->rowCount();
    }

    /**
     * @OA\Get(
     *     path="/funcionario-mesa/{id_funcionario}",
     *     summary="Obter todas as mesas associadas a um funcionário",
     *     @OA\Parameter(
     *         name="id_funcionario",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Lista de mesas associadas ao funcionário"),
     *     @OA\Response(response="404", description="Nenhuma associação encontrada para o funcionário")
     * )
     */
    public static function getByFuncionarioId($id_funcionario): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT fm.* FROM FuncionarioMesa fm INNER JOIN Funcionario f ON fm.id_funcionario = f.id INNER JOIN Mesa m ON fm.id_mesa = m.id WHERE fm.id_funcionario = ? AND f.deletado = 0 AND m.deletado = 0");
        $sql->execute([$id_funcionario]);

        $result = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            throw new Exception("Nenhuma associação encontrada para o funcionário", 404);
        }

        return $result;
    }

    /**
     * @OA\Get(
     *     path="/funcionario-mesa/exist",
     *     summary="Verificar se uma associação entre funcionário e mesa existe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_funcionario", "id_mesa"},
     *             @OA\Property(property="id_funcionario", type="integer", description="ID do funcionário"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Associação encontrada"),
     *     @OA\Response(response="404", description="Associação não encontrada")
     * )
     */
    public static function exist($id_funcionario, $id_mesa): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT COUNT(*) FROM FuncionarioMesa fm INNER JOIN Funcionario f ON fm.id_funcionario = f.id INNER JOIN Mesa m ON fm.id_mesa = m.id WHERE fm.id_funcionario = ? AND fm.id_mesa = ? AND f.deletado = 0 AND m.deletado = 0");
        $sql->execute([$id_funcionario, $id_mesa]);

        return $sql->fetchColumn() > 0;
    }
}