document.addEventListener("DOMContentLoaded", function () {
  const baseUrl = "http://localhost/restaurante-webservice";

  const imagemProdutos = {
    Bruschetta: "./assets/imgProducts/bruschetta.jpeg",
    "Tábua de Queijos": "./assets/imgProducts/tabua_queijos.jpeg",
    "Salada Caprese": "./assets/imgProducts/salada_caprese.jpeg",
    Carbonara: "./assets/imgProducts/carbonara.png",
    "Filé Mignon ao Molho Madeira":
      "./assets/imgProducts/file_mignon_madeira.jpeg",
    "Risoto de Cogumelos": "./assets/imgProducts/risoto_cogumelos.jpeg",
    Tiramisù: "./assets/imgProducts/tiramisu.jpeg",
    "Petit Gâteau": "./assets/imgProducts/petit_gateau.jpeg",
    Cheesecake: "./assets/imgProducts/cheesecake.jpeg",
    "Vinho Tinto": "./assets/imgProducts/vinho_tinto.jpeg",
    "Suco de Laranja": "./assets/imgProducts/suco_laranja.jpeg",
    "Água com Gás": "./assets/imgProducts/agua_com_gas.jpeg",
  };

  const imagemFuncionarios = {
    "Carlos Souza": ".assets/imgUsers/carlos_souza.webp",
    "João Silva": "./assets/imgUsers/joao_silva.webp",
    "Maria Oliveira": ".assets/imgUsers/maria_oliveira.webp",
  };
  // Elementos
  const loginSection = document.getElementById("login-section");
  const mainContent = document.getElementById("main-content");
  const logoutButton = document.getElementById("logout-button");

  // Navegação entre seções
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const sectionId = link.getAttribute("href").substring(1);
      mostrarSecao(sectionId);
    });
  });

  function mostrarSecao(sectionId) {
    document.querySelectorAll(".section").forEach((section) => {
      section.classList.remove("active");
    });
    document.getElementById(sectionId).classList.add("active");
    // Chamar a função de listagem correspondente
    if (sectionId === "home") {
      listarProdutosHome();
    } else if (sectionId === "funcionarios") {
      listarFuncionarios();
    } else if (sectionId === "mesas") {
      listarMesas();
    } else if (sectionId === "produtos") {
      listarProdutos();
    } else if (sectionId === "pagamentos") {
      listarPagamentos();
    }
  }

  // Login
  document
    .getElementById("login-form")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      const username = document.getElementById("username-login").value.trim();
      const senha = document.getElementById("senha-login").value.trim();

      try {
        // Simulando autenticação para simplificar
        if (username === "admin" && senha === "admin") {
          alert("Login bem-sucedido!");
          loginSection.style.display = "none";
          mainContent.style.display = "block";
          mostrarSecao("home");
          document.querySelectorAll(".restricted").forEach((link) => {
            link.style.display = "block";
          });
        } else {
          document.getElementById("login-error").style.display = "block";
        }
      } catch (error) {
        console.error("Erro ao tentar fazer login:", error.message);
      }
    });

  // Logout
  logoutButton.addEventListener("click", function () {
    mainContent.style.display = "none";
    loginSection.style.display = "block";
    document.getElementById("login-form").reset();
    document.getElementById("login-error").style.display = "none";
    document.querySelectorAll(".restricted").forEach((link) => {
      link.style.display = "none";
    });
  });

  // Listar Produtos na Home
  async function listarProdutosHome() {
    try {
      const response = await fetch(`${baseUrl}/produtos`, {
        method: "GET",
      });

      if (!response.ok) {
        const errorData = await response.json();
        alert(
          `Erro ao listar produtos: ${errorData.message || response.statusText}`
        );
        return;
      }

      const produtos = await response.json();
      const listaProdutosHome = document.getElementById("lista-produtos-home");
      listaProdutosHome.innerHTML = "";

      produtos.data.forEach((produto) => {
        const produtoDiv = document.createElement("div");
        produtoDiv.classList.add("item");

        // Se não houver imagem específica, usar uma imagem padrão
        const imagem =
          imagemProdutos[produto.nome] || "./assets/imgProducts/default.jpeg";

        produtoDiv.innerHTML = `
          <img src="${imagem}" alt="${produto.nome}">
          <p><strong>Nome:</strong> ${produto.nome}</p>
          <p><strong>Preço:</strong> R$${parseFloat(produto.preco).toFixed(
            2
          )}</p>
        `;

        listaProdutosHome.appendChild(produtoDiv);
      });
    } catch (error) {
      console.error("Erro ao listar produtos:", error.message);
    }
  }

  // Funcionários - CRUD
  document
    .getElementById("form-funcionario")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      const nome = document.getElementById("nome-funcionario").value.trim();
      const username = document
        .getElementById("username-funcionario")
        .value.trim();
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
          e.target.reset();
        } else {
          const errorData = await response.json();
          alert(
            `Erro ao criar funcionário: ${
              errorData.message || response.statusText
            }`
          );
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
        alert(
          `Erro ao listar funcionários: ${
            errorData.message || response.statusText
          }`
        );
        return;
      }

      const funcionarios = await response.json();
      const listaFuncionarios = document.getElementById("lista-funcionarios");
      listaFuncionarios.innerHTML = "";

      funcionarios.data.forEach((funcionario) => {
        console.log(funcionario.nome);
        const imagem =
          imagemFuncionarios[funcionario.nome] ||
          "./assets/imgUsers/default.jpeg";
        const funcionarioDiv = document.createElement("div");
        funcionarioDiv.classList.add("item");
        funcionarioDiv.innerHTML = `
          <img src="${imagem}" alt="${funcionario.nome}">
          <p><strong>Nome:</strong> ${funcionario.nome}</p>
          <p><strong>Username:</strong> ${funcionario.username}</p>
          <div class="action-buttons">
            <button class="editar-funcionario" data-id="${funcionario.id}" data-nome="${funcionario.nome}" data-username="${funcionario.username}">Editar</button>
            <button class="deletar-funcionario" data-id="${funcionario.id}">Deletar</button>
          </div>
        `;
        listaFuncionarios.appendChild(funcionarioDiv);
      });

      // Eventos para editar e deletar funcionários
      document.querySelectorAll(".editar-funcionario").forEach((button) => {
        button.addEventListener("click", function () {
          const id = this.getAttribute("data-id");
          const nome = this.getAttribute("data-nome");
          const username = this.getAttribute("data-username");
          abrirModalEditarFuncionario(id, nome, username);
        });
      });

      document.querySelectorAll(".deletar-funcionario").forEach((button) => {
        button.addEventListener("click", async function () {
          const id = this.getAttribute("data-id");
          await deletarFuncionario(id);
        });
      });
    } catch (error) {
      console.error("Erro ao listar funcionários:", error.message);
    }
  }

  async function deletarFuncionario(id) {
    if (!confirm("Tem certeza que deseja deletar este funcionário?")) {
      return;
    }

    try {
      const response = await fetch(`${baseUrl}/funcionarios/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Funcionário deletado com sucesso!");
        listarFuncionarios();
      } else {
        const errorData = await response.json();
        alert(
          `Erro ao deletar funcionário: ${
            errorData.message || response.statusText
          }`
        );
      }
    } catch (error) {
      console.error("Erro ao deletar funcionário:", error.message);
    }
  }

  // Funções para editar funcionário
  const modalEditarFuncionario = document.getElementById(
    "modal-editar-funcionario"
  );
  const fecharModalFuncionario = document.getElementById(
    "fechar-modal-funcionario"
  );

  fecharModalFuncionario.addEventListener("click", function () {
    modalEditarFuncionario.style.display = "none";
  });

  window.addEventListener("click", function (event) {
    if (event.target == modalEditarFuncionario) {
      modalEditarFuncionario.style.display = "none";
    }
  });

  function abrirModalEditarFuncionario(id, nome, username) {
    const nomeInput = document.getElementById("editar-nome-funcionario");
    const usernameInput = document.getElementById("editar-username-funcionario");

    // Preencher os campos com os valores atuais
    nomeInput.value = nome;
    usernameInput.value = username;
    document.getElementById("editar-id-funcionario").value = id;

    // Armazenar os valores originais nos atributos data-*
    nomeInput.setAttribute("data-original-nome", nome);
    usernameInput.setAttribute("data-original-username", username);

    // Limpar o campo de senha ao abrir o modal
    document.getElementById("editar-senha-funcionario").value = "";

    modalEditarFuncionario.style.display = "block";
  }

  document
      .getElementById("form-editar-funcionario")
      .addEventListener("submit", async function (e) {
        e.preventDefault();

        // Capturar os valores do formulário
        const id = document.getElementById("editar-id-funcionario").value;
        const nome = document.getElementById("editar-nome-funcionario").value.trim();
        const username = document
            .getElementById("editar-username-funcionario")
            .value.trim();
        const senha = document.getElementById("editar-senha-funcionario").value.trim();

        // Capturar os valores originais armazenados no modal
        const nomeOriginal = document
            .getElementById("editar-nome-funcionario")
            .getAttribute("data-original-nome");
        const usernameOriginal = document
            .getElementById("editar-username-funcionario")
            .getAttribute("data-original-username");

        // Caso 1: Atualizar apenas o nome
        if (nome !== nomeOriginal && username === usernameOriginal && !senha) {
          try {
            const response = await fetch(`${baseUrl}/funcionarios/${id}/nome`, {
              method: "PUT",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ nome }),
            });

            if (!response.ok) {
              const errorData = await response.json();
              alert(
                  `Erro ao atualizar nome: ${errorData.message || response.statusText}`
              );
              return;
            }

            alert("Nome atualizado com sucesso!");
            listarFuncionarios();
            modalEditarFuncionario.style.display = "none";
          } catch (error) {
            console.error("Erro ao atualizar nome:", error.message);
          }
          return;
        }

        // Caso 2: Atualizar apenas a senha
        if (senha && nome === nomeOriginal && username === usernameOriginal) {
          try {
            const response = await fetch(`${baseUrl}/funcionarios/${id}/senha`, {
              method: "PUT",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ novaSenha: senha }),
            });

            if (!response.ok) {
              const errorData = await response.json();
              alert(
                  `Erro ao atualizar senha: ${errorData.message || response.statusText}`
              );
              return;
            }

            alert("Senha atualizada com sucesso!");
            listarFuncionarios();
            modalEditarFuncionario.style.display = "none";
          } catch (error) {
            console.error("Erro ao atualizar senha:", error.message);
          }
          return;
        }

        // Caso 3: Atualizar nome e/ou username (e senha, se fornecida)
        const dadosAtualizados = {
          nome: nome || nomeOriginal,
          username: username || usernameOriginal,
        };

        // Adicionar a senha se foi preenchida
        if (senha) {
          dadosAtualizados.senha = senha;
        }

        try {
          const response = await fetch(`${baseUrl}/funcionarios/${id}`, {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(dadosAtualizados),
          });

          if (!response.ok) {
            const errorData = await response.json();
            alert(
                `Erro ao atualizar funcionário: ${
                    errorData.message || response.statusText
                }`
            );
            return;
          }

          alert("Funcionário atualizado com sucesso!");
          listarFuncionarios();
          modalEditarFuncionario.style.display = "none";
        } catch (error) {
          console.error("Erro ao atualizar funcionário:", error.message);
        }
      });

  // Mesas - CRUD
  document
    .getElementById("form-mesa")
    .addEventListener("submit", async function (e) {
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
          e.target.reset();
        } else {
          const errorData = await response.json();
          alert(
            `Erro ao criar mesa: ${errorData.message || response.statusText}`
          );
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
        alert(
          `Erro ao listar mesas: ${errorData.message || response.statusText}`
        );
        return;
      }

      const mesas = await response.json();
      const listaMesas = document.getElementById("lista-mesas");
      listaMesas.innerHTML = "";

      mesas.data.forEach((mesa) => {
        const mesaDiv = document.createElement("div");
        mesaDiv.classList.add("item");
        mesaDiv.innerHTML = `
          <p><strong>Número da Mesa:</strong> ${mesa.numero}</p>
          <div class="action-buttons">
            <button class="deletar-mesa" data-id="${mesa.id}">Deletar</button>
          </div>
        `;
        listaMesas.appendChild(mesaDiv);
      });

      // Adiciona eventos de clique para deletar após listar
      document.querySelectorAll(".deletar-mesa").forEach((button) => {
        button.addEventListener("click", async function () {
          const id = this.getAttribute("data-id");
          await deletarMesa(id);
        });
      });
    } catch (error) {
      console.error("Erro ao listar mesas:", error.message);
    }
  }

  async function deletarMesa(id) {
    if (!confirm("Tem certeza que deseja deletar esta mesa?")) {
      return;
    }

    try {
      const response = await fetch(`${baseUrl}/mesas/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Mesa deletada com sucesso!");
        listarMesas();
      } else {
        const errorData = await response.json();
        alert(
          `Erro ao deletar mesa: ${errorData.message || response.statusText}`
        );
      }
    } catch (error) {
      console.error("Erro ao deletar mesa:", error.message);
    }
  }

  // Produtos - CRUD
  document
    .getElementById("form-produto")
    .addEventListener("submit", async function (e) {
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
          e.target.reset();
        } else {
          const errorData = await response.json();
          alert(
            `Erro ao criar produto: ${errorData.message || response.statusText}`
          );
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
        alert(
          `Erro ao listar produtos: ${errorData.message || response.statusText}`
        );
        return;
      }

      const produtos = await response.json();
      const listaProdutos = document.getElementById("lista-produtos");
      listaProdutos.innerHTML = "";

      produtos.data.forEach((produto) => {
        const imagem =
          imagemProdutos[produto.nome] || "./assets/imgProducts/default.jpeg";
        const produtoDiv = document.createElement("div");
        produtoDiv.classList.add("item");
        produtoDiv.innerHTML = `
          <img src="${imagem}" alt="${produto.nome}">
          <p><strong>Nome:</strong> ${produto.nome}</p>
          <p><strong>Preço:</strong> R$${parseFloat(produto.preco).toFixed(
            2
          )}</p>
          <div class="action-buttons">
            <button class="editar-produto" data-id="${produto.id}" data-nome="${
          produto.nome
        }" data-preco="${produto.preco}">Editar</button>
            <button class="deletar-produto" data-id="${
              produto.id
            }">Deletar</button>
          </div>
        `;
        listaProdutos.appendChild(produtoDiv);
      });

      // Eventos para editar e deletar produtos
      document.querySelectorAll(".editar-produto").forEach((button) => {
        button.addEventListener("click", function () {
          const id = this.getAttribute("data-id");
          const nome = this.getAttribute("data-nome");
          const preco = this.getAttribute("data-preco");
          abrirModalEditarProduto(id, nome, preco);
        });
      });

      document.querySelectorAll(".deletar-produto").forEach((button) => {
        button.addEventListener("click", async function () {
          const id = this.getAttribute("data-id");
          await deletarProduto(id);
        });
      });
    } catch (error) {
      console.error("Erro ao listar produtos:", error.message);
    }
  }

  async function deletarProduto(id) {
    if (!confirm("Tem certeza que deseja deletar este produto?")) {
      return;
    }

    try {
      const response = await fetch(`${baseUrl}/produtos/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Produto deletado com sucesso!");
        listarProdutos();
      } else {
        const errorData = await response.json();
        alert(
          `Erro ao deletar produto: ${errorData.message || response.statusText}`
        );
      }
    } catch (error) {
      console.error("Erro ao deletar produto:", error.message);
    }
  }

  // Funções para editar produto
  const modalEditarProduto = document.getElementById("modal-editar-produto");
  const fecharModalProduto = document.getElementById("fechar-modal-produto");

  fecharModalProduto.addEventListener("click", function () {
    modalEditarProduto.style.display = "none";
  });

  window.addEventListener("click", function (event) {
    if (event.target == modalEditarProduto) {
      modalEditarProduto.style.display = "none";
    }
  });

  function abrirModalEditarProduto(id, nome, preco) {
    const nomeInput = document.getElementById("editar-nome-produto");
    const precoInput = document.getElementById("editar-preco-produto");

    nomeInput.value = nome;
    precoInput.value = parseFloat(preco).toFixed(2);

    nomeInput.setAttribute("data-original-nome", nome);
    precoInput.setAttribute("data-original-preco", preco);

    document.getElementById("editar-id-produto").value = id;
    modalEditarProduto.style.display = "block";
  }


  document
      .getElementById("form-editar-produto")
      .addEventListener("submit", async function (e) {
        e.preventDefault();

        const id = document.getElementById("editar-id-produto").value;
        const nome = document.getElementById("editar-nome-produto").value.trim();
        const preco = parseFloat(
            document.getElementById("editar-preco-produto").value
        );

        // Capturar valores originais para verificação
        const nomeOriginal = document
            .getElementById("editar-nome-produto")
            .getAttribute("data-original-nome");
        const precoOriginal = parseFloat(
            document
                .getElementById("editar-preco-produto")
                .getAttribute("data-original-preco")
        );

        if (preco !== precoOriginal && nome === nomeOriginal) {
          try {
            const response = await fetch(`${baseUrl}/produtos/${id}/preco`, {
              method: "PUT",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ preco }),
            });

            if (!response.ok) {
              const errorData = await response.json();
              alert(
                  `Erro ao atualizar preço: ${errorData.message || response.statusText}`
              );
              return;
            }

            alert("Preço atualizado com sucesso!");
            listarProdutos();
            modalEditarProduto.style.display = "none";
          } catch (error) {
            console.error("Erro ao atualizar preço:", error.message);
          }
          return;
        }

        const dadosAtualizados = {
          nome: nome || nomeOriginal,
          preco: preco || precoOriginal,
        };

        try {
          const response = await fetch(`${baseUrl}/produtos/${id}`, {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(dadosAtualizados),
          });

          if (!response.ok) {
            const errorData = await response.json();
            alert(
                `Erro ao atualizar produto: ${
                    errorData.message || response.statusText
                }`
            );
            return;
          }

          alert("Produto atualizado com sucesso!");
          listarProdutos();
          modalEditarProduto.style.display = "none";
        } catch (error) {
          console.error("Erro ao atualizar produto:", error.message);
        }
      });

  // Pagamentos - CRUD
  document
    .getElementById("form-pagamento")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      const idMesa = parseInt(
        document.getElementById("id-mesa-pagamento").value
      );
      const metodo = document.getElementById("metodo-pagamento").value.trim();
      const valor = parseFloat(
        document.getElementById("valor-pagamento").value
      );

      // Verificar se os campos estão preenchidos corretamente
      if (isNaN(idMesa) || !metodo || isNaN(valor)) {
        alert("Por favor, preencha todos os campos corretamente.");
        return;
      }

      try {
        const response = await fetch(`${baseUrl}/pagamentos`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ metodo, valor, id_mesa: idMesa }),
        });

        if (response.status === 201) {
          alert("Pagamento adicionado com sucesso!");
          listarPagamentos();
          e.target.reset();
        } else {
          const errorData = await response.json();
          alert(
            `Erro ao adicionar pagamento: ${
              errorData.message || response.statusText
            }`
          );
        }
      } catch (error) {
        console.error("Erro ao adicionar pagamento:", error.message);
      }
    });

  async function listarPagamentos() {
    try {
      const response = await fetch(`${baseUrl}/pagamentos`, {
        method: "GET",
      });

      if (!response.ok) {
        const errorData = await response.json();
        alert(
          `Erro ao listar pagamentos: ${
            errorData.message || response.statusText
          }`
        );
        return;
      }

      const pagamentos = await response.json();
      const listaPagamentos = document.getElementById("lista-pagamentos");
      listaPagamentos.innerHTML = "";

      pagamentos.data.forEach((pagamento) => {
        const pagamentoDiv = document.createElement("div");
        pagamentoDiv.classList.add("item");
        pagamentoDiv.innerHTML = `
          <p><strong>Método:</strong> ${pagamento.metodo}</p>
          <p><strong>Valor:</strong> R$${parseFloat(pagamento.valor).toFixed(
            2
          )}</p>
          <p><strong>ID da Mesa:</strong> ${pagamento.id_mesa}</p>
          <div class="action-buttons">
            <button class="deletar-pagamento" data-id="${
              pagamento.id
            }">Deletar</button>
          </div>
        `;
        listaPagamentos.appendChild(pagamentoDiv);
      });

      // Adiciona eventos de clique para deletar após listar
      document.querySelectorAll(".deletar-pagamento").forEach((button) => {
        button.addEventListener("click", async function () {
          const id = this.getAttribute("data-id");
          await deletarPagamento(id);
        });
      });
    } catch (error) {
      console.error("Erro ao listar pagamentos:", error.message);
    }
  }

  async function deletarPagamento(id) {
    if (!confirm("Tem certeza que deseja deletar este pagamento?")) {
      return;
    }

    try {
      const response = await fetch(`${baseUrl}/pagamentos/${id}`, {
        method: "DELETE",
      });

      if (response.ok) {
        alert("Pagamento deletado com sucesso!");
        listarPagamentos();
      } else {
        const errorData = await response.json();
        alert(
          `Erro ao deletar pagamento: ${
            errorData.message || response.statusText
          }`
        );
      }
    } catch (error) {
      console.error("Erro ao deletar pagamento:", error.message);
    }
  }

  // Inicializar com a seção de login
  loginSection.style.display = "block";
  mainContent.style.display = "none";
});
