<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/paymentMethodEnum.php';

/**
 * @OA\Schema(
 *     schema="Pagamento",
 *     type="object",
 *     title="Pagamento",
 *     description="Modelo de pagamento",
 *     @OA\Property(property="id", type="integer", description="ID do pagamento"),
 *     @OA\Property(property="metodo", type="string", description="Método de pagamento"),
 *     @OA\Property(property="valor", type="number", format="float", description="Valor do pagamento"),
 *     @OA\Property(property="data", type="string", format="date-time", description="Data do pagamento"),
 *     @OA\Property(property="numero_mesa", type="integer", description="Número da mesa associada ao pagamento"),
 *     @OA\Property(property="deletado", type="boolean", description="Indica se o pagamento está deletado"),
 *     @OA\Property(property="data_deletado", type="string", format="date-time", description="Data em que o pagamento foi deletado")
 * )
 */
class Pagamento {

    public static function listar(): array {
        try {
            $connection = Connection::getConnection();
            error_log("Listando todos os pagamentos ativos.");
            $sql = $connection->prepare("
                SELECT p.*, m.numero AS numero_mesa 
                FROM Pagamento p
                INNER JOIN Mesa m ON p.numero_mesa = m.numero
                WHERE p.deletado = 0
            ");

            $sql->execute();
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar pagamentos: " . $e->getMessage());
            throw $e;
        }
    }

    public static function listarDeletados(): array {
        try {
            $connection = Connection::getConnection();
            error_log("Listando todos os pagamentos deletados.");
            $sql = $connection->prepare("
            SELECT p.*, m.numero AS numero_mesa 
            FROM Pagamento p
            INNER JOIN Mesa m ON p.numero_mesa = m.numero
            WHERE p.deletado = 1
        ");
            $sql->execute();
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar pagamentos deletados: " . $e->getMessage());
            throw $e;
        }
    }


    public static function cadastrar(paymentMethodEnum $metodo, $valor, $numero_mesa) {
        try {
            $connection = Connection::getConnection();
            $data = self::dataAtual();
            error_log("Iniciando cadastro de pagamento. Método: {$metodo->value}, Valor: $valor, Número Mesa: $numero_mesa");

            // Verificar se a mesa existe e está ativa (não deletada)
            $sql = $connection->prepare("SELECT numero FROM Mesa WHERE numero = ? AND deletado = 0");
            $sql->execute([$numero_mesa]);
            $mesa = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$mesa) {
                error_log("Mesa com número $numero_mesa não encontrada ou está deletada.");
                throw new Exception("Mesa com número {$numero_mesa} não encontrada ou está deletada.", 404);
            }

            // Inserir o pagamento com o número da mesa
            $sql = $connection->prepare("
            INSERT INTO Pagamento (metodo, valor, data, numero_mesa) 
            VALUES (?, ?, ?, ?)
        ");
            $sql->execute([$metodo->value, $valor, $data, $numero_mesa]);

            $lastId = $connection->lastInsertId();
            error_log("Pagamento cadastrado com sucesso. ID: $lastId");
            return $lastId;
        } catch (Exception $e) {
            error_log("Erro ao cadastrar pagamento: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getById($id) {
        try {
            $connection = Connection::getConnection();
            error_log("Buscando pagamento pelo ID: $id");

            // Ajustar a consulta para usar numero_mesa
            $sql = $connection->prepare("
            SELECT p.id, p.metodo, p.valor, p.data, p.numero_mesa, p.deletado, p.data_deletado 
            FROM Pagamento p
            WHERE p.id = ? AND p.deletado = 0
        ");
            $sql->execute([$id]);
            $pagamento = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$pagamento) {
                error_log("Pagamento com ID $id não encontrado.");
                throw new Exception("Pagamento não encontrado", 404);
            }

            error_log("Pagamento encontrado: " . json_encode($pagamento));
            return $pagamento;
        } catch (Exception $e) {
            error_log("Erro ao buscar pagamento: " . $e->getMessage());
            throw $e;
        }
    }

    public static function atualizar($id, paymentMethodEnum $metodo, $valor, $numero_mesa) {
        try {
            $connection = Connection::getConnection();
            $data = self::dataAtual();
            error_log("Iniciando atualização de pagamento. ID: $id, Método: {$metodo->value}, Valor: $valor, Número Mesa: $numero_mesa");

            // Verificar se o pagamento existe
            if (!self::exist($id)) {
                error_log("Pagamento com ID $id não encontrado.");
                throw new Exception("Pagamento não encontrado", 404);
            }

            // Atualizar o pagamento diretamente com o número da mesa
            $sql = $connection->prepare("
            UPDATE Pagamento 
            SET metodo = ?, valor = ?, data = ?, numero_mesa = ? 
            WHERE id = ? AND deletado = 0
        ");
            $sql->execute([$metodo->value, $valor, $data, $numero_mesa, $id]);

            if ($sql->rowCount() === 0) {
                error_log("Nenhuma linha foi atualizada para o pagamento com ID $id.");
                throw new Exception("Não foi possível atualizar o pagamento", 500);
            }

            error_log("Pagamento atualizado com sucesso. ID: $id");
        } catch (Exception $e) {
            error_log("Erro ao atualizar pagamento: " . $e->getMessage());
            throw $e;
        }
    }


    public static function deleteById($id) {
        try {
            $connection = Connection::getConnection();
            error_log("Iniciando exclusão de pagamento com ID: $id");

            if (!self::exist($id)) {
                error_log("Pagamento com ID $id não encontrado.");
                throw new Exception("Pagamento não encontrado", 404);
            }

            $sql = $connection->prepare("
                UPDATE Pagamento 
                SET deletado = 1, data_deletado = NOW() 
                WHERE id = ?
            ");
            $sql->execute([$id]);

            if ($sql->rowCount() === 0) {
                error_log("Não foi possível deletar o pagamento com ID $id.");
                throw new Exception("Não foi possível deletar o pagamento", 500);
            }

            error_log("Pagamento deletado com sucesso. ID: $id");
        } catch (Exception $e) {
            error_log("Erro ao deletar pagamento: " . $e->getMessage());
            throw $e;
        }
    }

    public static function exist($id): bool {
        try {
            $connection = Connection::getConnection();
            error_log("Verificando existência do pagamento com ID: $id");
            $sql = $connection->prepare("
                SELECT COUNT(*) 
                FROM Pagamento 
                WHERE id = ? AND deletado = 0
            ");
            $sql->execute([$id]);

            return $sql->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar existência do pagamento: " . $e->getMessage());
            throw $e;
        }
    }

    private static function dataAtual(): string {
        return date('Y-m-d H:i:s');
    }
}
