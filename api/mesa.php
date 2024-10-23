<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Mesa.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/mesa.php";
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = array_filter(explode('/', $relativeUri));

$id = isset($uriSegments[1]) ? $uriSegments[1] : null;

if (method("GET")) {
    try {
        if ($id) {
            if (!Mesa::exist($id)) {
                throw new Exception("Mesa não encontrada", 404);
            }
            $mesa = Mesa::getById($id);
            output(200, [
                "status" => "success",
                "data" => $mesa,
                "links" => [
                    ["rel" => "self", "href" => "/mesas/" . $id],
                    ["rel" => "update", "href" => "/mesas/" . $id],
                    ["rel" => "delete", "href" => "/mesas/" . $id]
                ]
            ]);
        } else {
            $list = Mesa::listar();
            output(200, [
                "status" => "success",
                "data" => $list,
                "links" => [
                    ["rel" => "create", "href" => "/mesas"]
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

        output(201, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/mesas/" . $res],
                ["rel" => "update", "href" => "/mesas/" . $res],
                ["rel" => "delete", "href" => "/mesas/" . $res]
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
            throw new Exception("Nenhuma informação encontrada", 404);
        }
        if (!$id) {
            throw new Exception("ID não enviado", 404);
        }
        if (!valid($data, ["numero"])) {
            throw new Exception("Número não encontrado", 404);
        }
        if (count($data) != 1) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if (!Mesa::exist($id)) {
            throw new Exception("Mesa não encontrada", 400);
        }

        $numero = $data["numero"];
        $res = Mesa::atualizar($id, $numero);
        if (!$res) {
            throw new Exception("Não foi possível atualizar a mesa", 500);
        }

        output(200, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/mesas/" . $id],
                ["rel" => "delete", "href" => "/mesas/" . $id]
            ]
        ]);
    } catch (Exception $e) {
        output($e->getCode(), ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        if (!$id) {
            throw new Exception("ID não enviado", 400);
        }
        if (!Mesa::exist($id)) {
            throw new Exception("Mesa não encontrada", 404);
        }

        $res = Mesa::deleteById($id);
        if (!$res) {
            throw new Exception("Não foi possível deletar a mesa", 404);
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
