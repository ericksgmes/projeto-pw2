<?php

require_once __DIR__ . '/../model/Funcionario.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

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
    private static $jwtSecret;
    private static $jwtAlgorithm;

    public function __construct() {
        self::$jwtSecret = $_ENV['JWT_SECRET'];
        self::$jwtAlgorithm = $_ENV['JWT_ALGORITHM'];
    }

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
                    if ($action === 'nome') {
                        $this->atualizarNome($id, $data['nome'] ?? '');
                    } elseif ($action === 'senha') {
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

    public function atualizarNome($id, $novoNome): void {
        $this->autenticarRequisicao();
        error_log("Iniciando atualização de nome para o ID: $id", 0);

        if (empty($novoNome)) {
            error_log("Novo nome não fornecido para o ID: $id", 0);
            jsonResponse(400, ["status" => "error", "message" => "Nome não pode estar vazio"]);
            return;
        }

        if (!Funcionario::exist($id)) {
            error_log("Funcionário com ID $id não encontrado", 0);
            jsonResponse(404, ["status" => "error", "message" => "Funcionário não encontrado"]);
            return;
        }

        Funcionario::atualizarNome($id, $novoNome);
        error_log("Atualização de nome concluída com sucesso para ID: $id", 0);

        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }


    public function obterFuncionario($id): void {
        $this->autenticarRequisicao();
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
        $this->autenticarRequisicao();
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

            if (Funcionario::existsByUsername($data["username"])) {
                throw new Exception("Username já existente", 400);
            }
            $insertedId = Funcionario::cadastrar($data["nome"], $data["username"], $data["senha"]);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function atualizar($id, $data): void {
        $this->autenticarRequisicao();
        error_log("Iniciando atualização para o ID: $id", 0);

        if (!valid($data, ["nome", "username"])) {
            error_log("Dados inválidos fornecidos: " . json_encode($data), 0);
            jsonResponse(400, ["status" => "error", "message" => "Nome e/ou username não encontrados"]);
            return;
        }

        if (!Funcionario::exist($id)) {
            error_log("Funcionário com ID $id não encontrado", 0);
            jsonResponse(404, ["status" => "error", "message" => "Funcionário não encontrado"]);
            return;
        }

        if (Funcionario::existsByUsername($data["username"])) {
            error_log("Username duplicado detectado: " . $data["username"], 0);
            jsonResponse(409, ["status" => "error", "message" => "O username já existe. Tente outro."]);
            return;
        }

        Funcionario::atualizar($id, $data["nome"], $data["username"]);
        error_log("Atualização concluída com sucesso para ID: $id", 0);

        jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
    }

    public function deletar($id): void {
        $this->autenticarRequisicao();
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
        $this->autenticarRequisicao();
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

    public function autenticar($data) {
        try {
            error_log("Dados recebidos no login: " . json_encode($data));
            if (!valid($data, ["username", "senha"])) {
                throw new Exception("Username e/ou senha não fornecidos.", 400);
            }

            $funcionario = Funcionario::retornaUsuario($data["username"]);
            if (!$funcionario) {
                error_log("Usuário não encontrado: " . $data["username"]);
                throw new Exception("Username ou senha incorretos.", 401);
            }

            if (!password_verify($data["senha"], $funcionario["senha"])) {
                error_log("Senha incorreta para o usuário: " . $data["username"]);
                throw new Exception("Username ou senha incorretos.", 401);
            }

            // Gerar o JWT
            $token = JWT::encode([
                "id" => $funcionario["id"],
                "username" => $funcionario["username"],
                "nome" => $funcionario["nome"],
                "iat" => time(), // Data de emissão
                "exp" => time() + 3600 // Expiração em 1 hora
            ], self::$jwtSecret, self::$jwtAlgorithm);

            error_log("Token gerado para o usuário: " . $data["username"]);

            // Resposta com o token e informações do funcionário
            jsonResponse(200, [
                "status" => "success",
                "data" => [
                    "token" => $token,
                    "expira_em" => 3600, // Expiração em segundos
                    "funcionario" => [
                        "id" => $funcionario["id"],
                        "username" => $funcionario["username"],
                        "nome" => $funcionario["nome"]
                    ]
                ]
            ]);
        } catch (Exception $e) {
            error_log("Erro ao autenticar: " . $e->getMessage());
            jsonResponse($e->getCode() ?: 500, [
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function autenticarJWT($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$jwtSecret, self::$jwtAlgorithm));
            return (array) $decoded; // Retorna os dados decodificados como um array
        } catch (ExpiredException $e) {
            throw new Exception("Token expirado. Faça login novamente.", 401);
        } catch (Exception $e) {
            throw new Exception("Token inválido: " . $e->getMessage(), 401);
        }
    }

    private function autenticarRequisicao() {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                throw new Exception("Token não fornecido ou inválido.", 401);
            }

            return $this->autenticarJWT($matches[1]);
        } catch (Exception $e) {
            jsonResponse(401, [
                "status" => "error",
                "message" => "Acesso não autorizado: " . $e->getMessage()
            ]);
        }
    }


}
