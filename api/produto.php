<?php
require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Produto.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

if (method("GET")) {
    try {
        if (valid($_GET, ["id"])) {
            if (!Produto::exist($_GET["id"])) {
                throw new Exception("Produto não encontrado", 404);
            }
            $produto = Produto::getById($_GET["id"]);
            output(200, [
                "status" => "success",
                "data" => $produto,
                "links" => [
                    ["rel" => "self", "href" => "/produtos?id=" . $_GET["id"]],
                    ["rel" => "update", "href" => "/produtos?id=" . $_GET["id"]],
                    ["rel" => "delete", "href" => "/produtos?id=" . $_GET["id"]]
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
            throw new Exception("Nenhuma informação encontrada", 404);
        }

        if (!valid($data, ["nome", "preco"])) {
            throw new Exception("Nome ou preço não encontrado", 400);
        }

        if (Produto::existsByName($data["nome"])) {
            throw new Exception("Produto já existe com este nome. Tente outro.", 409);
        }

        $nome = $data["nome"];
        $preco = $data["preco"];

        $res = Produto::cadastrar($preco, $nome);
        if (!$res) {
            throw new Exception("Não foi possível criar o produto", 500);
        }

        output(201, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/produtos?id=" . $res],
                ["rel" => "update", "href" => "/produtos?id=" . $res],
                ["rel" => "delete", "href" => "/produtos?id=" . $res]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        if (!valid($_GET, ["id"])) {
            throw new Exception("ID não enviado", 404);
        }
        if (!Produto::exist($_GET["id"])) {
            throw new Exception("Produto não encontrado", 404);
        }

        $res = Produto::deleteById($_GET["id"]);
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

if (method("PUT")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 400);
        }
        if (!valid($_GET, ["id"])) {
            throw new Exception("ID não enviado", 404);
        }
        if (!valid($data, ["nome", "preco"])) {
            throw new Exception("Nome ou preço não encontrado", 400);
        }
        if ($data["preco"] < 0) {
            throw new Exception("Preço deve ser positivo", 400);
        }
        if (!Produto::exist($_GET["id"])) {
            throw new Exception("Produto não encontrado", 404);
        }

        $nome = $data["nome"];
        $preco = $data["preco"];

        $res = Produto::atualizar($_GET["id"], $nome, $preco);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o produto", 500);
        }

        output(200, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/produtos?id=" . $_GET["id"]],
                ["rel" => "delete", "href" => "/produtos?id=" . $_GET["id"]]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}