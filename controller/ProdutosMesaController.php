<?php

require_once __DIR__ . '/../model/ProdutosMesa.php';
require_once __DIR__ . '/../config/utils.php';


class ProdutosMesaController {
    public function handleRequest($method, $id = null, $action = null, $data = null): void {
        try {
            if ($method === 'GET') {
                if ($action === 'deletados') {
                    $this->listarDeletadas();
                } else {
                    $this->listar($id);
                }
            } elseif ($method === 'POST') {
                $this->adicionar($data);
            } elseif ($method === 'PUT') {
                if ($id) {
                    $this->atualizar($id, $data);
                } else {
                    jsonResponse(400, ["status" => "error", "message" => "ID necessário para atualização"]);
                }
            } elseif ($method === 'DELETE') {
                if ($id) {
                    $this->remover($id);
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

    public function listar($id = null): void {
        if (isset($_GET['id_mesa'])) {
            $id_mesa = $_GET['id_mesa'];
            $produtos = ProdutosMesa::getByMesaId($id_mesa);
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } elseif ($id) {
            $produtoMesa = ProdutosMesa::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $produtoMesa]);
        } else {
            $produtos = ProdutosMesa::listar();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        }
    }

    public function listarDeletadas(): void {
        $produtos = ProdutosMesa::listarDeletadas();
        jsonResponse(200, ["status" => "success", "data" => $produtos]);
    }


    public function adicionar($data): void {
        if (!valid($data, ["id_mesa", "id_prod", "quantidade"])) {
            jsonResponse(400, ["status" => "error", "message" => "ID da mesa, ID do produto ou quantidade não fornecido"]);
            return;
        }

        $id_mesa = $data["id_mesa"];
        $id_prod = $data["id_prod"];
        $quantidade = $data["quantidade"];

        $insertedId = ProdutosMesa::adicionarProduto($id_mesa, $id_prod, $quantidade);
        jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
    }

    public function atualizar($id, $data): void {
        if (!valid($data, ["quantidade"])) {
            jsonResponse(400, ["status" => "error", "message" => "Quantidade não fornecida"]);
            return;
        }

        $quantidade = $data["quantidade"];

        ProdutosMesa::atualizarQuantidade($id, $quantidade);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    public function remover($id): void {
        ProdutosMesa::removerProduto($id);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }
}
