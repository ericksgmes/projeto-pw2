<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/ProdutosMesa.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/produtosMesa.php";
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = array_values(array_filter(explode('/', $relativeUri)));

$id = $uriSegments[0] ?? null; // ID do registro em ProdutosMesa

if (method("GET")) {
    try {
        $mesaId = $_GET['id_mesa'] ?? null;

        if ($mesaId) {
            // Listar produtos de uma mesa específica
            $produtos = ProdutosMesa::getByMesaId($mesaId);
            output(200, [
                "status" => "success",
                "data" => $produtos
            ]);
        } elseif ($id) {
            // Obter produtoMesa específico
            $produtoMesa = ProdutosMesa::getById($id);
            if (!$produtoMesa) {
                throw new Exception("Produto na mesa não encontrado", 404);
            }
            output(200, [
                "status" => "success",
                "data" => $produtoMesa
            ]);
        } else {
            // Listar todos os produtos em mesas
            $produtos = ProdutosMesa::listar();
            output(200, [
                "status" => "success",
                "data" => $produtos
            ]);
        }
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("POST")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 400);
        }

        if (!valid($data, ["id_mesa", "id_prod", "quantidade"])) {
            throw new Exception("ID da mesa, ID do produto ou quantidade não encontrada", 400);
        }

        $id_mesa = $data["id_mesa"];
        $id_prod = $data["id_prod"];
        $quantidade = $data["quantidade"];

        $res = ProdutosMesa::adicionarProduto($id_mesa, $id_prod, $quantidade);
        if (!$res) {
            throw new Exception("Não foi possível adicionar o produto à mesa", 500);
        }

        $insertedId = $res; // Supondo que o método retorna o ID inserido

        output(201, [
            "status" => "success",
            "data" => ["id" => $insertedId],
            "links" => [
                ["rel" => "self", "href" => "/produtosMesa/" . $insertedId],
                ["rel" => "update", "href" => "/produtosMesa/" . $insertedId],
                ["rel" => "delete", "href" => "/produtosMesa/" . $insertedId]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("PUT")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 400);
        }

        if (!$id) {
            throw new Exception("ID não enviado", 400);
        }

        if (!valid($data, ["quantidade"])) {
            throw new Exception("Quantidade não encontrada", 400);
        }

        $quantidade = $data["quantidade"];

        // Verifica se o registro existe
        $produtoMesa = ProdutosMesa::getById($id);
        if (!$produtoMesa) {
            throw new Exception("Produto na mesa não encontrado", 404);
        }

        $res = ProdutosMesa::atualizarQuantidade($id, $quantidade);
        if (!$res) {
            throw new Exception("Não foi possível atualizar a quantidade", 500);
        }

        output(200, [
            "status" => "success",
            "data" => ["id" => $id],
            "links" => [
                ["rel" => "self", "href" => "/produtosMesa/" . $id],
                ["rel" => "delete", "href" => "/produtosMesa/" . $id]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        if (!$id) {
            throw new Exception("ID não enviado", 400);
        }

        // Verifica se o registro existe
        $produtoMesa = ProdutosMesa::getById($id);
        if (!$produtoMesa) {
            throw new Exception("Produto na mesa não encontrado", 404);
        }

        $res = ProdutosMesa::removerProduto($id);
        if (!$res) {
            throw new Exception("Não foi possível remover o produto da mesa", 500);
        }

        output(200, [
            "status" => "success",
            "data" => ["id" => $id]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}