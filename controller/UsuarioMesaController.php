<?php

require_once(__DIR__ . "/../model/UsuarioMesa.php");
require_once(__DIR__ . "/../config/utils.php");

class UsuarioMesaController {
    /**
     * @OA\Get(
     *     path="/usuario-mesa/{usuarioId}",
     *     summary="Listar todas as mesas associadas a um usuário",
     *     @OA\Parameter(
     *         name="usuarioId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Lista de mesas associadas ao usuário"),
     *     @OA\Response(response="404", description="Usuário não encontrado")
     * )
     * @OA\Get(
     *     path="/usuario-mesa",
     *     summary="Listar todas as associações entre usuários e mesas",
     *     @OA\Response(response="200", description="Lista de associações entre usuários e mesas")
     * )
     */
    private function listar($usuarioId = null): void {
        if ($usuarioId) {
            $associacoes = UsuarioMesa::getByUsuarioId($usuarioId);
            jsonResponse(200, ["status" => "success", "data" => $associacoes]);
        } else {
            $associacoes = UsuarioMesa::listar();
            jsonResponse(200, ["status" => "success", "data" => $associacoes]);
        }
    }

    /**
     * @OA\Post(
     *     path="/usuario-mesa",
     *     summary="Criar uma nova associação entre usuário e mesa",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_usuario", "id_mesa"},
     *             @OA\Property(property="id_usuario", type="integer", description="ID do usuário"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Associação criada com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="409", description="Associação já existente")
     * )
     */
    private function criar($data): void {
        if (!valid($data, ["id_usuario", "id_mesa"])) {
            throw new Exception("ID do usuário ou da mesa não encontrado", 400);
        }

        $id_usuario = $data["id_usuario"];
        $id_mesa = $data["id_mesa"];

        if (UsuarioMesa::exist($id_usuario, $id_mesa)) {
            throw new Exception("A associação já existe", 409);
        }

        UsuarioMesa::associar($id_usuario, $id_mesa);
        jsonResponse(201, [
            "status" => "success",
            "data" => ["id_usuario" => $id_usuario, "id_mesa" => $id_mesa]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/usuario-mesa/{usuarioId}/{mesaId}",
     *     summary="Desassociar um usuário de uma mesa",
     *     operationId="a8e9877158f7ddb4ea46d6732f5f5ac2",
     *     @OA\Parameter(
     *         name="usuarioId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID do usuário a ser desassociado"
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
    private function deletar($usuarioId, $mesaId): void {
        if (!$usuarioId || !$mesaId) {
            throw new Exception("ID do usuário ou da mesa não enviado", 400);
        }

        if (!UsuarioMesa::exist($usuarioId, $mesaId)) {
            throw new Exception("Associação não encontrada", 404);
        }

        UsuarioMesa::desassociar($usuarioId, $mesaId);
        jsonResponse(200, [
            "status" => "success",
            "data" => ["id_usuario" => $usuarioId, "id_mesa" => $mesaId]
        ]);
    }

    public function handleRequest($method, $usuarioId = null, $mesaId = null, $data = null): void {
        try {
            switch ($method) {
                case 'GET':
                    $this->listar($usuarioId);
                    break;
                case 'POST':
                    $this->criar($data);
                    break;
                case 'DELETE':
                    $this->deletar($usuarioId, $mesaId);
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
