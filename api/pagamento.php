<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Pagamento.php");
require_once(__DIR__ . "/../config/paymentMethodEnum.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

if (method("GET")) {
    try {
        if (valid($_GET, ["id"])) {
            if (!Pagamento::exist($_GET["id"])) {
                throw new Exception("Pagamento não encontrado", 404);
            }
            $pagamento = Pagamento::getById($_GET["id"]);
            output(200, [
                "status" => "success",
                "data" => $pagamento,
                "links" => [
                    ["rel" => "self", "href" => "/pagamentos?id=" . $_GET["id"]],
                    ["rel" => "update", "href" => "/pagamentos?id=" . $_GET["id"]],
                    ["rel" => "delete", "href" => "/pagamentos?id=" . $_GET["id"]]
                ]
            ]);
        } else {
            $list = Pagamento::listar();
            output(200, [
                "status" => "success",
                "data" => $list,
                "links" => [
                    ["rel" => "create", "href" => "/pagamentos"]
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

        if (!valid($data, ["metodo", "valor"])) {
            throw new Exception("Método de pagamento ou valor não encontrado", 400);
        }

        $metodo = paymentMethodEnum::from($data["metodo"]);
        $valor = $data["valor"];

        $res = Pagamento::cadastrar($metodo, $valor);
        if (!$res) {
            throw new Exception("Não foi possível criar o pagamento", 500);
        }

        output(201, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/pagamentos?id=" . $res],
                ["rel" => "update", "href" => "/pagamentos?id=" . $res],
                ["rel" => "delete", "href" => "/pagamentos?id=" . $res]
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
        if (!Pagamento::exist($_GET["id"])) {
            throw new Exception("Pagamento não encontrado", 404);
        }

        $res = Pagamento::deleteById($_GET["id"]);
        if (!$res) {
            throw new Exception("Não foi possível deletar o pagamento", 500);
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
        if (!valid($data, ["metodo", "valor"])) {
            throw new Exception("Método de pagamento ou valor não encontrado", 400);
        }
        if (!Pagamento::exist($_GET["id"])) {
            throw new Exception("Pagamento não encontrado", 404);
        }
        $metodo = paymentMethodEnum::from($data["metodo"]);
        $valor = $data["valor"];

        $res = Pagamento::atualizar($_GET["id"], $metodo, $valor);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o pagamento", 500);
        }

        output(200, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/pagamentos?id=" . $_GET["id"]],
                ["rel" => "delete", "href" => "/pagamentos?id=" . $_GET["id"]]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}