USE
restaurante;

-- Inserindo usuários
INSERT INTO Usuario (nome, username, senha, is_admin)
VALUES ('João Silva', 'joao', '$2y$10$tWiIWH3fEyXGMv3Oxc2a8eU0HZtHmHH2KYS4D4e7UGVco79BsP8Qe', 1), -- Admin
       ('Maria Oliveira', 'maria', '$2y$10$5OmUO40M28czift68aff0evFgk.7n8.XD3XW0zw67q57GQjq7StYS', 0),
       ('Carlos Souza', 'carlos', '$2y$10$5JkLx1JIYxMhkLDtjDTUGu3.S5LOLs/AFCLLvZhlIqMOtU88Fa9A2', 0);

-- Inserindo mesas
INSERT INTO Mesa (numero)
VALUES ('1'),
       ('2'),
       ('3'),
       ('4'),
       ('5');

-- Inserindo produtos
INSERT INTO Produto (nome, preco)
VALUES ('Bruschetta', 25.00),
       ('Tábua de Queijos', 45.00),
       ('Salada Caprese', 30.00),
       ('Carbonara', 50.00),
       ('Filé Mignon ao Molho Madeira', 75.00),
       ('Risoto de Cogumelos', 60.00),
       ('Tiramisù', 20.00),
       ('Petit Gâteau', 22.00),
       ('Cheesecake', 18.00),
       ('Vinho Tinto', 90.00),
       ('Suco de Laranja', 12.00),
       ('Água com Gás', 8.00);

-- Associando usuários às mesas
INSERT INTO UsuarioMesa (id_usuario, numero_mesa)
VALUES (1, '1'), -- João Silva associado à Mesa 1
       (2, '2'), -- Maria Oliveira associada à Mesa 2
       (3, '3');
-- Carlos Souza associado à Mesa 3

-- Inserindo produtos pedidos em cada mesa
INSERT INTO ProdutosMesa (numero_mesa, id_prod, quantidade)
VALUES ('1', 1, 2),  -- Mesa 1 pediu 2 Bruschettas
       ('1', 10, 1), -- Mesa 1 pediu 1 Vinho Tinto
       ('2', 4, 1),  -- Mesa 2 pediu 1 Carbonara
       ('2', 3, 2),  -- Mesa 2 pediu 2 Saladas Caprese
       ('3', 7, 3);
-- Mesa 3 pediu 3 Tiramisù

-- Inserindo pagamentos
INSERT INTO Pagamento (metodo, valor, data, numero_mesa)
VALUES ('CREDITO', 60.00, NOW(), '1'),
       ('DEBITO', 85.00, NOW(), '2'),
       ('PIX', 60.00, NOW(), '3');
