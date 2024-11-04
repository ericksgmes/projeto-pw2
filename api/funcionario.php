<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Funcionario.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = handleJsonInput();

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/funcionario.php";
$relativeUri = str_replace($basePath, '', $requestUri);

// Reindexa o array de segmentos da URI
$uriSegments = array_values(array_filter(explode('/', $relativeUri)));

// Extrai o ID corretamente a partir do caminho da URL
$id = $uriSegments[0] ?? null;

if (method("GET")) {
    try {
        if ($id) {
            if (!Funcionario::exist($id)) {
                throw new Exception("Funcionário não encontrado", 404);
            }
            $funcionario = Funcionario::getById($id);
            output(200, [
                "status" => "success",
                "data" => $funcionario,
                "links" => [
                    ["rel" => "self", "href" => "/funcionario.php/" . $id],
                    ["rel" => "update", "href" => "/funcionario.php/" . $id],
                    ["rel" => "delete", "href" => "/funcionario.php/" . $id]
                ]
            ]);
        } else {
            $list = Funcionario::listar();
            output(200, [
                "status" => "success",
                "data" => $list,
                "links" => [
                    ["rel" => "create", "href" => "/funcionario.php"]
                ]
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

        if (!valid($data, ["nome", "username", "senha"])) {
            throw new Exception("Nome, username e/ou senha não encontrados", 400);
        }

        if (count(array_keys($data)) !== 3) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }

        if (Funcionario::existsByUsername($data["username"])) {
            throw new Exception("O username já existe. Tente outro.", 409);
        }

        $insertedId = Funcionario::cadastrar($data["nome"], $data["username"], $data["senha"]);
        if (!$insertedId) {
            throw new Exception("Não foi possível cadastrar o funcionário", 500);
        }

        output(201, [
            "status" => "success",
            "data" => ["id" => $insertedId],
            "links" => [
                ["rel" => "self", "href" => "/funcionario.php/" . $insertedId],
                ["rel" => "update", "href" => "/funcionario.php/" . $insertedId],
                ["rel" => "delete", "href" => "/funcionario.php/" . $insertedId]
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
            throw new Exception("ID não enviado", 404);
        }
        if (!valid($data, ["nome", "username"])) {
            throw new Exception("Nome e/ou username não encontrados", 400);
        }
        if (count($data) != 2) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if (!Funcionario::exist($id)) {
            throw new Exception("Funcionário não encontrado", 404);
        }

        $nome = $data["nome"];
        $username = $data["username"];

        $funcionarioExistente = Funcionario::getByUsername($username);
        if ($funcionarioExistente && $funcionarioExistente['id'] != $id) {
            throw new Exception("O username já existe. Tente outro.", 409);
        }

        $res = Funcionario::atualizar($id, $nome, $username);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o funcionário", 500);
        }

        output(200, [
            "status" => "success",
            "data" => ["id" => $id],
            "links" => [
                ["rel" => "self", "href" => "/funcionario.php/" . $id],
                ["rel" => "delete", "href" => "/funcionario.php/" . $id]
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
            "data" => ["id" => $id]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}
