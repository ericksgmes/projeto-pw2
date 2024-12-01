<?php

require_once __DIR__ . '/../model/Pagamento.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../config/paymentMethodEnum.php';

class PagamentoController {
    public function listar($id = null): void {
        try {
            if ($id) {
                error_log("Listando pagamento com ID: $id");
                $pagamento = Pagamento::getById($id);
                jsonResponse(200, ["status" => "success", "data" => $pagamento]);
            } else {
                error_log("Listando todos os pagamentos");
                $pagamentos = Pagamento::listar();
                jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
            }
        } catch (Exception $e) {
            error_log("Erro ao listar pagamentos: " . $e->getMessage());
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function listarDeletados(): void {
        try {
            error_log("Listando pagamentos deletados");
            $pagamentos = Pagamento::listarDeletados();
            jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
        } catch (Exception $e) {
            error_log("Erro ao listar pagamentos deletados: " . $e->getMessage());
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function criar($data): void {
        error_log("Iniciando criação de pagamento com dados: " . json_encode($data));

        if (!valid($data, ["metodo", "valor", "numero"])) {
            error_log("Dados inválidos fornecidos para criação de pagamento.");
            jsonResponse(400, ["status" => "error", "message" => "Método de pagamento, valor ou número da mesa não fornecido"]);
            return;
        }

        try {
            $numero_mesa = $data["numero"];
            $metodo = paymentMethodEnum::from($data["metodo"]);
            $valor = $data["valor"];

            error_log("Dados validados. Método: {$metodo->value}, Valor: $valor, Número Mesa: $numero_mesa");

            // Criar pagamento diretamente
            $insertedId = Pagamento::cadastrar($metodo, $valor, $numero_mesa);
            error_log("Pagamento criado com sucesso. ID: $insertedId");
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            error_log("Erro ao criar pagamento: " . $e->getMessage());
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function atualizar($id, $data): void {
        error_log("Iniciando atualização de pagamento com ID: $id e dados: " . json_encode($data));

        if (!valid($data, ["metodo", "valor", "numero"])) {
            error_log("Dados inválidos fornecidos para atualização de pagamento.");
            jsonResponse(400, ["status" => "error", "message" => "Método de pagamento, valor ou número da mesa não fornecido"]);
            return;
        }

        try {
            $numero_mesa = $data["numero"];
            $metodo = paymentMethodEnum::from($data["metodo"]);
            $valor = $data["valor"];

            // Atualizar o pagamento diretamente
            Pagamento::atualizar($id, $metodo, $valor, $numero_mesa);
            error_log("Pagamento atualizado com sucesso. ID: $id");

            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar pagamento: " . $e->getMessage());
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function deletar($id): void {
        error_log("Iniciando exclusão de pagamento com ID: $id");

        try {
            Pagamento::deleteById($id);
            error_log("Pagamento deletado com sucesso. ID: $id");
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            error_log("Erro ao deletar pagamento: " . $e->getMessage());
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function handleRequest($method, $id = null, $action = null, $data = null): void {
        error_log("Iniciando manipulação de requisição para Pagamento. Método: $method, ID: $id, Ação: $action");

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
                error_log("Método não permitido: $method");
                jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            error_log("Erro ao manipular requisição: " . $e->getMessage());
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
