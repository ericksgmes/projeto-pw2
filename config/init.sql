CREATE DATABASE IF NOT EXISTS restaurante;
USE restaurante;

CREATE TABLE IF NOT EXISTS Usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    deletado TINYINT(1) DEFAULT 0,
    data_deletado DATETIME NULL,
    is_admin TINYINT(1) DEFAULT 0
    );

CREATE TABLE IF NOT EXISTS Mesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(255) NOT NULL UNIQUE,
    deletado TINYINT(1) DEFAULT 0,
    data_deletado DATETIME NULL
);

CREATE TABLE IF NOT EXISTS Produto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    preco DECIMAL(10, 2),
    deletado TINYINT(1) DEFAULT 0,
    data_deletado DATETIME NULL
    );

CREATE TABLE IF NOT EXISTS Pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metodo ENUM('DEBITO', 'CREDITO', 'PIX') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data DATETIME,
    numero_mesa INT NOT NULL,
    deletado TINYINT(1) DEFAULT 0,
    data_deletado DATETIME NULL,
    FOREIGN KEY (numero_mesa) REFERENCES Mesa(numero)
    );


CREATE TABLE IF NOT EXISTS UsuarioMesa (
    id_usuario INT,
    id_mesa INT,
    PRIMARY KEY (id_usuario, id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id),
    FOREIGN KEY (id_mesa) REFERENCES Mesa(id)
    );

CREATE TABLE IF NOT EXISTS ProdutosMesa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT,
    id_prod INT,
    quantidade INT,
    FOREIGN KEY (id_mesa) REFERENCES Mesa(id),
    FOREIGN KEY (id_prod) REFERENCES Produto(id)
    );