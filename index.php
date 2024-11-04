<?php

require_once __DIR__ . '/controller/FuncionarioController.php';
require_once __DIR__ . '/controller/FuncionarioMesaController.php';
require_once __DIR__ . '/config/utils.php';

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

$basePath = '/resturante-webservice';
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = explode('/', trim($relativeUri, '/'));

$resource = $uriSegments[0] ?? '';
$id = $uriSegments[1] ?? null;
$id2 = $uriSegments[2] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

switch ($resource) {
    case 'funcionario':
        $controller = new FuncionarioController();
        $controller->handleRequest($method, $id, $data);
        break;

    case 'funcionarioMesa':
        $controller = new FuncionarioMesaController();
        $controller->handleRequest($method, $id, $id2, $data);
        break;

    default:
        jsonResponse(404, ["status" => "error", "message" => "Recurso não encontrado"]);
}
