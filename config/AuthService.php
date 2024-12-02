<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthService {
    private $jwtSecret;
    private $jwtAlgorithm;

    public function __construct($jwtSecret, $jwtAlgorithm) {
        $this->jwtSecret = $jwtSecret;
        $this->jwtAlgorithm = $jwtAlgorithm;
    }

    public function verificarToken($authHeader): array {
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new Exception("Token não fornecido ou inválido.", 401);
        }

        $token = $matches[1];

        try {
            return (array) JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgorithm));
        } catch (ExpiredException $e) {
            throw new Exception("Token expirado. Faça login novamente.", 401);
        } catch (Exception $e) {
            throw new Exception("Token inválido.", 401);
        }
    }

    public function verificarPermissaoAdmin($decodedToken): void {
        if (!$decodedToken['is_admin']) {
            throw new Exception("Acesso negado: apenas administradores podem realizar esta ação.", 403);
        }
    }

    public function gerarToken(array $usuario): string {
        return JWT::encode([
            "id" => $usuario["id"],
            "username" => $usuario["username"],
            "nome" => $usuario["nome"],
            "is_admin" => $usuario["is_admin"],
            "iat" => time(),
            "exp" => time() + 3600
        ], $this->jwtSecret, $this->jwtAlgorithm);
    }
}
