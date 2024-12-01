<?php

require_once __DIR__ . '/../config/database.php';

/**
 * @OA\Schema(
 *     schema="Usuario",
 *     type="object",
 *     title="Usuario",
 *     description="Usuario model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID do usuário"
 *     ),
 *     @OA\Property(
 *         property="nome",
 *         type="string",
 *         description="Nome do usuário"
 *     ),
 *     @OA\Property(
 *         property="username",
 *         type="string",
 *         description="Username do usuário"
 *     ),
 *     @OA\Property(
 *         property="senha",
 *         type="string",
 *         description="Senha do usuário"
 *     ),
 *     @OA\Property(
 *         property="deletado",
 *         type="boolean",
 *         description="Indica se o usuário está deletado"
 *     ),
 *     @OA\Property(
 *         property="data_deletado",
 *         type="string",
 *         format="date-time",
 *         description="Data em que o usuário foi deletado"
 *     ),
 *     @OA\Property(
 *         property="is_admin",
 *         type="boolean",
 *         description="Indica se o usuário é administrador"
 *     )
 * )
 */

class Usuario {

    public static function listar(): array {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Usuario WHERE deletado = 0");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function cadastrar($nome, $username, $senha, $isAdmin = 0) {
        $connection = Connection::getConnection();

        if (self::existsByUsername($username)) {
            throw new Exception("O username já existe. Tente outro.", 409);
        }

        $senhaHashed = password_hash($senha, PASSWORD_BCRYPT);
        $sql = $connection->prepare("INSERT INTO Usuario (nome, username, senha, is_admin) VALUES (?, ?, ?, ?)");
        $sql->execute([$nome, $username, $senhaHashed, $isAdmin]);

        return $connection->lastInsertId();
    }

    public static function getById($id) {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT * FROM Usuario WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            throw new Exception("Usuário não encontrado", 404);
        }

        return $usuario;
    }

    public static function atualizar($id, $nome, $username, $isAdmin) {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Usuário não encontrado", 404);
        }

        if (self::existsByUsername($username)) {
            throw new Exception("O username já está em uso por outro usuário.", 409);
        }

        $sql = $connection->prepare("UPDATE Usuario SET nome = ?, username = ?, is_admin = ? WHERE id = ?");
        $sql->execute([$nome, $username, $isAdmin, $id]);

        return $sql->rowCount();
    }

    public static function deleteById($id): int {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Usuário não encontrado", 404);
        }

        $sql = $connection->prepare("UPDATE Usuario
                    SET deletado = 1, data_deletado = NOW(), username = CONCAT(username, '_deleted_', id)
                    WHERE id = ?;");
        $sql->execute([$id]);

        return $sql->rowCount();
    }

    public static function exist($id): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT COUNT(*) FROM Usuario WHERE id = ? AND deletado = 0");
        $sql->execute([$id]);
        return $sql->fetchColumn() > 0;
    }

    public static function existsByUsername($username): bool {
        $connection = Connection::getConnection();
        $sql = $connection->prepare("SELECT id FROM Usuario WHERE username = ? AND deletado = 0");
        $sql->execute([$username]);
        return (bool) $sql->fetch();
    }

    public static function retornaUsuario($username) {
        try {
            $connection = Connection::getConnection();
            $sql = $connection->prepare(
                "SELECT id, nome, username, senha, is_admin FROM Usuario WHERE username = ? AND deletado = 0"
            );
            $sql->execute([$username]);
            return $sql->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário por username: " . $e->getMessage());
            throw new Exception("Erro ao buscar usuário.", 500);
        }
    }

    public static function atualizarSenha($id, $novaSenha): void
    {
        $connection = Connection::getConnection();

        if (!self::exist($id)) {
            throw new Exception("Usuário não encontrado", 404);
        }

        $senhaHashed = password_hash($novaSenha, PASSWORD_BCRYPT);

        $sql = $connection->prepare("UPDATE Usuario SET senha = ? WHERE id = ? AND deletado = 0");
        $sql->execute([$senhaHashed, $id]);

        if ($sql->rowCount() === 0) {
            throw new Exception("Falha ao atualizar a senha", 500);
        }
    }

    public static function atualizarParcial($id, $campos): void {
        $connection = Connection::getConnection();

        // Verifica se o usuário existe
        if (!self::exist($id)) {
            throw new Exception("Usuário não encontrado", 404);
        }

        // Verifica se os campos não estão vazios
        if (empty($campos) || !is_array($campos)) {
            throw new Exception("Nenhum campo para atualizar foi fornecido", 400);
        }

        // Monta dinamicamente a query SQL e os parâmetros
        $sets = [];
        $params = [];
        foreach ($campos as $campo => $valor) {
            if (in_array($campo, ['nome', 'username', 'senha', 'is_admin'], true)) {
                $sets[] = "$campo = ?";
                $params[] = $campo === 'senha' ? password_hash($valor, PASSWORD_BCRYPT) : $valor;
            } else {
                throw new Exception("Campo inválido: $campo", 400);
            }
        }

        // Adiciona o ID como parâmetro
        $params[] = $id;

        // Monta a query
        $sql = "UPDATE Usuario SET " . implode(", ", $sets) . " WHERE id = ? AND deletado = 0";
        $stmt = $connection->prepare($sql);

        // Executa a query
        if (!$stmt->execute($params)) {
            throw new Exception("Erro ao atualizar o usuário", 500);
        }

        // Verifica se alguma linha foi afetada
        if ($stmt->rowCount() === 0) {
            throw new Exception("Nenhuma alteração foi realizada", 400);
        }
    }
}