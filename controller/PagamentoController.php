<?php

require_once __DIR__ . '/../model/Pagamento.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../config/paymentMethodEnum.php';

class PagamentoController {
    /**
     * @OA\Get(
     *     path="/pagamentos/{id}",
     *     summary="Obter detalhes de um pagamento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Detalhes do pagamento"),
     *     @OA\Response(response="404", description="Pagamento não encontrado")
     * )
     * @OA\Get(
     *     path="/pagamentos",
     *     summary="Listar todos os pagamentos",
     *     @OA\Response(response="200", description="Lista de pagamentos")
     * )
     */
    public function listar($id = null): void {
        if ($id) {
            $pagamento = Pagamento::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $pagamento]);
        } else {
            $pagamentos = Pagamento::listar();
            jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
        }
    }

    /**
     * @OA\Get(
     *     path="/pagamentos/deletados",
     *     summary="Listar todos os pagamentos deletados",
     *     @OA\Response(response="200", description="Lista de pagamentos deletados")
     * )
     */
    public function listarDeletados(): void {
        $pagamentos = Pagamento::listarDeletados();
        jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
    }

    /**
     * @OA\Post(
     *     path="/pagamentos",
     *     summary="Criar um novo pagamento",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"metodo", "valor", "id_mesa"},
     *             @OA\Property(property="metodo", type="string", description="Método de pagamento"),
     *             @OA\Property(property="valor", type="number", format="float", description="Valor do pagamento"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa associada ao pagamento")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Pagamento criado com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos")
     * )
     */
    public function criar($data): void {
        error_log("Iniciando criação de pagamento com dados: " . json_encode($data));

        if (!valid($data, ["metodo", "valor", "id_mesa"])) {
            jsonResponse(400, ["status" => "error", "message" => "Método de pagamento, valor ou ID da mesa não fornecido"]);
            return;
        }

        try {
            $metodo = paymentMethodEnum::from($data["metodo"]);
            $valor = $data["valor"];
            $id_mesa = $data["id_mesa"];
            error_log("Dados validados. Método: {$metodo->value}, Valor: $valor, ID Mesa: $id_mesa");

            $insertedId = Pagamento::cadastrar($metodo, $valor, $id_mesa);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            error_log("Erro ao criar pagamento: " . $e->getMessage());
            throw new Exception("Erro ao criar pagamento: " . $e->getMessage(), 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/pagamentos/{id}",
     *     summary="Atualizar um pagamento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"metodo", "valor", "id_mesa"},
     *             @OA\Property(property="metodo", type="string", description="Método de pagamento"),
     *             @OA\Property(property="valor", type="number", format="float", description="Valor do pagamento"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa associada ao pagamento")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Pagamento atualizado com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="404", description="Pagamento não encontrado")
     * )
     */
    public function atualizar($id, $data): void {
        if (!valid($data, ["metodo", "valor", "id_mesa"])) {
            jsonResponse(400, ["status" => "error", "message" => "Método de pagamento, valor ou ID da mesa não fornecido"]);
            return;
        }

        $metodo = paymentMethodEnum::from($data["metodo"]);
        $valor = $data["valor"];
        $id_mesa = $data["id_mesa"];

        Pagamento::atualizar($id, $metodo, $valor, $id_mesa);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    /**
     * @OA\Delete(
     *     path="/pagamentos/{id}",
     *     summary="Deletar um pagamento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Pagamento deletado com sucesso"),
     *     @OA\Response(response="404", description="Pagamento não encontrado")
     * )
     */
    public function deletar($id): void {
        Pagamento::deleteById($id);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    public function handleRequest($method, $id = null, $action = null, $data = null): void {
        try {
            if ($method === 'GET') {
                if ($action === 'deletados') {
                    $this->listarDeletados();
                } else {
                    $this->listar($id);
                }
            } elseif ($method === 'POST') {
                $this->criar($data);
            } elseif ($method === 'PUT') {
                if ($id) {
                    $this->atualizar($id, $data);
                } else {
                    jsonResponse(400, ["status" => "error", "message" => "ID necessário para atualização"]);
                }
            } elseif ($method === 'DELETE') {
                if ($id) {
                    $this->deletar($id);
                } else {
                    jsonResponse(400, ["status" => "error", "message" => "ID necessário para exclusão"]);
                }
            } else {
                jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            jsonResponse($code, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
