<?php

require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../controller/FuncionarioController.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Obter o método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Se for uma requisição OPTIONS, enviar os headers e encerrar
if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obter os dados JSON da requisição
$data = handleJsonInput();

// Obter o caminho da URI após o script
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = "/projeto-pw2/api/funcionario.php";
$relativeUri = str_replace($basePath, '', $requestUri);

// Reindexar os segmentos da URI
$uriSegments = array_values(array_filter(explode('/', $relativeUri)));

// Extrair o ID
$id = $uriSegments[0] ?? null;

// Instanciar o controlador e lidar com a requisição
$controller = new FuncionarioController();
$controller->handleRequest($method, $id, $data);
