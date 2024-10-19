<?php

require_once(__DIR__ . "/../configs/Database.php");
// Caso seja necessário acessar alguma função global auxiliar.
require_once(__DIR__ . "/../configs/utils.php");

class Usuario
{

    public static function listar() {
        try {
            $conexao = Conexao::getConexao();
            $sql = $conexao->prepare("SELECT * FROM usuarios");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e-getMessage()]);
        }
    }

    public static function insert($nome, $data) {
        try {
            $conexao = Conexao::getConexao();
            $sql = $conexao->prepare("INSERT INTO usuarios(nome, data_nascimento) VALUES (?,?)");
            $sql->execute([$nome, $data]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e-getMessage()]);
        }
    }

    public static function getById($id) {
        
    }

    public static function exist($id) {
        try {
            $conexao = Conexao::getConexao();
            $sql = $conexao->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetchColumn();
        } catch (Exception $e) {
            output(500, ["msg" => $e-getMessage()]);
        }
    }

    public static function deletar($id) {
        try {
            $conexao = Conexao::getConexao();
            $conexao->beginTransaction();

            $sql = $conexao->prepare("DELETE FROM carros WHERE idUsuario = ?");
            $sql->execute([$id]);

            $sql = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
            $sql->execute([$id]);

            $conexao->commit();
        } catch (Exception $e) {
            $conexao->rollback();
            output(500, ["msg" => $e-getMessage()]);
        }
    }
}
