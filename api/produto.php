<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Produto.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/produto.php";
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = array_values(array_filter(explode('/', $relativeUri))); // Reindexa o array

$id = $uriSegments[0] ?? null; // Captura o ID corretamente

// Se $_GET['id'] não estiver definido, atribui o valor de $id
if (!isset($_GET['id']) && $id) {
    $_GET['id'] = $id;
}

if (method("GET")) {
    try {
        $produtoId = $_GET["id"] ?? null;

        if ($produtoId) {
            if (!Produto::exist($produtoId)) {
                throw new Exception("Produto não encontrado", 404);
            }
            $produto = Produto::getById($produtoId);
            output(200, [
                "status" => "success",
                "data" => $produto,
                "links" => [
                    ["rel" => "self", "href" => "/produtos/" . $produtoId],
                    ["rel" => "update", "href" => "/produtos/" . $produtoId],
                    ["rel" => "delete", "href" => "/produtos/" . $produtoId]
                ]
            ]);
        } else {
            $list = Produto::listar();
            output(200, [
                "status" => "success",
                "data" => $list,
                "links" => [
                    ["rel" => "create", "href" => "/produtos"]
                ]
            ]);
        }
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("POST")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 400);
        }

        if (!valid($data, ["nome", "preco"])) {
            throw new Exception("Nome ou preço não encontrado", 400);
        }

        if (Produto::existsByName($data["nome"])) {
            throw new Exception("O produto já existe. Tente outro nome.", 409);
        }

        $nome = $data["nome"];
        $preco = $data["preco"];

        $res = Produto::cadastrar($preco, $nome);
        if (!$res) {
            throw new Exception("Não foi possível cadastrar o produto", 500);
        }

        output(201, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/produtos/" . $res],
                ["rel" => "update", "href" => "/produtos/" . $res],
                ["rel" => "delete", "href" => "/produtos/" . $res]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("PUT")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 400);
        }

        $produtoId = $_GET["id"] ?? $id;

        if (!$produtoId) {
            throw new Exception("ID não enviado", 404);
        }

        if (!valid($data, ["nome", "preco"])) {
            throw new Exception("Nome ou preço não encontrado", 400);
        }

        if (!Produto::exist($produtoId)) {
            throw new Exception("Produto não encontrado", 404);
        }

        $nome = $data["nome"];
        $preco = $data["preco"];

        $res = Produto::atualizar($produtoId, $preco, $nome);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o produto", 500);
        }

        output(200, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/produtos/" . $produtoId],
                ["rel" => "delete", "href" => "/produtos/" . $produtoId]
            ]
        ]);
    } catch (Exception $e) {
        output($e->getCode(), ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        $produtoId = $_GET["id"] ?? $id;

        if (!$produtoId) {
            throw new Exception("ID não enviado", 404);
        }
        if (!Produto::exist($produtoId)) {
            throw new Exception("Produto não encontrado", 404);
        }

        $res = Produto::deleteById($produtoId);
        if (!$res) {
            throw new Exception("Não foi possível deletar o produto", 500);
        }

        output(200, [
            "status" => "success",
            "data" => $res
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}
