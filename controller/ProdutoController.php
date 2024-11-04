<?php

require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../config/utils.php';


class ProdutoController {
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
            $produto = Produto::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $produto]);
        } else {
            $produtos = Produto::listar();
            jsonResponse(200, ["status" => "success", "data" => $produtos]);
        }
    }

    public function listarDeletados(): void {
        $produtos = Produto::listarDeletados();
        jsonResponse(200, ["status" => "success", "data" => $produtos]);
    }
    public function criar($data): void {
        if (!valid($data, ["nome", "preco"])) {
            jsonResponse(400, ["status" => "error", "message" => "Nome ou preço não fornecido"]);
            return;
        }

        $nome = $data["nome"];
        $preco = $data["preco"];

        $insertedId = Produto::cadastrar($nome, $preco);
        jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
    }

    public function atualizar($id, $data): void {
        if (!valid($data, ["nome", "preco"])) {
            jsonResponse(400, ["status" => "error", "message" => "Nome ou preço não fornecido"]);
            return;
        }

        $nome = $data["nome"];
        $preco = $data["preco"];

        Produto::atualizar($id, $nome, $preco);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    public function deletar($id): void {
        Produto::deleteById($id);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }
}
