<?php

require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/utils.php");
require_once(__DIR__ . "/../config/paymentMethodEnum.php");

class Pagamento
{
    private static function dataAtual() {
        $dataAtual = new DateTime();
        $dataAtual->setTimezone(new DateTimeZone("America/Sao_Paulo"));
        return $dataAtual->format('Y-m-d H:i:s');
    }

    public static function listar() {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Pagamento WHERE deletado = 0");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function cadastrar(paymentMethodEnum $metodo, $valor, $id_mesa) {
        try {
            $connection = Connection::getConnection();
            $data = self::dataAtual();

            // Verifica se a mesa existe e não está deletada
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
            $sql->execute([$id_mesa]);
            if (!$sql->fetch()) {
                throw new Exception("Mesa não encontrada ou está deletada.");
            }

            $sql = $connection->prepare("INSERT INTO Pagamento(metodo, valor, data, id_mesa) VALUES (?,?,?,?)");
            $sql->execute([$metodo->value, $valor, $data, $id_mesa]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Pagamento WHERE id = ? AND deletado = 0");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function deleteById($id){
        try {
            $connection = Connection::getConnection();

            // Marca o pagamento como deletado
            $sql = $connection->prepare("UPDATE Pagamento SET deletado = 1, data_deletado = NOW() WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Pagamento WHERE id = ? AND deletado = 0");
            $sql->execute([$id]);

            return $sql->fetchColumn() > 0;
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function atualizar($id, paymentMethodEnum $metodo, $valor, $id_mesa) {
        try {
            $connection = Connection::getConnection();
            $data = self::dataAtual();

            // Verifica se o pagamento não está deletado
            if (!self::exist($id)) {
                throw new Exception("Pagamento não encontrado ou está deletado.");
            }

            // Verifica se a mesa existe e não está deletada
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
            $sql->execute([$id_mesa]);
            if (!$sql->fetch()) {
                throw new Exception("Mesa não encontrada ou está deletada.");
            }

            $sql = $connection->prepare("UPDATE Pagamento SET metodo = ?, valor = ?, data = ?, id_mesa = ? WHERE id = ? AND deletado = 0");
            $sql->execute([$metodo->value, $valor, $data, $id_mesa, $id]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function restoreById($id){
        try {
            $connection = Connection::getConnection();

            // Verifica se o pagamento existe e está deletado
            $sql = $connection->prepare("SELECT id FROM Pagamento WHERE id = ? AND deletado = 1");
            $sql->execute([$id]);
            if (!$sql->fetch()) {
                throw new Exception("Pagamento não encontrado ou não está deletado.");
            }

            // Restaura o pagamento
            $sql = $connection->prepare("UPDATE Pagamento SET deletado = 0, data_deletado = NULL WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }
}
