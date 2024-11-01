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

// Se $_GET['id'] não estiver definido, atribui o valor de $id
if (!isset($_GET['id']) && $id) {
    $_GET['id'] = $id;
}

if (method("GET")) {
    try {
        $pagamentoId = $_GET["id"] ?? null; // Considera ambos os casos

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
                ["rel" => "self", "href" => "/pagamentos/" . $res],
                ["rel" => "update", "href" => "/pagamentos/" . $res],
                ["rel" => "delete", "href" => "/pagamentos/" . $res]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        $pagamentoId = $_GET["id"] ?? $id;

        if (!$pagamentoId) {
            throw new Exception("ID não enviado", 404);
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
        $pagamentoId = $_GET["id"] ?? $id;

        if (!$pagamentoId) {
            throw new Exception("ID não enviado", 404);
        }
        if (!valid($data, ["metodo", "valor"])) {
            throw new Exception("Método de pagamento ou valor não encontrado", 400);
        }
        if (!Pagamento::exist($pagamentoId)) {
            throw new Exception("Pagamento não encontrado", 404);
        }
        $metodo = paymentMethodEnum::from($data["metodo"]);
        $valor = $data["valor"];

        $res = Pagamento::atualizar($pagamentoId, $metodo, $valor);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o pagamento", 500);
        }

        output(200, [
            "status" => "success",
            "data" => $res,
            "links" => [
                ["rel" => "self", "href" => "/pagamentos/" . $pagamentoId],
                ["rel" => "delete", "href" => "/pagamentos/" . $pagamentoId]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}
