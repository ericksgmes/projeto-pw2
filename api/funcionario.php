<?php

require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../model/Funcionario.php");

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = handleJsonInput();

if (method("GET")) {
    try {
        if (valid($_GET, ["id"])) {
            // Verifica se o funcionário existe
            if (!Funcionario::exist($_GET["id"])) {
                throw new Exception("Funcionário não encontrado", 404);
            }
            // Busca funcionário por ID
            $funcionario = Funcionario::getById($_GET["id"]);
            output(200, $funcionario);
        } else {
            // Lista todos os funcionários
            $list = Funcionario::listar();
            output(200, $list);
        }
    } catch (Exception $e) {
        // Se o código for 0, define como 500 (erro interno)
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["msg" => $e->getMessage()]);
    }
}

if (method("POST")) {
    try {
        if (!$data) {
            throw new Exception("Nenhuma informação encontrada", 404);
        }

        // Verifica se os campos "nome", "username", "senha" estão presentes
        if (!valid($data, ["nome", "username", "senha"])) {
            throw new Exception("Nome, username e/ou senha não encontrados", 400);
        }

        // Garante que apenas os 3 campos esperados estão presentes
        if (count(array_keys($data)) !== 3) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }

        // Verifica se o username já existe
        if (Funcionario::existsByUsername($data["username"])) {
            throw new Exception("O username já existe. Tente outro.", 409);
        }

        // Cadastra o funcionário
        $res = Funcionario::cadastrar($data["nome"], $data["username"], $data["senha"]);
        if (!$res) {
            throw new Exception("Não foi possível cadastrar o funcionário", 500);
        }

        output(201, ["msg" => "Funcionário criado com sucesso"]);
    } catch (Exception $e) {
        $code = $e->getCode() > 100 ? $e->getCode() : 500;
        output($code, ["msg" => $e->getMessage()]);
    }
}

if(method("PUT")) {
    try {
        if(!$data) {
            throw new Exception("Nenhuma informação encontrada", 404);
        }
        if(!valid($_GET, ["id"])) {
            throw new Exception("ID não enviado", 404);
        }
        if(count($_GET) != 1) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if(!valid($data,["nome", "username"])) {
            throw new Exception("Nome e/ou username não encontrados", 404);
        }
        if(count($data) != 2) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if(!Funcionario::exist($_GET["id"])) {
            throw new Exception("Usuário não encontrado", 400);
        }

        $nome = $data["nome"];
        $username = $data["username"];
        $res = Funcionario::atualizar($_GET["id"], $nome, $username);
        if (!$res) {
            throw new Exception("Não foi possível atualizar o funcionário", 500);
        }

        output(200, ["msg" => "Usuário editado com sucesso"]);
    } catch (Exception $e) {
        output($e->getCode(), ["msg" => $e->getMessage()]);
    }
}

