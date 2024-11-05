<?php

require_once(__DIR__ . "/../model/FuncionarioMesa.php");
require_once(__DIR__ . "/../config/utils.php");

class FuncionarioMesaController {
    /**
     * @OA\Get(
     *     path="/funcionario-mesa/{funcionarioId}",
     *     summary="Listar todas as mesas associadas a um funcionário",
     *     @OA\Parameter(
     *         name="funcionarioId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Lista de mesas associadas ao funcionário"),
     *     @OA\Response(response="404", description="Funcionário não encontrado")
     * )
     * @OA\Get(
     *     path="/funcionario-mesa",
     *     summary="Listar todas as associações entre funcionários e mesas",
     *     @OA\Response(response="200", description="Lista de associações entre funcionários e mesas")
     * )
     */
    private function listar($funcionarioId = null): void {
        if ($funcionarioId) {
            $associacoes = FuncionarioMesa::getByFuncionarioId($funcionarioId);
            jsonResponse(200, ["status" => "success", "data" => $associacoes]);
        } else {
            $associacoes = FuncionarioMesa::listar();
            jsonResponse(200, ["status" => "success", "data" => $associacoes]);
        }
    }

    /**
     * @OA\Post(
     *     path="/funcionario-mesa",
     *     summary="Criar uma nova associação entre funcionário e mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_funcionario", "id_mesa"},
     *             @OA\Property(property="id_funcionario", type="integer", description="ID do funcionário"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Associação criada com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="409", description="Associação já existente")
     * )
     */
    private function criar($data): void {
        if (!valid($data, ["id_funcionario", "id_mesa"])) {
            throw new Exception("ID do funcionário ou da mesa não encontrado", 400);
        }

        $id_funcionario = $data["id_funcionario"];
        $id_mesa = $data["id_mesa"];

        if (FuncionarioMesa::exist($id_funcionario, $id_mesa)) {
            throw new Exception("A associação já existe", 409);
        }

        FuncionarioMesa::associar($id_funcionario, $id_mesa);
        jsonResponse(201, [
            "status" => "success",
            "data" => ["id_funcionario" => $id_funcionario, "id_mesa" => $id_mesa]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/funcionario-mesa/{funcionarioId}/{mesaId}",
     *     summary="Desassociar um funcionário de uma mesa",
     *     operationId="a8e9877158f7ddb4ea46d6732f5f5ac2",
     *     @OA\Parameter(
     *         name="funcionarioId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID do funcionário a ser desassociado"
     *     ),
     *     @OA\Parameter(
     *         name="mesaId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID da mesa a ser desassociada"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Associação removida com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Associação não encontrada"
     *     )
     * )
     */

    private function deletar($funcionarioId, $mesaId): void {
        if (!$funcionarioId || !$mesaId) {
            throw new Exception("ID do funcionário ou da mesa não enviado", 400);
        }

        if (!FuncionarioMesa::exist($funcionarioId, $mesaId)) {
            throw new Exception("Associação não encontrada", 404);
        }

        FuncionarioMesa::desassociar($funcionarioId, $mesaId);
        jsonResponse(200, [
            "status" => "success",
            "data" => ["id_funcionario" => $funcionarioId, "id_mesa" => $mesaId]
        ]);
    }

    public function handleRequest($method, $funcionarioId = null, $mesaId = null, $data = null): void {
        try {
            switch ($method) {
                case 'GET':
                    $this->listar($funcionarioId);
                    break;
                case 'POST':
                    $this->criar($data);
                    break;
                case 'DELETE':
                    $this->deletar($funcionarioId, $mesaId);
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}