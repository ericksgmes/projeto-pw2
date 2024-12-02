<?php

require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../config/AuthService.php';
require_once __DIR__ . '/../vendor/autoload.php';

class UsuarioController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']);
    }

    private function autenticarRequisicao() {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            return $this->authService->verificarToken($authHeader);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    public function handleRequest(string $method, int $id = null, string $action = null, array $data = null): void {
        try {
            $decodedToken = $this->autenticarRequisicao();

            if ($method === 'GET' && !$decodedToken['is_admin']) {
                throw new Exception("Acesso negado: apenas administradores podem listar usuários.", 403);
            }

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
                    if ($action === 'parcial') {
                        $this->atualizacaoParcial($id, $data);
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

            // Gera o token usando o AuthService
            $token = $this->authService->gerarToken($usuario);

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


    public function atualizacaoParcial($id, $data): void {
        $this->autenticarRequisicao();

        try {
            // Verifica se o usuário existe
            if (!Usuario::exist($id)) {
                throw new Exception("Usuário não encontrado.", 404);
            }

            // Verifica se há dados para atualizar
            if (empty($data)) {
                throw new Exception("Nenhum dado fornecido para atualização.", 400);
            }

            // Monta os campos para atualização
            $camposAtualizados = [];

            if (!empty($data['nome'])) {
                $camposAtualizados['nome'] = $data['nome'];
            }

            if (!empty($data['username'])) {
                $camposAtualizados['username'] = $data['username'];
            }

            if (!empty($data['novaSenha'])) {
                $this->atualizarSenha($id, $data['novaSenha']);
            }

            if (isset($data['is_admin'])) {
                $camposAtualizados['is_admin'] = $data['is_admin'] ? 1 : 0;
            }

            // Valida se há algo a ser atualizado
            if (empty($camposAtualizados)) {
                throw new Exception("Nenhum dado válido para atualização.", 400);
            }

            // Atualiza no banco
            Usuario::atualizarParcial($id, $camposAtualizados);

            // Retorna sucesso
            jsonResponse(200, [
                "status" => "success",
                "message" => "Usuário atualizado com sucesso.",
                "data" => ["id" => $id]
            ]);
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
