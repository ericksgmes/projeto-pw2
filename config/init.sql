CREATE DATABASE IF NOT EXISTS restaurante;
USE restaurante;

CREATE TABLE IF NOT EXISTS Funcionario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    username VARCHAR(255) UNIQUE,
    senha VARCHAR(255)
    );

CREATE TABLE IF NOT EXISTS Mesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL
);

CREATE TABLE IF NOT EXISTS Produto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    preco DECIMAL(10, 2)
    );

CREATE TABLE IF NOT EXISTS Pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metodo ENUM('DEBITO', 'CREDITO', 'PIX') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data DATETIME
    );

CREATE TABLE IF NOT EXISTS Atende (
    id_funcionario INT,
    id_mesa INT,
    PRIMARY KEY (id_funcionario, id_mesa),
    FOREIGN KEY (id_funcionario) REFERENCES Funcionario(id),
    FOREIGN KEY (id_mesa) REFERENCES Mesa(id)
    );

CREATE TABLE IF NOT EXISTS ProdutosMesa (
    id_mesa INT,
    id_prod INT,
    quantidade INT,
    PRIMARY KEY (id_mesa, id_prod),
    FOREIGN KEY (id_mesa) REFERENCES Mesa(id),
    FOREIGN KEY (id_prod) REFERENCES Produto(id)
    );

CREATE TABLE IF NOT EXISTS PagamentoMesa (
    id_mesa INT,
    id_pag INT,
    PRIMARY KEY (id_mesa, id_pag),
    FOREIGN KEY (id_mesa) REFERENCES Mesa(id),
    FOREIGN KEY (id_pag) REFERENCES Pagamento(id)
);