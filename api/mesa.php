

<?php

require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../controller/MesaController.php';

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
$basePath = "/projeto-pw2/api/mesa.php";
$relativeUri = str_replace($basePath, '', $requestUri);

$uriSegments = array_values(array_filter(explode('/', $relativeUri)));

$firstSegment = $uriSegments[0] ?? null;
$secondSegment = $uriSegments[1] ?? null;

$id = null;
$action = null;

if (is_numeric($firstSegment)) {
    $id = $firstSegment;
} else if ($firstSegment) {
    $action = $firstSegment;
}

$controller = new MesaController();
$controller->handleRequest($method, $id, $action, $data);
