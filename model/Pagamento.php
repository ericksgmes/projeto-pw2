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
            $sql = $connection->prepare("SELECT * FROM Pagamento");
            $sql->execute();

            return $sql->fetchAll();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function cadastrar(paymentMethodEnum $metodo, $valor) {
        try {
            $connection = Connection::getConnection();
            $data = self::dataAtual();
            $sql = $connection->prepare("INSERT INTO Pagamento(metodo, valor, data) VALUES (?,?,?)");
            $sql->execute([$metodo->value, $valor, $data]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT * FROM Pagamento WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function deleteById($id){
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("DELETE FROM Pagamento WHERE id = ?");
            $sql->execute([$id]);

            return $sql->rowCount();

        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function exist($id) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare("SELECT COUNT(*) FROM Pagamento WHERE id = ?");
            $sql->execute([$id]);

            return $sql->fetchColumn();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

    public static function atualizar($id, paymentMethodEnum $metodo, $valor) {
        try {
            $connection = Connection::getConnection();
            $data = self::dataAtual();
            $sql = $connection->prepare("UPDATE Pagamento SET metodo = ?, valor = ?, data = ? WHERE id = ?");
            $sql->execute([$metodo->value, $valor, $data, $id]);

            return $sql->rowCount();
        } catch (Exception $e) {
            output(500, ["msg" => $e->getMessage()]);
        }
    }

}