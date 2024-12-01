<?php

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

function jsonResponse($statusCode, $data)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);

    exit;
}

function verificarToken($jwtSecret, $jwtAlgorithm) {
    try {
        // Obter o cabeçalho Authorization
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        error_log("Cabeçalho Authorization recebido: " . json_encode($authHeader));

        if (!$authHeader) {
            throw new Exception("Token não fornecido.", 401);
        }

        // Extração do token do cabeçalho
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            error_log("Token extraído: " . $token);
        } else {
            throw new Exception("Token inválido.", 401);
        }

        // Decodificar o token
        $decoded = JWT::decode($token, new Key($jwtSecret, $jwtAlgorithm));
        error_log("Token decodificado com sucesso: " . json_encode($decoded));

        // Validações adicionais do payload
        if (empty($decoded->id) || empty($decoded->username)) {
            throw new Exception("Token inválido: campos obrigatórios ausentes.", 401);
        }

        // Verificar expiração manualmente (opcional, se o pacote não tratar)
        if (isset($decoded->exp) && $decoded->exp < time()) {
            throw new Exception("Token expirado.", 401);
        }

        return $decoded;
    } catch (ExpiredException $e) {
        error_log("Token expirado: " . $e->getMessage());
        throw new Exception("Token expirado.", 401);
    } catch (SignatureInvalidException $e) {
        error_log("Assinatura do token inválida: " . $e->getMessage());
        throw new Exception("Token inválido.", 401);
    } catch (Exception $e) {
        error_log("Erro ao verificar token: " . $e->getMessage());
        throw new Exception("Acesso não autorizado: " . $e->getMessage(), 401);
    }
}


function method($method)
{
    if (!strcasecmp($_SERVER['REQUEST_METHOD'], $method)) {
        return true;
    }
    return false;
}

function valid($metodo, $lista)
{
    $obtidos = array_keys($metodo);
    $nao_encontrados = array_diff($lista, $obtidos);
    if (empty($nao_encontrados)) {
        foreach ($lista as $p) {
            if (empty(trim($metodo[$p]))) {
                return false;
            }
        }
        return true;
    }
    return false;
}
