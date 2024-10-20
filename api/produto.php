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
            output(200, $produto);
        } else {
            $list = Produto::listar();
            output(200, $list);
        }
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["msg" => $e->getMessage()]);
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

        output(201, ["msg" => "Produto criado com sucesso"]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["msg" => $e->getMessage()]);
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

        output(200, ["msg" => "Produto deletado com sucesso"]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["msg" => $e->getMessage()]);
    }
}

if (method("PUT")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 404);
        }
        if (!valid($_GET, ["id"])) {
            throw new Exception("ID não enviado", 404);
        }
        if (!valid($data, ["nome", "preco"])) {
            throw new Exception("Nome ou preço não encontrado", 400);
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

        output(200, ["msg" => "Produto atualizado com sucesso"]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["msg" => $e->getMessage()]);
    }
}