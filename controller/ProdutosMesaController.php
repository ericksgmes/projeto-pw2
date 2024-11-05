<?php

require_once __DIR__ . '/../model/ProdutosMesa.php';
require_once __DIR__ . '/../config/utils.php';

class ProdutosMesaController {
    /**
     * Handle incoming requests for the ProdutosMesa resource.
     *
     * @param string $method The HTTP method (GET, POST, PUT, DELETE)
     * @param int|null $id The ID of the ProdutosMesa (if applicable)
     * @param string|null $action The action to perform (if applicable)
     * @param array|null $data The request data (for POST and PUT)
     */
    public function handleRequest(string $method, int $id = null, string $action = null, array $data = null): void
    {
        try {
            switch (strtoupper($method)) {
                case 'GET':
                    if ($action === 'deletados') {
                        $this->listarDeletadas();
                    } elseif ($id) {
                        $this->listar($id);
                    } else {
                        $this->listar();
                    }
                    break;
                case 'POST':
                    $this->adicionar($data);
                    break;
                case 'PUT':
                    $this->atualizar($id, $data);
                    break;
                case 'DELETE':
                    $this->remover($id);
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/produtos-mesa/{id}",
     *     summary="Obter detalhes dos produtos de uma mesa",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Detalhes dos produtos da mesa"),
     *     @OA\Response(response="404", description="Mesa não encontrada")
     * )
     * @OA\Get(
     *     path="/produtos-mesa",
     *     summary="Listar todos os produtos de todas as mesas",
     *     @OA\Response(response="200", description="Lista de produtos de todas as mesas")
     * )
     */
    public function listar($id = null): void {
        try {
            if ($id) {
                $produtos = ProdutosMesa::getByMesaId($id);
            } else {
                $produtos = ProdutosMesa::listar();
            }
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/produtos-mesa/deletados",
     *     summary="Listar todos os produtos removidos das mesas",
     *     @OA\Response(response="200", description="Lista de produtos removidos das mesas")
     * )
     */
    public function listarDeletadas(): void {
        try {
            $produtos = ProdutosMesa::listarDeletadas();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
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
     *     @OA\Response(response="400", description="Dados inválidos")
     * )
     */
    public function adicionar($data): void {
        try {
            if (!valid($data, ["id_mesa", "id_prod", "quantidade"])) {
                jsonResponse(400, ["status" => "error", "message" => "ID da mesa, ID do produto ou quantidade não fornecido"]);
                return;
            }

            $id_mesa = $data["id_mesa"];
            $id_prod = $data["id_prod"];
            $quantidade = $data["quantidade"];

            $insertedId = ProdutosMesa::adicionarProduto($id_mesa, $id_prod, $quantidade);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
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
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="404", description="Produto não encontrado na mesa")
     * )
     */
    public function atualizar($id, $data): void {
        try {
            if (!valid($data, ["quantidade"])) {
                jsonResponse(400, ["status" => "error", "message" => "Quantidade não fornecida"]);
                return;
            }

            $quantidade = $data["quantidade"];

            ProdutosMesa::atualizarQuantidade($id, $quantidade);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
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
     *     @OA\Response(response="404", description="Produto não encontrado na mesa")
     * )
     */
    public function remover($id): void {
        try {
            ProdutosMesa::removerProduto($id);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}