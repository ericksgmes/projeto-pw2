<?php

require_once(__DIR__ . '/../model/ProdutosMesa.php');
require_once(__DIR__ . '/../config/utils.php');
require_once(__DIR__ . '/../config/AuthService.php');

class ProdutosMesaController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']);
    }

    private function autenticarRequisicao()
    {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            return $this->authService->verificarToken($authHeader);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function handleRequest(string $method, $id = null, string $action = null, array $data = null): void
    {
        try {
            $decodedToken = $this->autenticarRequisicao();

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
                    if (!$decodedToken['is_admin']) {
                        jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
                        exit;
                    }
                    $this->atualizar($id, $data);
                    break;
                case 'DELETE':
                    if (!$decodedToken['is_admin']) {
                        jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
                        exit;
                    }
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
    public function listar($numero_mesa = null): void
    {
        try {
            $produtos = $numero_mesa ? ProdutosMesa::getByMesaNumero($numero_mesa) : ProdutosMesa::listar();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // Listar produtos deletados
    public function listarDeletadas(): void
    {
        try {
            $produtos = ProdutosMesa::listarDeletadas();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // Adicionar produto a uma mesa
    public function adicionarProduto($data): void
    {
        try {
            $numero_mesa = $data["numero_mesa"];
            $produtos = $data["produtos"];

            if (empty($produtos)) {
                jsonResponse(400, ["status" => "error", "message" => "A lista de produtos não pode estar vazia."]);
                return;
            }

            ProdutosMesa::adicionar($numero_mesa, $produtos);
            jsonResponse(201, ["status" => "success", "message" => "Produtos adicionados à mesa com sucesso"]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    // Atualizar quantidade de um produto na mesa
    public function atualizar($id, $data): void
    {
        try {
            if (!valid($data, ["quantidade"])) {
                jsonResponse(400, ["status" => "error", "message" => "Quantidade não fornecida"]);
                return;
            }

            $quantidade = $data["quantidade"];
            $produtoMesa = ProdutosMesa::getById($id);

            if (!$produtoMesa) {
                jsonResponse(404, ["status" => "error", "message" => "Produto não encontrado na mesa"]);
                return;
            }

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
    public function remover($id): void
    {
        try {
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