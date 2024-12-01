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
                    $this->adicionarProduto($data);
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

    // Listar produtos da mesa ou de todas as mesas
    public function listar($numero_mesa = null): void {
        try {
            // Verifica se a mesa existe antes de buscar produtos específicos
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

    // Listar produtos deletados
    public function listarDeletadas(): void {
        try {
            $produtos = ProdutosMesa::listarDeletadas();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // Adicionar produto a uma mesa
    public function adicionarProduto($data): void {
        try {
            $numero_mesa = $data["numero_mesa"];
            $produtos = $data["produtos"];

            // Verifica se a lista de produtos está vazia
            if (empty($produtos)) {
                error_log("Erro: A lista de produtos está vazia para a mesa $numero_mesa.");
                jsonResponse(400, ["status" => "error", "message" => "A lista de produtos não pode estar vazia."]);
                return;
            }

            // Realiza a inserção dos produtos na mesa
            ProdutosMesa::adicionar($numero_mesa, $produtos);

            // Log de sucesso
            error_log("Sucesso: Produtos adicionados à mesa $numero_mesa. Produtos: " . json_encode($produtos));

            // Resposta de sucesso
            jsonResponse(201, ["status" => "success", "message" => "Produtos adicionados à mesa com sucesso"]);
        } catch (Exception $e) {
            // Log de erro
            error_log("Erro na função 'adicionarProduto': " . $e->getMessage());

            // Retorna o erro para o cliente
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // Atualizar quantidade de um produto na mesa
    public function atualizar($id, $data): void {
        try {
            // Verifica se a quantidade foi fornecida
            if (!valid($data, ["quantidade"])) {
                jsonResponse(400, ["status" => "error", "message" => "Quantidade não fornecida"]);
                return;
            }

            $quantidade = $data["quantidade"];

            // Verifica se o produto na mesa existe
            $produtoMesa = ProdutosMesa::getById($id);
            if (!$produtoMesa) {
                jsonResponse(404, ["status" => "error", "message" => "Produto não encontrado na mesa"]);
                return;
            }

            // Verifica se a quantidade é válida
            if ($quantidade <= 0) {
                jsonResponse(400, ["status" => "error", "message" => "A quantidade deve ser maior que zero"]);
                return;
            }

            ProdutosMesa::atualizarQuantidade($id, $quantidade);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // Remover produto de uma mesa
    public function remover($id): void {
        try {
            // Verifica se o produto existe antes de remover
            $produtoMesa = ProdutosMesa::getById($id);
            if (!$produtoMesa) {
                jsonResponse(404, ["status" => "error", "message" => "Produto não encontrado na mesa"]);
                return;
            }

            ProdutosMesa::removerProduto($id);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}