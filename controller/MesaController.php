<?php

require_once __DIR__ . '/../model/Mesa.php';
require_once __DIR__ . '/../config/utils.php';

class MesaController {
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
     * @OA\Get(
     *     path="/mesas",
     *     summary="Listar todas as mesas",
     *     @OA\Response(response="200", description="Lista de mesas")
     * )
     */
    public function listar($id = null): void {
        if ($id) {
            $mesa = Mesa::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $mesa]);
        } else {
            $mesas = Mesa::listar();
            jsonResponse(200, ["status" => "success", "data" => $mesas]);
        }
    }

    /**
     * @OA\Get(
     *     path="/mesas/deletadas",
     *     summary="Listar todas as mesas deletadas",
     *     @OA\Response(response="200", description="Lista de mesas deletadas")
     * )
     */
    public function listarDeletadas(): void {
        $mesas = Mesa::listarDeletadas();
        jsonResponse(200, ["status" => "success", "data" => $mesas]);
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
     *     @OA\Response(response="400", description="Número da mesa não fornecido")
     * )
     */
    public function criar($data): void {
        if (!valid($data, ["numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Número da mesa não fornecido"]);
            return;
        }

        $insertedId = Mesa::criar($data["numero"]);
        jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
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
     *     @OA\Response(response="400", description="Número da mesa não fornecido"),
     *     @OA\Response(response="404", description="Mesa não encontrada")
     * )
     */
    public function atualizar($id, $data): void {
        if (!valid($data, ["numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Número da mesa não fornecido"]);
            return;
        }

        Mesa::atualizar($id, $data["numero"]);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
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
    public function deletar($id): void {
        Mesa::deleteById($id);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    public function handleRequest($method, $id = null, $action = null, $data = null): void {
        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'deletadas') {
                        $this->listarDeletadas();
                    } else {
                        $this->listar($id);
                    }
                    break;
                case 'POST':
                    $this->criar($data);
                    break;
                case 'PUT':
                    if ($id) {
                        $this->atualizar($id, $data);
                    } else {
                        jsonResponse(400, ["status" => "error", "message" => "ID necessário para atualização"]);
                    }
                    break;
                case 'DELETE':
                    if ($id) {
                        $this->deletar($id);
                    } else {
                        jsonResponse(400, ["status" => "error", "message" => "ID necessário para exclusão"]);
                    }
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            $code = $e->getCode() >= 100 ? $e->getCode() : 500;
            jsonResponse($code, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}