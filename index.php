<?php

require_once __DIR__ . '/controller/FuncionarioController.php';
require_once __DIR__ . '/controller/FuncionarioMesaController.php';
require_once __DIR__ . '/controller/MesaController.php';
require_once __DIR__ . '/controller/PagamentoController.php';
require_once __DIR__ . '/controller/ProdutoController.php';
require_once __DIR__ . '/controller/ProdutosMesaController.php';
require_once __DIR__ . '/config/utils.php';

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
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
$action = $uriSegments[2] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

switch ($resource) {
    case 'funcionarios':
        $controller = new FuncionarioController();
        $controller->handleRequest($method, $id, $data);
        break;

    case 'funcionario-mesa':
        $controller = new FuncionarioMesaController();
        $controller->handleRequest($method, $id, $action, $data);
        break;

    case 'mesas':
        $controller = new MesaController();
        $controller->handleRequest($method, $id, $action, $data);
        break;

    case 'pagamentos':
        $controller = new PagamentoController();
        $controller->handleRequest($method, $id, $action, $data);
        break;

    case 'produtos':
        $controller = new ProdutoController();
        $controller->handleRequest($method, $id, $action, $data);
        break;

    case 'produtos-mesa':
        $controller = new ProdutosMesaController();
        $controller->handleRequest($method, $id, $action, $data);
        break;

    default:
        jsonResponse(404, ["status" => "error", "message" => "Recurso não encontrado"]);
}
