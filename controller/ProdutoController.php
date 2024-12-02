<?php
require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../config/AuthService.php';

class ProdutoController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']);
    }

    private function autenticarRequisicao(): array {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            return $this->authService->verificarToken($authHeader);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function listar($id = null): void {
        $this->autenticarRequisicao(); // Garantir que apenas usuários autenticados possam acessar

        if ($id) {
            $produto = Produto::getById($id);
            if (!$produto) {
                jsonResponse(404, ["status" => "error", "message" => "Produto não encontrado"]);
                return;
            }
            jsonResponse(200, ["status" => "success", "data" => $produto]);
        } else {
            $produtos = Produto::listar();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        }
    }

    public function listarDeletados(): void {
        $decodedToken = $this->autenticarRequisicao();
        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            return;
        }

        $produtos = Produto::listarDeletados();
        jsonResponse(200, ["status" => "success", "data" => $produtos]);
    }

    public function criar($data): void {
        $decodedToken = $this->autenticarRequisicao();
        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            return;
        }

        if (!valid($data, ["nome", "preco"])) {
            jsonResponse(400, ["status" => "error", "message" => "Nome ou preço não fornecido"]);
            return;
        }

        $produtoExistente = Produto::existsByName($data["nome"]);
        if ($produtoExistente && $produtoExistente["deletado"] == 0) {
            jsonResponse(409, ["status" => "error", "message" => "Um produto com este nome já existe"]);
            return;
        }

        $insertedId = Produto::cadastrar($data["nome"], $data["preco"]);
        jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
    }

    public function atualizar($id, $data): void {
        $decodedToken = $this->autenticarRequisicao();
        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            return;
        }

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

    public function deletar($id): void {
        $decodedToken = $this->autenticarRequisicao();
        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            return;
        }

        Produto::deleteById($id);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    public function atualizarPreco($id, $data): void {
        $decodedToken = $this->autenticarRequisicao();
        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            return;
        }

        if (!isset($data['preco']) || !is_numeric($data['preco'])) {
            jsonResponse(400, ["status" => "error", "message" => "Preço inválido ou não fornecido"]);
            return;
        }

        try {
            Produto::atualizarPreco($id, $data['preco']);
            jsonResponse(200, ["status" => "success", "message" => "Preço atualizado com sucesso"]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function handleRequest($method, $id = null, $action = null, $data = null): void {
        try {
            if ($method === 'PUT' && $action === 'preco') {
                if ($id) {
                    $this->atualizarPreco($id, $data);
                } else {
                    jsonResponse(400, ["status" => "error", "message" => "ID necessário para atualização de preço"]);
                }
            } elseif ($method === 'GET') {
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
