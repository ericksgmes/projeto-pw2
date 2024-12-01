<?php

require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuarioController {
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
                        $this->obterUsuario($id);
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

    public function obterUsuario($id): void {
        $this->autenticarRequisicao();
        try {
            if (!Usuario::exist($id)) {
                throw new Exception("Usuário não encontrado", 404);
            }
            $usuario = Usuario::getById($id);
            jsonResponse(200, ["status" => "success", "data" => $usuario]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function listarTodos(): void {
        $this->autenticarRequisicao();
        try {
            $usuarios = Usuario::listar();
            jsonResponse(200, ["status" => "success", "data" => $usuarios]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function criar($data): void {
        try {
            if (!valid($data, ["nome", "username", "senha"])) {
                throw new Exception("Nome, username e/ou senha não encontrados", 400);
            }

            $isAdmin = isset($data["is_admin"]) && $data["is_admin"] ? 1 : 0;

            $insertedId = Usuario::cadastrar($data["nome"], $data["username"], $data["senha"], $isAdmin);
            jsonResponse(201, ["status" => "success", "data" => ["id" => $insertedId]]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function atualizar($id, $data): void {
        $this->autenticarRequisicao();

        try {
            // Verifica se os dados necessários foram enviados
            if (!valid($data, ["nome", "username"])) {
                throw new Exception("Nome e/ou username não encontrados", 400);
            }

            // Verifica se o usuário existe
            if (!Usuario::exist($id)) {
                throw new Exception("Usuário não encontrado", 404);
            }

            // Define o valor padrão para is_admin caso não esteja presente
            $isAdmin = isset($data["is_admin"]) ? ($data["is_admin"] ? 1 : 0) : null;

            // Chama o método de atualização no modelo
            Usuario::atualizar($id, $data["nome"], $data["username"], $isAdmin);

            // Retorna a resposta de sucesso
            jsonResponse(200, ["status" => "success", "data" => ["id" => $id]]);
        } catch (Exception $e) {
            // Retorna a resposta de erro
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function deletar($id): void {
        $this->autenticarRequisicao();
        try {
            if (!Usuario::exist($id)) {
                throw new Exception("Usuário não encontrado", 404);
            }

            Usuario::deleteById($id);
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

        if (!Usuario::exist($id)) {
            jsonResponse(404, ["status" => "error", "message" => "Usuário não encontrado"]);
            return;
        }

        Usuario::atualizarSenha($id, $novaSenha);
        jsonResponse(200, ["status" => "success", "message" => "Senha atualizada com sucesso"]);
    }

    public function autenticar($data): void {
        try {
            if (!valid($data, ["username", "senha"])) {
                throw new Exception("Username e/ou senha não fornecidos.", 400);
            }

            $usuario = Usuario::retornaUsuario($data["username"]);
            if (!$usuario || !password_verify($data["senha"], $usuario["senha"])) {
                throw new Exception("Username ou senha incorretos.", 401);
            }

            $token = JWT::encode([
                "id" => $usuario["id"],
                "username" => $usuario["username"],
                "nome" => $usuario["nome"],
                "is_admin" => $usuario["is_admin"],
                "iat" => time(),
                "exp" => time() + 3600
            ], self::$jwtSecret, self::$jwtAlgorithm);

            jsonResponse(200, [
                "status" => "success",
                "data" => [
                    "token" => $token,
                    "expira_em" => 3600,
                    "usuario" => [
                        "id" => $usuario["id"],
                        "username" => $usuario["username"],
                        "nome" => $usuario["nome"],
                        "is_admin" => $usuario["is_admin"]
                    ]
                ]
            ]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    private function autenticarRequisicao() {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                throw new Exception("Token não fornecido ou inválido.", 401);
            }

            $this->autenticarJWT($matches[1]);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function autenticarJWT($token): array {
        try {
            return (array) JWT::decode($token, new Key(self::$jwtSecret, self::$jwtAlgorithm));
        } catch (ExpiredException $e) {
            throw new Exception("Token expirado. Faça login novamente.", 401);
        } catch (Exception $e) {
            throw new Exception("Token inválido.", 401);
        }
    }
}
