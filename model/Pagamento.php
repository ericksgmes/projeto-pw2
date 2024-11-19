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
 *     @OA\Property(property="id_mesa", type="integer", description="ID da mesa associada ao pagamento"),
 *     @OA\Property(property="deletado", type="boolean", description="Indica se o pagamento está deletado"),
 *     @OA\Property(property="data_deletado", type="string", format="date-time", description="Data em que o pagamento foi deletado")
 * )
 */
class Pagamento {

    /**
     * @OA\Get(
     *     path="/pagamentos",
     *     summary="Listar todos os pagamentos",
     *     @OA\Response(response="200", description="Lista de pagamentos")
     * )
     */
    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Pagamento WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Get(
     *     path="/pagamentos/deletados",
     *     summary="Listar todos os pagamentos deletados",
     *     @OA\Response(response="200", description="Lista de pagamentos deletados")
     * )
     */
    public static function listarDeletados(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Pagamento WHERE deletado = 1");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @OA\Post(
     *     path="/pagamentos",
     *     summary="Cadastrar um novo pagamento",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"metodo", "valor", "id_mesa"},
     *             @OA\Property(property="metodo", type="string", description="Método de pagamento"),
     *             @OA\Property(property="valor", type="number", format="float", description="Valor do pagamento"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa associada ao pagamento")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Pagamento cadastrado com sucesso"),
     *     @OA\Response(response="404", description="Mesa não encontrada ou deletada")
     * )
     */
    public static function cadastrar(paymentMethodEnum $metodo, $valor, $id_mesa) {
        error_log("Iniciando cadastro de pagamento.");
        error_log("Dados recebidos: Método - {$metodo->value}, Valor - $valor, ID Mesa - $id_mesa");

        $connection = Connection::getConnection();
        $data = self::dataAtual();
        error_log("Data atual gerada: $data");

        try {
            // Verificar se a mesa existe e não está deletada
            error_log("Verificando existência da mesa com ID: $id_mesa");
            $sql = $connection->prepare("SELECT id FROM Mesa WHERE id = ? AND deletado = 0");
            $sql->execute([$id_mesa]);

            $mesa = $sql->fetch();
            if (!$mesa) {
                error_log("Mesa não encontrada ou está deletada. ID Mesa: $id_mesa");
                throw new Exception("Mesa não encontrada ou está deletada.", 404);
            }
            error_log("Mesa encontrada: " . json_encode($mesa));

            // Inserir o pagamento
            error_log("Inserindo pagamento no banco.");
            $sql = $connection->prepare("INSERT INTO Pagamento (metodo, valor, data, id_mesa) VALUES (?, ?, ?, ?)");
            $sql->execute([$metodo->value, $valor, $data, $id_mesa]);

            $insertedId = $connection->lastInsertId();
            error_log("Pagamento inserido com sucesso. ID: $insertedId");

            return $insertedId;
        } catch (PDOException $e) {
            error_log("Erro no banco ao cadastrar pagamento: " . $e->getMessage());
            throw new Exception("Erro ao cadastrar pagamento: " . $e->getMessage(), 500);
        } catch (Exception $e) {
            error_log("Erro ao cadastrar pagamento: " . $e->getMessage());
            throw $e; // Repassar exceções personalizadas
        }
    }


    /**
     * @OA\Get(
     *     path="/pagamentos/{id}",
     *     summary="Obter detalhes de um pagamento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Detalhes do pagamento"),
     *     @OA\Response(response="404", description="Pagamento não encontrado")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/pagamentos/{id}",
     *     summary="Atualizar um pagamento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"metodo", "valor", "id_mesa"},
     *             @OA\Property(property="metodo", type="string", description="Método de pagamento"),
     *             @OA\Property(property="valor", type="number", format="float", description="Valor do pagamento"),
     *             @OA\Property(property="id_mesa", type="integer", description="ID da mesa associada ao pagamento")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Pagamento atualizado com sucesso"),
     *     @OA\Response(response="404", description="Pagamento ou mesa não encontrado")
     * )
     */
    public static function atualizar($id, paymentMethodEnum $metodo, $valor, $id_mesa) {
        $connection = Connection::getConnection();
        $data = self::dataAtual();

        if (!self::exist($id)) {
            throw new Exception("Pagamento não encontrado", 404);
        }

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

    /**
     * @OA\Delete(
     *     path="/pagamentos/{id}",
     *     summary="Deletar um pagamento",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Pagamento deletado com sucesso"),
     *     @OA\Response(response="404", description="Pagamento não encontrado")
     * )
     */
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

    private static function dataAtual(): string
    {
        return date('Y-m-d H:i:s');
    }
}
