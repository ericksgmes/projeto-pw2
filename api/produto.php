<?php

// Atualização em produto.php

require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../controller/ProdutoController.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = handleJsonInput();

$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);
$relativeUri = substr($requestUri, strlen($scriptName));

$uriSegments = array_values(array_filter(explode('/', $relativeUri)));

$action = null;
$id = null;

if (isset($uriSegments[0])) {
    if (is_numeric($uriSegments[0])) {
        $id = $uriSegments[0];
    } else {
        $action = $uriSegments[0];
    }
}

$controller = new ProdutoController();
$controller->handleRequest($method, $id, $action, $data);
