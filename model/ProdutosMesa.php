<?php

require_once __DIR__ . '/../config/database.php';

/**
 * @OA\Schema(
 *     schema="ProdutosMesa",
 *     type="object",
 *     title="ProdutosMesa",
 *     description="Modelo de produtos associados a uma mesa",
 *     @OA\Property(property="id", type="integer", description="ID da associação produto-mesa"),
 *     @OA\Property(property="numero_mesa", type="string", description="Número da mesa"),
 *     @OA\Property(property="id_prod", type="integer", description="ID do produto"),
 *     @OA\Property(property="quantidade", type="integer", description="Quantidade do produto")
 * )
 */
class ProdutosMesa {

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*, m.numero AS numero_mesa, p.nome AS nome_produto, p.preco AS preco_produto 
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE m.deletado = 0 AND p.deletado = 0
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarDeletadas(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*, m.numero AS numero_mesa, p.nome AS nome_produto
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE m.deletado = 1 OR p.deletado = 1
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function adicionar($numero_mesa, $produtos) {
        $connection = Connection::getConnection();

        try {
            // Verifica se a mesa existe
            $sql = $connection->prepare("SELECT numero FROM Mesa WHERE numero = ? AND deletado = 0");
            $sql->execute([$numero_mesa]);
            if (!$sql->fetch()) {
                error_log("Erro: Mesa com número {$numero_mesa} não encontrada ou está deletada.");
                throw new Exception("Mesa não encontrada ou está deletada.", 404);
            }

            // Processa cada produto na lista de produtos
            foreach ($produtos as $produto) {
                $id_prod = $produto['id_prod'];
                $quantidade = $produto['quantidade'];

                // Verifica se o produto existe
                $sql = $connection->prepare("SELECT id FROM Produto WHERE id = ? AND deletado = 0");
                $sql->execute([$id_prod]);
                if (!$sql->fetch()) {
                    error_log("Erro: Produto com ID {$id_prod} não encontrado ou está deletado.");
                    throw new Exception("Produto com ID {$id_prod} não encontrado ou está deletado.", 404);
                }

                // Verifica se a quantidade é válida
                if ($quantidade <= 0) {
                    error_log("Erro: A quantidade do produto {$id_prod} deve ser maior que zero.");
                    throw new Exception("A quantidade do produto {$id_prod} deve ser maior que zero.", 400);
                }

                // Adiciona o produto à mesa
                $sql = $connection->prepare("INSERT INTO ProdutosMesa (numero_mesa, id_prod, quantidade) VALUES (?, ?, ?)");
                $sql->execute([$numero_mesa, $id_prod, $quantidade]);

                // Se ocorrer falha ao adicionar o produto, logar o erro
                if ($sql->rowCount() === 0) {
                    error_log("Erro: Falha ao adicionar produto ID {$id_prod} à mesa {$numero_mesa}.");
                    throw new Exception("Não foi possível adicionar o produto à mesa.", 500);
                }
            }

            // Se tudo correr bem, logar o sucesso
            error_log("Sucesso: Produtos adicionados à mesa {$numero_mesa}. Produtos: " . json_encode($produtos));

            return ["status" => "success", "message" => "Produtos adicionados à mesa com sucesso"];

        } catch (Exception $e) {
            // Registra o erro completo
            error_log("Erro na função adicionar: " . $e->getMessage());
            throw $e; // Rethrow the exception to handle it further upstream if needed
        }
    }

    public static function removerProduto($id) {
        $connection = Connection::getConnection();

        // Verifica se o produto existe
        $sql = $connection->prepare("SELECT id FROM ProdutosMesa WHERE id = ?");
        $sql->execute([$id]);
        if (!$sql->fetch()) {
            throw new Exception("Produto não encontrado na mesa.", 404);
        }

        // Realiza a remoção
        $sql = $connection->prepare("DELETE FROM ProdutosMesa WHERE id = ?");
        $sql->execute([$id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível remover o produto da mesa", 500);
        }
    }


    public static function atualizarQuantidade($id, $quantidade) {
        $connection = Connection::getConnection();

        // Verifica se o produto existe
        $sql = $connection->prepare("SELECT id FROM ProdutosMesa WHERE id = ?");
        $sql->execute([$id]);
        if (!$sql->fetch()) {
            throw new Exception("Produto não encontrado na mesa.", 404);
        }

        // Verifica se a quantidade é válida
        if ($quantidade <= 0) {
            throw new Exception("A quantidade deve ser maior que zero.", 400);
        }

        // Atualiza a quantidade do produto
        $sql = $connection->prepare("UPDATE ProdutosMesa SET quantidade = ? WHERE id = ?");
        $sql->execute([$quantidade, $id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Não foi possível atualizar a quantidade", 500);
        }
    }

    public static function getByMesaNumero($numero_mesa): array {
        $connection = Connection::getConnection();

        // Verifica se a mesa existe e não está deletada
        $sql = $connection->prepare("SELECT numero FROM Mesa WHERE numero = ? AND deletado = 0");
        $sql->execute([$numero_mesa]);
        if (!$sql->fetch()) {
            throw new Exception("Mesa não encontrada ou está deletada.", 404);
        }

        $sql = $connection->prepare("
            SELECT pm.*, p.nome AS nome_produto, p.preco AS preco_produto 
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE pm.numero_mesa = ? AND m.deletado = 0 AND p.deletado = 0
        ");
        $sql->execute([$numero_mesa]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("
            SELECT pm.*, m.numero AS numero_mesa, p.nome AS nome_produto, p.preco AS preco_produto
            FROM ProdutosMesa pm
            INNER JOIN Mesa m ON pm.numero_mesa = m.numero
            INNER JOIN Produto p ON pm.id_prod = p.id
            WHERE pm.id = ? AND m.deletado = 0 AND p.deletado = 0
        ");
        $sql->execute([$id]);
        $produtoMesa = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$produtoMesa) {
            throw new Exception("Produto na mesa não encontrado", 404);
        }

        return $produtoMesa;
    }
}