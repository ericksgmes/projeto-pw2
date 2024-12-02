<?php
function jsonResponse($statusCode, $data)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);

    exit;
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
