<?php

require_once __DIR__ . '/controller/UsuarioController.php';
require_once __DIR__ . '/controller/UsuarioMesaController.php';
require_once __DIR__ . '/controller/MesaController.php';
require_once __DIR__ . '/controller/PagamentoController.php';
require_once __DIR__ . '/controller/ProdutoController.php';
require_once __DIR__ . '/controller/ProdutosMesaController.php';
require_once __DIR__ . '/config/utils.php';
require_once __DIR__ . '/config/AuthService.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$jwtSecret = $_ENV['JWT_SECRET'];
$jwtAlgorithm = $_ENV['JWT_ALGORITHM'];

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

$basePath = '/restaurante-webservice';
$relativeUri = str_replace($basePath, '', $requestUri);
$uriSegments = explode('/', trim($relativeUri, '/'));

$resource = $uriSegments[0] ?? '';
$id = $uriSegments[1] ?? null;
$action = $uriSegments[2] ?? null;

// Instanciar o AuthService
$authService = new AuthService($jwtSecret, $jwtAlgorithm);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

switch ($resource) {
    case 'auth':
        $controller = new UsuarioController();
        $controller->autenticar($data);
        break;
    case 'signup':
        $controller = new UsuarioController();
        $controller->criar($data);
        break;
    case 'usuarios':
    case 'usuario-mesa':
    case 'mesas':
    case 'pagamentos':
    case 'produtos':
    case 'produtos-mesa':
        try {
            // Verifica o token usando o AuthService
            $decodedToken = $authService->verificarToken($_SERVER['HTTP_AUTHORIZATION'] ?? '');
            $controller = match ($resource) {
                'usuarios' => new UsuarioController(),
                'usuario-mesa' => new UsuarioMesaController(),
                'mesas' => new MesaController(),
                'pagamentos' => new PagamentoController(),
                'produtos' => new ProdutoController(),
                'produtos-mesa' => new ProdutosMesaController(),
            };
            $controller->handleRequest($method, $id, $action, $data);
        } catch (Exception $e) {
            jsonResponse(401, ["status" => "error", "message" => $e->getMessage()]);
        }
        break;

    default:
        jsonResponse(404, ["status" => "error", "message" => "Recurso não encontrado"]);
}
