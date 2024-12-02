
---

# Restaurante Webservice - PW2 Discipline Project

Este repositório contém a aplicação web desenvolvida para a disciplina **Programação para Web 2 (PW2)**. Ele fornece uma solução completa para gerenciar as funcionalidades de um restaurante, incluindo gerenciamento de funcionários, pedidos, mesas e pagamentos, com um frontend totalmente integrado ao backend.

## 📋 Índice

- [Início Rápido](#início-rápido)
   - [Pré-requisitos](#pré-requisitos)
   - [Como Rodar](#como-rodar)
- [📖 Documentação Swagger](#-documentação-swagger)
- [🚀 Funcionalidades Disponíveis](#-funcionalidades-disponíveis)
- [📁 Estrutura de Diretórios](#-estrutura-de-diretórios)
- [📝 Notas](#-notas)
- [🔮 Melhorias Futuras](#-melhorias-futuras)
- [👥 Autores](#-autores)
- [📄 Licença](#-licença)
- [🔗 Recursos Adicionais](#-recursos-adicionais)

---

## Início Rápido

Siga estas instruções para configurar e rodar o projeto utilizando **XAMPP** ou outro servidor compatível com PHP.

### Pré-requisitos

- **XAMPP** (para servidores Apache e MySQL)
- **Composer** (para gerenciar dependências PHP)
- **Postman** *(opcional, para testar a API)*

### Como Rodar

1. **Clone o Repositório:**

   ```bash
   git clone https://github.com/ericksgmes/restaurante-webservice
   ```

2. **Instale as Dependências:**

   Navegue até o diretório do projeto e execute:

   ```bash
   composer install
   ```

3. **Configure o Servidor:**

   - Certifique-se de que o **Apache** e o **MySQL** estão ativos no **XAMPP**.

4. **Configure o Banco de Dados:**

   - Acesse o **phpMyAdmin** pelo navegador em `http://localhost/phpmyadmin`.
   - Crie um banco de dados chamado **`restaurante`**.
   - Importe o arquivo **`init.sql`** localizado no repositório para configurar o esquema inicial e os dados.
   - Também o arquivo **`test_data.sql`** para inicializar com dados de teste. Os dois arquivos sql se encontram na pasta /config

5. **Configure o Arquivo `.env`:**

   Crie um arquivo `.env` na raiz do projeto com as configurações do banco de dados. Exemplo:

   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=restaurante
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Inicie o Servidor:**

   - Navegue para o diretório do projeto e acesse o frontend:

     ```
     http://localhost/restaurante-webservice/frontend
     ```

   - O aplicativo agora estará disponível para uso.

7. **Teste os Endpoints (Opcional):**

   - Use **Postman** ou o Swagger UI para testar os endpoints da API.
   - Navegue para:

     ```
     http://localhost/restaurante-webservice/swagger.html
     ```

---

## 📖 Documentação Swagger

O projeto inclui a interface Swagger UI para visualização e teste da API. Para acessá-la:

1. Certifique-se de que o servidor **Apache** está rodando.
2. Acesse:

   ```
   http://localhost/restaurante-webservice/swagger.html
   ```

3. Explore os endpoints disponíveis e faça testes diretamente pelo navegador.

---

## 🚀 Funcionalidades Disponíveis

O aplicativo fornece funcionalidades para gerenciar vários aspectos do restaurante, como:

- **Funcionários**: Adicionar, atualizar, deletar e listar informações de funcionários.
- **Mesas**: Gerenciar status e associações de mesas.
- **Pedidos**: Criar e atualizar pedidos de clientes.
- **Pagamentos**: Processar pagamentos de pedidos concluídos.

---

## 📁 Estrutura de Diretórios

```
├── config/            # Arquivos de configuração (conexão ao banco de dados, utilitários)
├── controller/        # Controladores que lidam com requisições
├── frontend/          # Arquivos de frontend (integrados com o backend)
├── model/             # Modelos do banco de dados para as entidades
├── .gitignore         # Regras do Git para ignorar arquivos
├── .htaccess          # Configuração do Apache
├── LICENSE            # Licença do projeto
├── README.md          # Documentação do projeto
├── composer.json      # Arquivo de dependências PHP
├── index.php          # Ponto de entrada do webservice
└── swagger.html       # Interface do Swagger para visualizar os endpoints da API
```

---

## 📝 Notas

- **Integração Frontend-Backend**: O frontend está totalmente integrado ao backend. Para acessá-lo, navegue para `http://localhost/restaurante-webservice/frontend`.
- **Testes de API**: Você ainda pode usar ferramentas como **Postman** ou a interface Swagger para interagir com a API REST.

---

## 🔮 Melhorias Futuras

- **Autenticação**: Implementar autenticação para proteger os endpoints da API.
- **Relatórios**: Adicionar funcionalidades para gerar relatórios sobre vendas e pedidos.
- **Interface Responsiva**: Melhorar a responsividade do frontend.

---

## 👥 Autores

Desenvolvido com ❤️ por:

- [@ericksgmes](https://github.com/ericksgmes)
- [@leoh3nrique](https://github.com/leoh3nrique)

---

## 📄 Licença

Este projeto é licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 🔗 Recursos Adicionais

- **Repositório no GitHub**: [restaurante-webservice](https://github.com/ericksgmes/restaurante-webservice)
- **XAMPP**: [Baixar XAMPP](https://www.apachefriends.org/index.html)
- **Composer**: [Baixar Composer](https://getcomposer.org/download/)
- **Postman** *(opcional)*: [Baixar Postman](https://www.postman.com/downloads/)

---
