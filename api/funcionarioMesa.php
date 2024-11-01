<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/FuncionarioMesa.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/funcionarioMesa.php";
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = array_values(array_filter(explode('/', $relativeUri)));

$funcionarioId = $uriSegments[0] ?? null;
$mesaId = $uriSegments[1] ?? null;

if (method("GET")) {
    try {
        if ($funcionarioId && $mesaId) {
            // Obter associação específica (opcional)
            throw new Exception("Operação não suportada", 400);
        } elseif ($funcionarioId) {
            // Listar mesas associadas a um funcionário
            $associacoes = FuncionarioMesa::getByFuncionarioId($funcionarioId);
            output(200, [
                "status" => "success",
                "data" => $associacoes
            ]);
        } else {
            // Listar todas as associações
            $associacoes = FuncionarioMesa::listar();
            output(200, [
                "status" => "success",
                "data" => $associacoes
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

        if (!valid($data, ["id_funcionario", "id_mesa"])) {
            throw new Exception("ID do funcionário ou da mesa não encontrado", 400);
        }

        $id_funcionario = $data["id_funcionario"];
        $id_mesa = $data["id_mesa"];

        // Verifica se a associação já existe
        if (FuncionarioMesa::exist($id_funcionario, $id_mesa)) {
            throw new Exception("A associação já existe", 409);
        }

        $res = FuncionarioMesa::associar($id_funcionario, $id_mesa);
        if (!$res) {
            throw new Exception("Não foi possível criar a associação", 500);
        }

        output(201, [
            "status" => "success",
            "data" => ["id_funcionario" => $id_funcionario, "id_mesa" => $id_mesa],
            "links" => [
                ["rel" => "self", "href" => "/funcionarioMesa/" . $id_funcionario . "/" . $id_mesa],
                ["rel" => "delete", "href" => "/funcionarioMesa/" . $id_funcionario . "/" . $id_mesa]
            ]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}

if (method("DELETE")) {
    try {
        if (!$funcionarioId || !$mesaId) {
            throw new Exception("ID do funcionário ou da mesa não enviado", 400);
        }

        if (!FuncionarioMesa::exist($funcionarioId, $mesaId)) {
            throw new Exception("Associação não encontrada", 404);
        }

        $res = FuncionarioMesa::desassociar($funcionarioId, $mesaId);
        if (!$res) {
            throw new Exception("Não foi possível remover a associação", 500);
        }

        output(200, [
            "status" => "success",
            "data" => ["id_funcionario" => $funcionarioId, "id_mesa" => $mesaId]
        ]);
    } catch (Exception $e) {
        $code = $e->getCode() >= 100 ? $e->getCode() : 500;
        output($code, ["status" => "error", "message" => $e->getMessage()]);
    }
}