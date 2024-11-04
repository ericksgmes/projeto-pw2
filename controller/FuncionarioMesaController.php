<?php

require_once(__DIR__ . "/../model/FuncionarioMesa.php");
require_once(__DIR__ . "/../config/utils.php");

class FuncionarioMesaController {
    public function handleRequest($method, $funcionarioId = null, $mesaId = null, $data = null): void {
        try {
            switch ($method) {
                case 'GET':
                    $this->listar($funcionarioId);
                    break;
                case 'POST':
                    $this->criar($data);
                    break;
                case 'DELETE':
                    $this->deletar($funcionarioId, $mesaId);
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    private function listar($funcionarioId = null): void {
        if ($funcionarioId) {
            $associacoes = FuncionarioMesa::getByFuncionarioId($funcionarioId);
            jsonResponse(200, ["status" => "success", "data" => $associacoes]);
        } else {
            $associacoes = FuncionarioMesa::listar();
            jsonResponse(200, ["status" => "success", "data" => $associacoes]);
        }
    }

    private function criar($data): void {
        if (!valid($data, ["id_funcionario", "id_mesa"])) {
            throw new Exception("ID do funcionário ou da mesa não encontrado", 400);
        }

        $id_funcionario = $data["id_funcionario"];
        $id_mesa = $data["id_mesa"];

        if (FuncionarioMesa::exist($id_funcionario, $id_mesa)) {
            throw new Exception("A associação já existe", 409);
        }

        FuncionarioMesa::associar($id_funcionario, $id_mesa);
        jsonResponse(201, [
            "status" => "success",
            "data" => ["id_funcionario" => $id_funcionario, "id_mesa" => $id_mesa]
        ]);
    }

    private function deletar($funcionarioId, $mesaId): void {
        if (!$funcionarioId || !$mesaId) {
            throw new Exception("ID do funcionário ou da mesa não enviado", 400);
        }

        if (!FuncionarioMesa::exist($funcionarioId, $mesaId)) {
            throw new Exception("Associação não encontrada", 404);
        }

        FuncionarioMesa::desassociar($funcionarioId, $mesaId);
        jsonResponse(200, [
            "status" => "success",
            "data" => ["id_funcionario" => $funcionarioId, "id_mesa" => $mesaId]
        ]);
    }
}
