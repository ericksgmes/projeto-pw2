document.addEventListener("DOMContentLoaded", function () {
  const baseUrl = "http://localhost/restaurante-webservice";

  // Navegação entre páginas
  document.querySelectorAll("header nav ul li a").forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const sectionId = link.getAttribute("href").substring(1);
      mostrarSecao(sectionId);
    });
  });

  function mostrarSecao(sectionId) {
    document.querySelectorAll(".section").forEach(section => {
      section.classList.remove("active");
    });
    document.getElementById(sectionId).classList.add("active");
  }

  // Login
  document.getElementById("form-login").addEventListener("submit", async function (e) {
    e.preventDefault();

    const username = document.getElementById("username-login").value.trim();
    const senha = document.getElementById("senha-login").value.trim();

    try {
      // Simulando autenticação para simplificar
      if (username === "admin" && senha === "admin") {
        alert("Login bem-sucedido!");
        document.getElementById("login").style.display = "none";
        mostrarSecao("funcionarios");
      } else {
        alert("Credenciais inválidas!");
      }
    } catch (error) {
      console.error("Erro ao tentar fazer login:", error.message);
    }
  });

  // Funcionários - CRUD
  document.getElementById("form-funcionario").addEventListener("submit", async function (e) {
    e.preventDefault();

    const nome = document.getElementById("nome-funcionario").value.trim();
    const username = document.getElementById("username-funcionario").value.trim();
    const senha = document.getElementById("senha-funcionario").value.trim();

    try {
      const response = await fetch(`${baseUrl}/funcionarios`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          nome,
          username,
          senha,
        }),
      });

      if (response.status === 201) {
        alert("Funcionário criado com sucesso!");
        listarFuncionarios();
      } else {
        const errorData = await response.json();
        alert(`Erro ao criar funcionário: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao criar funcionário:", error.message);
    }
  });

  async function listarFuncionarios() {
    try {
      const response = await fetch(`${baseUrl}/funcionarios`, {
        method: "GET",
      });

      if (!response.ok) {
        const errorData = await response.json();
        alert(`Erro ao listar funcionários: ${errorData.message || response.statusText}`);
        return;
      }

      const funcionarios = await response.json();
      const listaFuncionarios = document.getElementById("lista-funcionarios");
      listaFuncionarios.innerHTML = "";

      funcionarios.data.forEach(funcionario => {
        const funcionarioDiv = document.createElement("div");
        funcionarioDiv.classList.add("item");
        funcionarioDiv.innerHTML = `
        <p><strong>Nome:</strong> ${funcionario.nome}</p>
        <p><strong>Username:</strong> ${funcionario.username}</p>
        <button class="deletar-funcionario" data-id="${funcionario.id}">Deletar</button>
      `;
        listaFuncionarios.appendChild(funcionarioDiv);
      });
    } catch (error) {
      console.error("Erro ao listar funcionários:", error.message);
    }
  }

  async function deletarFuncionario(id) {
    try {
      const response = await fetch(`${baseUrl}/funcionarios/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Funcionário deletado com sucesso!");
        listarFuncionarios();
      } else {
        const errorData = await response.json();
        alert(`Erro ao deletar funcionário: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao deletar funcionário:", error.message);
    }
  }

  document.querySelectorAll(".deletar-funcionario").forEach(button => {
    button.addEventListener("click", async function () {
      const id = this.getAttribute("data-id");
      await deletarFuncionario(id);
    });
  });

  listarFuncionarios(); // Lista funcionários ao carregar a página

  // Mesas - CRUD
  document.getElementById("form-mesa").addEventListener("submit", async function (e) {
    e.preventDefault();

    const numero = document.getElementById("numero-mesa").value.trim();

    try {
      const response = await fetch(`${baseUrl}/mesas`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ numero }),
      });

      if (response.status === 201) {
        alert("Mesa criada com sucesso!");
        listarMesas();
      } else {
        const errorData = await response.json();
        alert(`Erro ao criar mesa: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao criar mesa:", error.message);
    }
  });

  async function listarMesas() {
    try {
      const response = await fetch(`${baseUrl}/mesas`, {
        method: "GET",
      });

      if (!response.ok) {
        const errorData = await response.json();
        alert(`Erro ao listar mesas: ${errorData.message || response.statusText}`);
        return;
      }

      const mesas = await response.json();
      const listaMesas = document.getElementById("lista-mesas");
      listaMesas.innerHTML = "";

      mesas.data.forEach(mesa => {
        const mesaDiv = document.createElement("div");
        mesaDiv.classList.add("item");
        mesaDiv.innerHTML = `
          <p><strong>Número da Mesa:</strong> ${mesa.numero}</p>
          <button onclick="deletarMesa(${mesa.id})">Deletar</button>
        `;
        listaMesas.appendChild(mesaDiv);
      });
    } catch (error) {
      console.error("Erro ao listar mesas:", error.message);
    }
  }

  async function deletarMesa(id) {
    try {
      const response = await fetch(`${baseUrl}/mesas/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Mesa deletada com sucesso!");
        listarMesas();
      } else {
        const errorData = await response.json();
        alert(`Erro ao deletar mesa: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao deletar mesa:", error.message);
    }
  }

  document.querySelectorAll(".deletar-mesa").forEach(button => {
    button.addEventListener("click", async function () {
      const id = this.getAttribute("data-id");
      await deletarMesa(id);
    });
  });

  listarMesas(); // Lista mesas ao carregar a página

  // Produtos - CRUD
  document.getElementById("form-produto").addEventListener("submit", async function (e) {
    e.preventDefault();

    const nome = document.getElementById("nome-produto").value.trim();
    const preco = parseFloat(document.getElementById("preco-produto").value);

    try {
      const response = await fetch(`${baseUrl}/produtos`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ nome, preco }),
      });

      if (response.status === 201) {
        alert("Produto criado com sucesso!");
        listarProdutos();
      } else {
        const errorData = await response.json();
        alert(`Erro ao criar produto: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao criar produto:", error.message);
    }
  });

  async function listarProdutos() {
    try {
      const response = await fetch(`${baseUrl}/produtos`, {
        method: "GET",
      });

      if (!response.ok) {
        const errorData = await response.json();
        alert(`Erro ao listar produtos: ${errorData.message || response.statusText}`);
        return;
      }

      const produtos = await response.json();
      const listaProdutos = document.getElementById("lista-produtos");
      listaProdutos.innerHTML = "";

      produtos.data.forEach(produto => {
        const produtoDiv = document.createElement("div");
        produtoDiv.classList.add("item");
        produtoDiv.innerHTML = `
          <p><strong>Nome:</strong> ${produto.nome}</p>
          <p><strong>Preço:</strong> R$${produto.preco.toFixed(2)}</p>
          <button onclick="deletarProduto(${produto.id})">Deletar</button>
        `;
        listaProdutos.appendChild(produtoDiv);
      });
    } catch (error) {
      console.error("Erro ao listar produtos:", error.message);
    }
  }

  async function deletarProduto(id) {
    try {
      const response = await fetch(`${baseUrl}/produtos/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Produto deletado com sucesso!");
        listarProdutos();
      } else {
        const errorData = await response.json();
        alert(`Erro ao deletar produto: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao deletar produto:", error.message);
    }
  }

  document.querySelectorAll(".deletar-produto").forEach(button => {
    button.addEventListener("click", async function () {
      const id = this.getAttribute("data-id");
      await deletarProduto(id);
    });
  });

  listarProdutos(); // Lista produtos ao carregar a página


  // Pagamentos - CRUD
  document.getElementById("form-pagamento").addEventListener("submit", async function (e) {
    e.preventDefault();

    const idMesa = parseInt(document.getElementById("id-mesa-pagamento").value);
    const metodo = document.getElementById("metodo-pagamento").value.trim();
    const valor = parseFloat(document.getElementById("valor-pagamento").value);

    try {
      const response = await fetch(`${baseUrl}/pagamentos`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ metodo, valor, id_mesa: idMesa }),
      });

      if (response.status === 201) {
        alert("Pagamento criado com sucesso!");
        listarPagamentos();
      } else {
        const errorData = await response.json();
        alert(`Erro ao criar pagamento: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao criar pagamento:", error.message);
    }
  });

  async function listarPagamentos() {
    try {
      const response = await fetch(`${baseUrl}/pagamentos`, {
        method: "GET",
      });

      if (!response.ok) {
        const errorData = await response.json();
        alert(`Erro ao listar pagamentos: ${errorData.message || response.statusText}`);
        return;
      }

      const pagamentos = await response.json();
      const listaPagamentos = document.getElementById("lista-pagamentos");
      listaPagamentos.innerHTML = "";

      pagamentos.data.forEach(pagamento => {
        const pagamentoDiv = document.createElement("div");
        pagamentoDiv.classList.add("item");
        pagamentoDiv.innerHTML = `
          <p><strong>Método:</strong> ${pagamento.metodo}</p>
          <p><strong>Valor:</strong> R$${pagamento.valor.toFixed(2)}</p>
          <p><strong>ID da Mesa:</strong> ${pagamento.id_mesa}</p>
          <button onclick="deletarPagamento(${pagamento.id})">Deletar</button>
        `;
        listaPagamentos.appendChild(pagamentoDiv);
      });
    } catch (error) {
      console.error("Erro ao listar pagamentos:", error.message);
    }
  }

  async function deletarPagamento(id) {
    try {
      const response = await fetch(`${baseUrl}/pagamentos/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Pagamento deletado com sucesso!");
        listarPagamentos();
      } else {
        const errorData = await response.json();
        alert(`Erro ao deletar pagamento: ${errorData.message || response.statusText}`);
      }
    } catch (error) {
      console.error("Erro ao deletar pagamento:", error.message);
    }
  }

  document.querySelectorAll(".deletar-pagamento").forEach(button => {
    button.addEventListener("click", async function () {
      const id = this.getAttribute("data-id");
      await deletarPagamento(id);
    });
  });

  listarPagamentos(); // Lista pagamentos ao carregar a página
});
