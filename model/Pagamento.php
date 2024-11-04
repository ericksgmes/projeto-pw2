<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paymentMethodEnum.php';

class Pagamento {

    private static function dataAtual() {
        $dataAtual = new DateTime();
        $dataAtual->setTimezone(new DateTimeZone("America/Sao_Paulo"));
        return $dataAtual->format('Y-m-d H:i:s');
    }

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Pagamento WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function listarDeletados(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Pagamento WHERE deletado = 1");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function cadastrar(paymentMethodEnum $metodo, $valor, $id_mesa) {
        $connection = Connection::getConnection();
        $data = self::dataAtual();

        // Verifica se a mesa existe e não está deletada
        $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
        $sql->execute([$id_mesa]);
        if (!$sql->fetch()) {
            throw new Exception("Mesa não encontrada ou está deletada.", 404);
        }

        $sql = $connection->prepare("INSERT INTO Pagamento (metodo, valor, data, id_mesa) VALUES (?, ?, ?, ?)");
        $sql->execute([$metodo->value, $valor, $data, $id_mesa]);

        return $connection->lastInsertId();
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Pagamento WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);
        $pagamento = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$pagamento) {
            throw new Exception("Pagamento não encontrado", 404);
        }

        return $pagamento;
    }

    public static function atualizar($id, paymentMethodEnum $metodo, $valor, $id_mesa) {
        $connection = Connection::getConnection();
        $data = self::dataAtual();

        if (!self::exist($id)) {
            throw new Exception("Pagamento não encontrado", 404);
        }

        // Verifica se a mesa existe e não está deletada
        $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
        $sql->execute([$id_mesa]);
        if (!$sql->fetch()) {
            throw new Exception("Mesa não encontrada ou está deletada.", 404);
        }

        $sql = $connection->prepare("UPDATE Pagamento SET metodo = ?, valor = ?, data = ?, id_mesa = ? WHERE id = ? AND deletado = 0");
        $sql->execute([$metodo->value, $valor, $data, $id_mesa, $id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível atualizar o pagamento", 500);
        }
    }

    public static function deleteById($id) {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Pagamento não encontrado", 404);
        }

        $sql = $connection->prepare("UPDATE Pagamento SET deletado = 1, data_deletado = NOW() WHERE id = ?");
        $sql->execute([$id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível deletar o pagamento", 500);
        }
    }

    public static function exist($id): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT COUNT(*) FROM Pagamento WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);

        return $sql->fetchColumn() > 0;
    }
}
