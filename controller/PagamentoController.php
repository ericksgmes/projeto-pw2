<?php

require_once __DIR__ . '/../model/Pagamento.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../config/paymentMethodEnum.php';

class PagamentoController {
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

    public function listar($id = null): void {
        if ($id) {
            $pagamento = Pagamento::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $pagamento]);
        } else {
            $pagamentos = Pagamento::listar();
            jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
        }
    }
    public function listarDeletados(): void {
        $pagamentos = Pagamento::listarDeletados();
        jsonResponse(200, ["status" => "success", "data" => $pagamentos]);
    }

    public function criar($data): void {
        if (!valid($data, ["metodo", "valor", "id_mesa"])) {
            jsonResponse(400, ["status" => "error", "message" => "Método de pagamento, valor ou ID da mesa não fornecido"]);
            return;
        }

        $metodo = paymentMethodEnum::from($data["metodo"]);
        $valor = $data["valor"];
        $id_mesa = $data["id_mesa"];

        $insertedId = Pagamento::cadastrar($metodo, $valor, $id_mesa);
        jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
    }

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

    public function deletar($id): void {
        Pagamento::deleteById($id);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }
}
