<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");

class Mesa
{
    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Mesa WHERE deletado = 0");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function criar($numero) {
        try {
            $connection = Connection::getConnection();

            // Verifica se o número é positivo
            if ($numero <= 0) {
                throw new Exception("O número da mesa deve ser maior que zero.");
            }

            // Verifica se o número já existe entre as mesas ativas
            if (self::existsByNumber($numero)) {
                throw new Exception("Número da mesa já está em uso.");
            }

            $sql = $connection->prepare("INSERT INTO Mesa(numero) VALUE (?)");
            $sql->execute([$numero]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Mesa WHERE id = ? AND deletado = 0");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function deleteById($id){
        try {
            $connection = Connection::getConnection();

            // Obtém o número atual da mesa
            $sql = $connection->prepare("SELECT numero FROM Mesa WHERE id = ?");
            $sql->execute([$id]);
            $numero = $sql->fetchColumn();

            if ($numero === false) {
                throw new Exception("Mesa não encontrada.");
            }

            // Modifica o número para evitar conflitos (transforma em negativo)
            $novoNumero = -abs($numero);

            // Atualiza o registro para marcar como deletado
            $sql = $connection->prepare("UPDATE Mesa SET deletado = 1, data_deletado = NOW(), numero = ? WHERE id = ?");
            $sql->execute([$novoNumero, $id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Mesa WHERE id = ? AND deletado = 0");
            $sql->execute([$id]);

            return $sql->fetchColumn() > 0;
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function existsByNumber($numero) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE numero = ? AND deletado = 0");
            $sql->execute([$numero]);
            return (bool)$sql->fetch();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function atualizar($id, $numero) {
        try {
            $connection = Connection::getConnection();

            // Verifica se o número já está em uso por outra mesa ativa
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE numero = ? AND deletado = 0 AND id != ?");
            $sql->execute([$numero, $id]);
            if ($sql->fetch()) {
                throw new Exception("Número da mesa já está em uso.");
            }

            $sql = $connection->prepare("UPDATE Mesa SET numero = ? WHERE id = ? AND deletado = 0");
            $sql->execute([$numero, $id]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function restoreById($id){
        try {
            $connection = Connection::getConnection();

            // Obtém o número atual da mesa (negativo)
            $sql = $connection->prepare("SELECT numero FROM Mesa WHERE id = ?");
            $sql->execute([$id]);
            $numero = $sql->fetchColumn();

            if ($numero === false) {
                throw new Exception("Mesa não encontrada.");
            }

            // Converte o número para positivo
            $numeroOriginal = abs($numero);

            // Verifica se o número original já está em uso por outra mesa ativa
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE numero = ? AND deletado = 0");
            $sql->execute([$numeroOriginal]);
            if ($sql->fetch()) {
                throw new Exception("Número da mesa já está em uso.");
            }

            // Atualiza o registro para restaurar a mesa
            $sql = $connection->prepare("UPDATE Mesa SET deletado = 0, data_deletado = NULL, numero = ? WHERE id = ?");
            $sql->execute([$numeroOriginal, $id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }
}
