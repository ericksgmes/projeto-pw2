<?php

require_once __DIR__ . '/../model/Pagamento.php';
require_once __DIR__ . '/../config/AuthService.php';
require_once __DIR__ . '/../config/paymentMethodEnum.php';

class PagamentoController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']);
    }

    private function autenticarRequisicao(): array
    {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            return $this->authService->verificarToken($authHeader);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function listar($id = null): void
    {
        $decodedToken = $this->autenticarRequisicao();

        // Somente usuários autenticados podem listar pagamentos
        try {
            if ($id) {
                $pagamento = Pagamento::getById($id);
                if (!$pagamento) {
                    jsonResponse(404, ["status" => "error", "message" => "Pagamento não encontrado"]);
                    return;
                }
                jsonResponse(200, ["status" => "success", "data" => $pagamento]);
            } else {
                $pagamentos = Pagamento::listar();
                jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function listarDeletados(): void
    {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem listar pagamentos deletados"]);
            return;
        }

        try {
            $pagamentos = Pagamento::listarDeletados();
            jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function criar($data): void
    {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem criar pagamentos"]);
            return;
        }

        if (!valid($data, ["metodo", "valor", "numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Método de pagamento, valor ou número da mesa não fornecido"]);
            return;
        }

        try {
            $numero_mesa = $data["numero"];
            $metodo = paymentMethodEnum::from($data["metodo"]);
            $valor = $data["valor"];

            $insertedId = Pagamento::cadastrar($metodo, $valor, $numero_mesa);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function atualizar($id, $data): void
    {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem atualizar pagamentos"]);
            return;
        }

        if (!valid($data, ["metodo", "valor", "numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Método de pagamento, valor ou número da mesa não fornecido"]);
            return;
        }

        try {
            $numero_mesa = $data["numero"];
            $metodo = paymentMethodEnum::from($data["metodo"]);
            $valor = $data["valor"];

            Pagamento::atualizar($id, $metodo, $valor, $numero_mesa);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function deletar($id): void
    {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem deletar pagamentos"]);
            return;
        }

        try {
            Pagamento::deleteById($id);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function handleRequest($method, $id = null, $action = null, $data = null): void
    {
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
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
