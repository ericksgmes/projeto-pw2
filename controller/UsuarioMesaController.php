<?php

require_once(__DIR__ . "/../model/UsuarioMesa.php");
require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../config/AuthService.php");

class UsuarioMesaController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM']);
    }

    private function autenticarRequisicao()
    {
        try {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            return $this->authService->verificarToken($authHeader);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
            exit;
        }
    }

    /**
     * @OA\Get(...)
     */
    private function listar($usuarioId = null): void
    {
        $decodedToken = $this->autenticarRequisicao();

        // Apenas administradores podem listar todas as associações
        if (!$decodedToken['is_admin'] && $usuarioId !== $decodedToken['id']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            exit;
        }

        if ($usuarioId) {
            $associacoes = UsuarioMesa::getByUsuarioId($usuarioId);
        } else {
            $associacoes = UsuarioMesa::listar();
        }
        jsonResponse(200, ["status" => "success", "data" => $associacoes]);
    }

    /**
     * @OA\Post(...)
     */
    private function criar($data): void
    {
        $decodedToken = $this->autenticarRequisicao();

        // Apenas administradores podem criar associações
        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            exit;
        }

        if (!valid($data, ["id_usuario", "id_mesa"])) {
            throw new Exception("ID do usuário ou da mesa não encontrado", 400);
        }

        $id_usuario = $data["id_usuario"];
        $id_mesa = $data["id_mesa"];

        if (UsuarioMesa::exist($id_usuario, $id_mesa)) {
            throw new Exception("A associação já existe", 409);
        }

        UsuarioMesa::associar($id_usuario, $id_mesa);
        jsonResponse(201, [
            "status" => "success",
            "data" => ["id_usuario" => $id_usuario, "id_mesa" => $id_mesa]
        ]);
    }

    /**
     * @OA\Delete(...)
     */
    private function deletar($usuarioId, $mesaId): void
    {
        $decodedToken = $this->autenticarRequisicao();

        // Apenas administradores podem deletar associações
        if (!$decodedToken['is_admin']) {
            jsonResponse(403, ["status" => "error", "message" => "Acesso negado"]);
            exit;
        }

        if (!$usuarioId || !$mesaId) {
            throw new Exception("ID do usuário ou da mesa não enviado", 400);
        }

        if (!UsuarioMesa::exist($usuarioId, $mesaId)) {
            throw new Exception("Associação não encontrada", 404);
        }

        UsuarioMesa::desassociar($usuarioId, $mesaId);
        jsonResponse(200, [
            "status" => "success",
            "data" => ["id_usuario" => $usuarioId, "id_mesa" => $mesaId]
        ]);
    }

    public function handleRequest($method, $usuarioId = null, $mesaId = null, $data = null): void
    {
        try {
            switch ($method) {
                case 'GET':
                    $this->listar($usuarioId);
                    break;
                case 'POST':
                    $this->criar($data);
                    break;
                case 'DELETE':
                    $this->deletar($usuarioId, $mesaId);
                    break;
                default:
                    jsonResponse(405, ["status" => "error", "message" => "Método não permitido"]);
            }
        } catch (Exception $e) {
            jsonResponse($e->getCode() ?: 500, ["status" => "error", "message" => $e->getMessage()]);
        }
    }
}
