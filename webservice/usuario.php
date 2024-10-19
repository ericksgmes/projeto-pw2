<?php

// Arquivos com funções úteis que vão ser usadas nesta rota.
require_once(__DIR__ . "/configs/utils.php");
// Arquivos com as entidades (models) que vão ser usadas nesta rota.
 require_once(__DIR__ . "/model/Usuario.php");

// Bloco de código configurando o servidor. Remover os métodos que não forem suportados.
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Usado para receber os dados brutos do corpo da requisição.
// Caso não tenha sido enviado nada no formato JSON, retorna FALSE.
$data = handleJSONInput();


// Listar algo.
if(method("GET")) {
    try {
        
        if(valid($_GET, ["id"])) {
            if(!Usuario::exist($_GET["id"])) {
                throw new Exception("Usuário não encontrado", 400);
            }

        }
        // Listar todos usuários
        

        $lista = Usuario::listar();

        output(200, $lista);
    } catch (Exception $e) {
        //throw $th;
    }
}

// Cadastrar
if(method("POST")) {
    try {
        if(!$data) {
            throw new Exception("Nenhuma informação encontrada", 404);
        }
        if(!valid($data,["nome", "data_nascimento"])) {
            throw new Exception("Nome e/ou data_nascimento não encontrados", 404);
        }
        if(count($data) != 2) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        // Todas as checagens possíveis.
        // ...
        $res = Usuario::insert($data["nome"], $data["data_nascimento"]);
        if(!$res) {
            throw new Exception("Não foi possível cadastrar o usuário", 500);
        }
        output(200, ["msg" => "Usuário criado com sucesso"]);
    } catch (Exception $e) {
        output($e->getCode(), ["msg" => $e->getMessage()]);
    }
}

// Editar
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
        if(!valid($data,["nome", "data_nascimento"])) {
            throw new Exception("Nome e/ou data_nascimento não encontrados", 404);
        }
        if(count($data) != 2) {
            throw new Exception("Foram enviados dados desconhecidos", 400);
        }
        if(!Usuario::exist($_GET["id"])) {
            throw new Exception("Usuário não encontrado", 400);
        }
        //$res = Usuario::update($_GET["id"], $data["nome"], $data["data_nascimento"]);
        //if(!$res) {
        //    throw new Exception("Não foi possível editar o usuário", 500);
        //}
        output(200, ["msg" => "Usuário editado com sucesso"]);
    } catch (Exception $e) {
        output($e->getCode(), ["msg" => $e->getMessage()]);
    }
}

?>