<?php

require_once __DIR__ . '/../model/ProdutosMesa.php';
require_once __DIR__ . '/../config/utils.php';

class ProdutosMesaController {
    public function handleRequest(string $method, $id = null, string $action = null, array $data = null): void {
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

    public function listar($numero_mesa = null): void {
        try {
            if ($numero_mesa) {
                $produtos = ProdutosMesa::getByMesaNumero($numero_mesa);
            } else {
                $produtos = ProdutosMesa::listar();
            }
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function listarDeletadas(): void {
        try {
            $produtos = ProdutosMesa::listarDeletadas();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function adicionar($data): void {
        try {
            if (!valid($data, ["numero_mesa", "id_prod", "quantidade"])) {
                jsonResponse(400, ["status" => "error", "message" => "Número da mesa, ID do produto ou quantidade não fornecido"]);
                return;
            }

            $numero_mesa = $data["numero_mesa"];
            $id_prod = $data["id_prod"];
            $quantidade = $data["quantidade"];

            $insertedId = ProdutosMesa::adicionarProduto($numero_mesa, $id_prod, $quantidade);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

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

    public function remover($id): void {
        try {
            ProdutosMesa::removerProduto($id);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
