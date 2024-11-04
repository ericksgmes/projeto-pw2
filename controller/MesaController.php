<?php

require_once __DIR__ . '/../model/Mesa.php';
require_once __DIR__ . '/../config/utils.php';

class MesaController {
    public function handleRequest($method, $id = null, $action = null, $data = null): void {
        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'deletadas') {
                        $this->listarDeletadas();
                    } else {
                        $this->listar($id);
                    }
                    break;
                case 'POST':
                    $this->criar($data);
                    break;
                case 'PUT':
                    if ($id) {
                        $this->atualizar($id, $data);
                    } else {
                        jsonResponse(400, ["status" => "error", "message" => "ID necessário para atualização"]);
                    }
                    break;
                case 'DELETE':
                    if ($id) {
                        $this->deletar($id);
                    } else {
                        jsonResponse(400, ["status" => "error", "message" => "ID necessário para exclusão"]);
                    }
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            $code = $e->getCode() >= 100 ? $e->getCode() : 500;
            jsonResponse($code, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function listar($id = null): void {
        if ($id) {
            $mesa = Mesa::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $mesa]);
        } else {
            $mesas = Mesa::listar();
            jsonResponse(200, ["status" => "success", "data" => $mesas]);
        }
    }

    public function listarDeletadas(): void {
        $mesas = Mesa::listarDeletadas();
        jsonResponse(200, ["status" => "success", "data" => $mesas]);
    }

    public function criar($data): void {
        if (!valid($data, ["numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Número da mesa não fornecido"]);
            return;
        }

        $insertedId = Mesa::criar($data["numero"]);
        jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
    }

    public function atualizar($id, $data): void {
        if (!valid($data, ["numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Número da mesa não fornecido"]);
            return;
        }

        Mesa::atualizar($id, $data["numero"]);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    public function deletar($id): void {
        Mesa::deleteById($id);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }
}
