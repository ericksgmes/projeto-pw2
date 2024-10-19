<?php


function output($statusCode, $data)
{
    http_response_code($statusCode);
    // Define o cabeçalho de resposta como JSON
    header('Content-Type: application/json');
    echo json_encode($data);

    exit();
}

function handleJSONInput()
{
    try {
        $json = file_get_contents('php://input');
        $json = json_decode($json, true);
        if ($json == null) {
            throw new Exception("JSON não enviado", 0);
        }
        return $json;
    } catch (Exception $e) {
        return false;
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