<?php
require_once __DIR__ . '/../model/Funcionario.php';
require_once __DIR__ . '/../config/utils.php';

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="2.0.0",
 *         title="API Restaurante Webservice",
 *         description="Documentação da API para o sistema de gerenciamento de restaurante."
 *     ),
 *     @OA\Server(
 *         url="http://localhost/restaurante-webservice",
 *         description="Servidor local de desenvolvimento"
 *     )
 * )
 */

class FuncionarioController {

    public function handleRequest(string $method, int $id = null, string $action = null, array $data = null): void {
        try {
            switch (strtoupper($method)) {
                case 'GET':
                    if ($id) {
                        $this->obterFuncionario($id);
                    } else {
                        $this->listarTodos();
                    }
                    break;
                case 'POST':
                    $this->criar($data);
                    break;
                case 'PUT':
                    if ($action === 'senha') {
                        $this->atualizarSenha($id, $data['novaSenha'] ?? '');
                    } else {
                        $this->atualizar($id, $data);
                    }
                    break;
                case 'DELETE':
                    $this->deletar($id);
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function obterFuncionario($id): void {
        try {
            if (!Funcionario::exist($id)) {
                throw new Exception("Funcionário não encontrado", 404);
            }
            $funcionario = Funcionario::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $funcionario]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function listarTodos(): void {
        try {
            $funcionarios = Funcionario::listar();
            jsonResponse(200, ["status" => "success", "data" => $funcionarios]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function criar($data): void {
        try {
            if (!valid($data, ["nome", "username", "senha"])) {
                throw new Exception("Nome, username e/ou senha não encontrados", 400);
            }

            $funcionarioExistente = Funcionario::getByUsername($data["username"]);
            if ($funcionarioExistente && $funcionarioExistente["deletado"] == 0) {
                throw new Exception("O username já existe. Tente outro.", 409);
            }

            $insertedId = Funcionario::cadastrar($data["nome"], $data["username"], $data["senha"]);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function atualizar($id, $data): void {
        if (!valid($data, ["nome", "username"])) {
            jsonResponse(400, ["status" => "error", "message" => "Nome e/ou username não encontrados"]);
            return;
        }
    
        if (!Funcionario::exist($id)) {
            jsonResponse(404, ["status" => "error", "message" => "Funcionário não encontrado"]);
            return;
        }
    
        $funcionarioExistente = Funcionario::getByUsername($data["username"]);
        if ($funcionarioExistente && $funcionarioExistente['id'] != $id) {
            jsonResponse(409, ["status" => "error", "message" => "O username já existe. Tente outro."]);
            return;
        }
    
        Funcionario::atualizar($id, $data["nome"], $data["username"]);
        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    
    public function deletar($id): void {
        try {
            if (!Funcionario::exist($id)) {
                throw new Exception("Funcionário não encontrado", 404);
            }

            Funcionario::deleteById($id);
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function atualizarSenha($id, $novaSenha): void {
        if (empty($novaSenha)) {
            jsonResponse(400, ["status" => "error", "message" => "Senha não pode estar vazia"]);
            return;
        }
    
        if (!Funcionario::exist($id)) {
            jsonResponse(404, ["status" => "error", "message" => "Funcionário não encontrado"]);
            return;
        }
    
        Funcionario::atualizarSenha($id, $novaSenha);
        jsonResponse(200, ["status" => "success", "message" => "Senha atualizada com sucesso"]);
    }
    
}
