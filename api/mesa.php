<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Mesa.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

if (method("GET")) {
    try {
        if (valid($_GET, ["id"])) {
            if (!Mesa::exist($_GET["id"])) {
                throw new Exception("Mesa não encontrada", 404);
            }
            $mesa = Mesa::getById($_GET["id"]);
            output(200, $mesa);
        } else {
            $list = Mesa::listar();
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

        if (!valid($data, ["numero"])) {
            throw new Exception("Número não encontrado", 400);
        }

        if (count(array_keys($data)) !== 1) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }

        if (Mesa::existsByNumber($data["numero"])) {
            throw new Exception("O número já existe. Tente outro.", 409);
        }

        $res = Mesa::criar($data["numero"]);
        if (!$res) {
            throw new Exception("Não foi possível criar a mesa", 500);
        }

        output(201, ["msg" => "Mesa criada com sucesso"]);
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
        if (count($_GET) != 1) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if (!valid($data, ["numero"])) {
            throw new Exception("Número não encontrado", 404);
        }
        if (count($data) != 1) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if (!Mesa::exist($_GET["id"])) {
            throw new Exception("Mesa não encontrada", 400);
        }

        $numero = $data["numero"];
        $res = Mesa::atualizar($_GET["id"], $numero);
        if (!$res) {
            throw new Exception("Não foi possível atualizar a mesa", 500);
        }

        output(200, ["msg" => "Mesa editada com sucesso"]);
    } catch (Exception $e) {
        output($e->getCode(), ["msg" => $e->getMessage()]);
    }
}

