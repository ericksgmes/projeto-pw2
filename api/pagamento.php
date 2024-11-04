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

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/pagamento.php";
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = array_values(array_filter(explode('/', $relativeUri))); // Reindexa o array

$id = $uriSegments[0] ?? null; // Captura o ID corretamente

if (method("GET")) {
    try {
        $pagamentoId = $id ?? null;

        if ($pagamentoId) {
            if (!Pagamento::exist($pagamentoId)) {
                throw new Exception("Pagamento não encontrado", 404);
            }
            $pagamento = Pagamento::getById($pagamentoId);
            output(200, [
                "status" => "success",
                "data" => $pagamento,
                "links" => [
                    ["rel" => "self", "href" => "/pagamentos/" . $pagamentoId],
                    ["rel" => "update", "href" => "/pagamentos/" . $pagamentoId],
                    ["rel" => "delete", "href" => "/pagamentos/" . $pagamentoId]
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
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("POST")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 400);
        }

        if (!valid($data, ["metodo", "valor", "id_mesa"])) {
            throw new Exception("Método de pagamento, valor ou ID da mesa não encontrado", 400);
        }

        $metodo = paymentMethodEnum::from($data["metodo"]);
        $valor = $data["valor"];
        $id_mesa = $data["id_mesa"];

        $insertedId = Pagamento::cadastrar($metodo, $valor, $id_mesa);
        if (!$insertedId) {
            throw new Exception("Não foi possível criar o pagamento", 500);
        }

        output(201, [
            "status" => "success",
            "data" => ["id" => $insertedId],
            "links" => [
                ["rel" => "self", "href" => "/pagamentos/" . $insertedId],
                ["rel" => "update", "href" => "/pagamentos/" . $insertedId],
                ["rel" => "delete", "href" => "/pagamentos/" . $insertedId]
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
        $pagamentoId = $id ?? null;

        if (!$pagamentoId) {
            throw new Exception("ID não enviado", 400);
        }
        if (!valid($data, ["metodo", "valor", "id_mesa"])) {
            throw new Exception("Método de pagamento, valor ou ID da mesa não encontrado", 400);
        }
        if (!Pagamento::exist($pagamentoId)) {
            throw new Exception("Pagamento não encontrado", 404);
        }

        $metodo = paymentMethodEnum::from($data["metodo"]);
        $valor = $data["valor"];
        $id_mesa = $data["id_mesa"];

        $res = Pagamento::atualizar($pagamentoId, $metodo, $valor, $id_mesa);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o pagamento", 500);
        }

        output(200, [
            "status" => "success",
            "data" => ["id" => $pagamentoId],
            "links" => [
                ["rel" => "self", "href" => "/pagamentos/" . $pagamentoId],
                ["rel" => "delete", "href" => "/pagamentos/" . $pagamentoId]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        $pagamentoId = $id ?? null;

        if (!$pagamentoId) {
            throw new Exception("ID não enviado", 400);
        }
        if (!Pagamento::exist($pagamentoId)) {
            throw new Exception("Pagamento não encontrado", 404);
        }

        $res = Pagamento::deleteById($pagamentoId);
        if (!$res) {
            throw new Exception("Não foi possível deletar o pagamento", 500);
        }

        output(200, [
            "status" => "success",
            "data" => ["id" => $pagamentoId]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}