<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Funcionario.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/funcionario.php";
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = array_filter(explode('/', $relativeUri));

$id = isset($uriSegments[1]) ? $uriSegments[1] : null;


if (method("GET")) {
    try {
        if (valid($_GET, ["id"])) {
            if (!Funcionario::exist($_GET["id"])) {
                throw new Exception("Funcionário não encontrado", 404);
            }
            $funcionario = Funcionario::getById($_GET["id"]);
            output(200, [
                "status" => "success",
                "data" => $funcionario,
                "links" => [
                    ["rel" => "self", "href" => "/funcionarios?id=" . $_GET["id"]],
                    ["rel" => "update", "href" => "/funcionarios?id=" . $_GET["id"]],
                    ["rel" => "delete", "href" => "/funcionarios?id=" . $_GET["id"]]
                ]
            ]);
        } else {
            $list = Funcionario::listar();
            output(200, [
                "status" => "success",
                "data" => $list,
                "links" => [
                    ["rel" => "create", "href" => "/funcionarios"]
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

        if (!valid($data, ["nome", "username", "senha"])) {
            throw new Exception("Nome, username e/ou senha não encontrados", 400);
        }

        if (count(array_keys($data)) !== 3) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }

        if (Funcionario::existsByUsername($data["username"])) {
            throw new Exception("O username já existe. Tente outro.", 409);
        }

        $res = Funcionario::cadastrar($data["nome"], $data["username"], $data["senha"]);
        if (!$res) {
            throw new Exception("Não foi possível cadastrar o funcionário", 500);
        }

        output(201, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/funcionarios?id=" . $res],
                ["rel" => "update", "href" => "/funcionarios?id=" . $res],
                ["rel" => "delete", "href" => "/funcionarios?id=" . $res]
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
        if (!$id) {
            throw new Exception("ID não enviado", 404);
        }
        if (!valid($data, ["nome", "username"])) {
            throw new Exception("Nome e/ou username não encontrados", 404);
        }
        if (count($data) != 2) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if (!Funcionario::exist($id)) {
            throw new Exception("Usuário não encontrado", 400);
        }

        $nome = $data["nome"];
        $username = $data["username"];
        $res = Funcionario::atualizar($id, $nome, $username);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o funcionário", 500);
        }

        output(200, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/funcionarios/" . $id],
                ["rel" => "delete", "href" => "/funcionarios/" . $id . "/deletar"]
            ]
        ]);
    } catch (Exception $e) {
        output($e->getCode(), ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        if (!$id) {
            throw new Exception("ID não enviado", 404);
        }
        if (!Funcionario::exist($id)) {
            throw new Exception("Funcionário não encontrado", 404);
        }

        $res = Funcionario::deleteById($id);
        if (!$res) {
            throw new Exception("Não foi possível deletar o funcionário", 500);
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