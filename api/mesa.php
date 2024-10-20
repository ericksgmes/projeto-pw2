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
            output(200, ["status" => "success", "data" => $data]);
        } else {
            $list = Mesa::listar();
            output(200, ["status" => "success", "data" => $list]);
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

        output(201, ["status" => "success", "data" => $res]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
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

        output(200, ["status" => "success", "data" => $res]);
    } catch (Exception $e) {
        output($e->getCode(), ["status" => "error", "message" => $e->getMessage()]);
    }

    if (method("DELETE")) {
        try {
            if (!valid($_GET, ["id"])) {
                throw new Exception("ID não enviado", 400);
            }
            if (!Mesa::exist($_GET["id"])) {
                throw new Exception("Mesa não encontrada", 404);
            }

            $res = Mesa::deleteById($_GET["id"]);
            if (!$res) {
                throw new Exception("Não foi possível deletar a mesa", 500);
            }

            output(200, ["status" => "success", "data" => $res]);
        } catch (Exception $e) {
            $code = $e->getCode() > 100 ? $e->getCode() : 500;
            output($code, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}

