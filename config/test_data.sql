-- Populating Funcionario table
INSERT INTO Funcionario (id, nome, username, senha)
VALUES (1, 'Carlos Silva', 'carlos.s', 'senha123'),
       (2, 'Maria Santos', 'maria.s', 'senha456'),
       (3, 'João Pereira', 'joao.p', 'senha789');

-- Populating Mesa table
INSERT INTO Mesa (id, numero)
VALUES (1, 1),
       (2, 2),
       (3, 3),
       (4, 4),
       (5, 5);

-- Populating Produto table
INSERT INTO Produto (id, nome, preco)
VALUES (1, 'Pizza Margherita', 25.00),
       (2, 'Espaguete à Bolonhesa', 18.50),
       (3, 'Salada Caesar', 15.00),
       (4, 'Coca-Cola', 5.00),
       (5, 'Água Mineral', 3.00),
       (6, 'Tiramisu', 10.00);

-- Populating Pagamento table
INSERT INTO Pagamento (id, metodo, valor, data)
VALUES (1, 'DEBITO', 53.00, '2023-10-10 12:30:00'),
       (2, 'CREDITO', 28.50, '2023-10-10 13:00:00'),
       (3, 'PIX', 25.00, '2023-10-10 14:00:00'),
       (4, 'CREDITO', 85.00, '2023-10-10 14:30:00'),
       (5, 'DEBITO', 6.00, '2023-10-10 15:00:00');

-- Populating Atende table
INSERT INTO Atende (id_funcionario, id_mesa)
VALUES (1, 1),
       (1, 2),
       (2, 3),
       (3, 4),
       (3, 5);

-- Populating ProdutosMesa table
INSERT INTO ProdutosMesa (id_mesa, id_prod, quantidade)
VALUES (1, 1, 2),
       (1, 5, 1),
       (2, 2, 1),
       (2, 4, 2),
       (3, 3, 1),
       (3, 6, 1),
       (4, 1, 3),
       (4, 4, 2),
       (5, 5, 2);

-- Populating PagamentoMesa table
INSERT INTO PagamentoMesa (id_mesa, id_pag)
VALUES (1, 1),
       (2, 2),
       (3, 3),
       (4, 4),
       (5, 5);
