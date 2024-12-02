<?php

require_once __DIR__ . '/../model/Mesa.php';
require_once __DIR__ . '/../config/AuthService.php';

class MesaController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']);
    }

    private function autenticarRequisicao(): array {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            return $this->authService->verificarToken($authHeader);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function listar($id = null): void {
        $decodedToken = $this->autenticarRequisicao();

        try {
            if ($id) {
                $mesa = Mesa::getById($id);
                if (!$mesa) {
                    jsonResponse(404, ["status" => "error", "message" => "Mesa não encontrada"]);
                    return;
                }
                jsonResponse(200, ["status" => "success", "data" => $mesa]);
            } else {
                $mesas = Mesa::listar();
                jsonResponse(200, ["status" => "success", "data" => $mesas]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function listarDeletadas(): void {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem listar mesas deletadas"]);
            return;
        }

        try {
            $mesas = Mesa::listarDeletadas();
            jsonResponse(200, ["status" => "success", "data" => $mesas]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function criar($data): void {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem criar mesas"]);
            return;
        }

        if (!valid($data, ["numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Número da mesa não fornecido"]);
            return;
        }

        try {
            $mesaExistente = Mesa::existsByNumber($data["numero"]);
            if ($mesaExistente && $mesaExistente["deletado"] == 0) {
                jsonResponse(409, ["status" => "error", "message" => "Uma mesa com este número já existe"]);
                return;
            }

            $insertedId = Mesa::criar($data["numero"]);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function atualizar($id, $data): void {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem atualizar mesas"]);
            return;
        }

        if (!valid($data, ["numero"])) {
            jsonResponse(400, ["status" => "error", "message" => "Número da mesa não fornecido"]);
            return;
        }

        try {
            $mesaExistente = Mesa::existsByNumber($data["numero"]);
            if ($mesaExistente && $mesaExistente["id"] != $id && $mesaExistente["deletado"] == 0) {
                jsonResponse(409, ["status" => "error", "message" => "Uma mesa com este número já existe"]);
                return;
            }

            Mesa::atualizar($id, $data["numero"]);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function deletar($id): void {
        $decodedToken = $this->autenticarRequisicao();

        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado: apenas administradores podem deletar mesas"]);
            return;
        }

        try {
            Mesa::deleteById($id);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function handleRequest($method, $id = null, $action = null, $data = null): void {
        try {
            if ($method === 'GET') {
                if ($action === 'deletadas') {
                    $this->listarDeletadas();
                } else {
                    $this->listar($id);
                }
            } elseif ($method === 'POST') {
                $this->criar($data);
            } elseif ($method === 'PUT') {
                if ($id) {
                    $this->atualizar($id, $data);
                } else {
                    jsonResponse(400, ["status" => "error", "message" => "ID necessário para atualização"]);
                }
            } elseif ($method === 'DELETE') {
                if ($id) {
                    $this->deletar($id);
                } else {
                    jsonResponse(400, ["status" => "error", "message" => "ID necessário para exclusão"]);
                }
            } else {
                jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
