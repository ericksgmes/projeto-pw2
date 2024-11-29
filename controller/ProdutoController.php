<?php
require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../config/utils.php';

class ProdutoController {
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
     * @OA\Get(
     *     path="/produtos",
     *     summary="Listar todos os produtos",
     *     @OA\Response(response="200", description="Lista de produtos")
     * )
     */
    public function listar($id = null): void {
        if ($id) {
            $produto = Produto::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $produto]);
        } else {
            $produtos = Produto::listar();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        }
    }

    /**
     * @OA\Get(
     *     path="/produtos/deletados",
     *     summary="Listar todos os produtos deletados",
     *     @OA\Response(response="200", description="Lista de produtos deletados")
     * )
     */
    public function listarDeletados(): void {
        $produtos = Produto::listarDeletados();
        jsonResponse(200, ["status" => "success", "data" => $produtos]);
    }

    /**
     * @OA\Post(
     *     path="/produtos",
     *     summary="Criar um novo produto",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "preco"},
     *             @OA\Property(property="nome", type="string", description="Nome do produto"),
     *             @OA\Property(property="preco", type="number", format="float", description="Preço do produto")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Produto criado com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos")
     * )
     */
    public function criar($data): void {
        if (!valid($data, ["nome", "preco"])) {
            jsonResponse(400, ["status" => "error", "message" => "Nome ou preço não fornecido"]);
            return;
        }

        // Verificar se já existe um produto com o mesmo nome que não esteja deletado
        $produtoExistente = Produto::existsByName($data["nome"]);
        if ($produtoExistente && $produtoExistente["deletado"] == 0) {
            jsonResponse(409, ["status" => "error", "message" => "Um produto com este nome já existe"]);
            return;
        }

        $insertedId = Produto::cadastrar($data["nome"], $data["preco"]);
        jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
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
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="404", description="Produto não encontrado")
     * )
     */
    public function atualizar($id, $data): void {
        if (!valid($data, ["nome", "preco"])) {
            jsonResponse(400, ["status" => "error", "message" => "Nome ou preço não fornecido"]);
            return;
        }

        $produtoExistente = Produto::existsByName($data["nome"]);
        if ($produtoExistente && $produtoExistente["id"] != $id && $produtoExistente["deletado"] == 0) {
            jsonResponse(409, ["status" => "error", "message" => "Um produto com este nome já existe"]);
            return;
        }

        Produto::atualizar($id, $data["nome"], $data["preco"]);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
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
    public function deletar($id): void {
        Produto::deleteById($id);
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
