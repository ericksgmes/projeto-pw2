<?php

require_once __DIR__ . '/../model/Funcionario.php';
require_once __DIR__ . '/../config/utils.php';

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
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

    /**
     * Handle incoming requests for the Funcionario resource.
     *
     * @param string $method The HTTP method (GET, POST, PUT, DELETE)
     * @param int|null $id The ID of the Funcionario (if applicable)
     * @param string|null $action The action to perform (if applicable)
     * @param array|null $data The request data (for POST and PUT)
     */
    /**
     * Handle incoming requests for the Funcionario resource.
     *
     * @param string $method The HTTP method (GET, POST, PUT, DELETE)
     * @param int|null $id The ID of the Funcionario (if applicable)
     * @param string|null $action The action to perform (if applicable)
     * @param array|null $data The request data (for POST and PUT)
     */
    public function handleRequest(string $method, int $id = null, string $action = null, array $data = null): void
    {
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

    /**
     * @OA\Get(
     *     path="/funcionarios/{id}",
     *     summary="Obter detalhes de um funcionário",
     *     operationId="obterFuncionarioPorId",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Detalhes do funcionário"),
     *     @OA\Response(response="404", description="Funcionário não encontrado")
     * )
     */
    public function obterFuncionario($id): void
    {
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

    /**
     * @OA\Get(
     *     path="/funcionarios",
     *     summary="Listar todos os funcionários",
     *     operationId="listarFuncionarios",
     *     @OA\Response(response="200", description="Lista de funcionários")
     * )
     */
    public function listarTodos(): void
    {
        try {
            $funcionarios = Funcionario::listar();
            jsonResponse(200, ["status" => "success", "data" => $funcionarios]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/funcionarios",
     *     summary="Criar um novo funcionário",
     *     operationId="criarFuncionario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "username", "senha"},
     *             @OA\Property(property="nome", type="string"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="senha", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Funcionário criado com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="409", description="Username já existente")
     * )
     */
    public function criar($data): void
    {
        try {
            if (!valid($data, ["nome", "username", "senha"])) {
                throw new Exception("Nome, username e/ou senha não encontrados", 400);
            }

            if (Funcionario::existsByUsername($data["username"])) {
                throw new Exception("O username já existe. Tente outro.", 409);
            }

            $insertedId = Funcionario::cadastrar($data["nome"], $data["username"], $data["senha"]);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/funcionarios/{id}",
     *     summary="Atualizar um funcionário",
     *     operationId="atualizarFuncionario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "username"},
     *             @OA\Property(property="nome", type="string"),
     *             @OA\Property(property="username", type="string")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Funcionário atualizado com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="404", description="Funcionário não encontrado"),
     *     @OA\Response(response="409", description="Username já existente")
     * )
     */
    public function atualizar($id, $data): void
    {
        try {
            if (!valid($data, ["nome", "username"])) {
                throw new Exception("Nome e/ou username não encontrados", 400);
            }

            if (!Funcionario::exist($id)) {
                throw new Exception("Funcionário não encontrado", 404);
            }

            $funcionarioExistente = Funcionario::getByUsername($data["username"]);
            if ($funcionarioExistente && $funcionarioExistente['id'] != $id) {
                throw new Exception("O username já existe. Tente outro.", 409);
            }
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/funcionarios/{id}",
     *     summary="Deletar um funcionário",
     *     operationId="deletarFuncionario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Funcionário deletado com sucesso"),
     *     @OA\Response(response="404", description="Funcionário não encontrado")
     * )
     */
    public function deletar($id): void
    {
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

    /**
     * @OA\Put(
     *     path="/funcionarios/{id}/senha",
     *     summary="Atualizar a senha de um funcionário",
     *     operationId="atualizarSenhaFuncionario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"novaSenha"},
     *             @OA\Property(property="novaSenha", type="string")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Senha atualizada com sucesso"),
     *     @OA\Response(response="400", description="Dados inválidos"),
     *     @OA\Response(response="404", description="Funcionário não encontrado")
     * )
     */
    public function atualizarSenha($id, $novaSenha): void
    {
        try {
            if (empty($novaSenha)) {
                throw new Exception("Senha não pode estar vazia", 400);
            }

            if (!Funcionario::exist($id)) {
                throw new Exception("Funcionário não encontrado", 404);
            }

            Funcionario::atualizarSenha($id, $novaSenha);
            jsonResponse(200, ["status" => "success", "message" => "Senha atualizada com sucesso"]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
