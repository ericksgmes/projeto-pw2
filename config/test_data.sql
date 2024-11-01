USE
restaurante;

-- Inserindo funcionários
INSERT INTO Funcionario (nome, username, senha)
VALUES ('João Silva', 'joao', 'senha123'),
       ('Maria Oliveira', 'maria', 'senha456'),
       ('Carlos Souza', 'carlos', 'senha789');

-- Inserindo mesas
INSERT INTO Mesa (numero)
VALUES (1),
       (2),
       (3),
       (4),
       (5);

-- Inserindo produtos
INSERT INTO Produto (nome, preco)
VALUES ('Pizza Margherita', 25.00),
       ('Spaghetti Carbonara', 20.00),
       ('Salada Caesar', 15.00),
       ('Refrigerante', 5.00),
       ('Sobremesa', 10.00);

-- Associando funcionários às mesas
INSERT INTO FuncionarioMesa (id_funcionario, id_mesa)
VALUES (1, 1),
       (2, 2),
       (3, 3);

-- Inserindo produtos pedidos em cada mesa
INSERT INTO ProdutosMesa (id_mesa, id_prod, quantidade)
VALUES (1, 1, 2), -- Mesa 1 pediu 2 Pizzas Margherita
       (1, 4, 2), -- Mesa 1 pediu 2 Refrigerantes
       (2, 2, 1), -- Mesa 2 pediu 1 Spaghetti Carbonara
       (2, 3, 1), -- Mesa 2 pediu 1 Salada Caesar
       (3, 5, 3); -- Mesa 3 pediu 3 Sobremesas

-- Inserindo pagamentos
INSERT INTO Pagamento (metodo, valor, data, id_mesa)
VALUES ('CREDITO', 60.00, NOW(), 1),
       ('DEBITO', 35.00, NOW(), 2),
       ('PIX', 30.00, NOW(), 3);