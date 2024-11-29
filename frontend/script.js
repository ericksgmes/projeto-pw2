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
  const goToRegister = document.getElementById("go-to-register");
  const goToLogin = document.getElementById("go-to-login");
  const registerSection = document.getElementById("register-section");

  // Navegação entre seções
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const sectionId = link.getAttribute("href").substring(1);
      mostrarSecao(sectionId);
    });
  });

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
          showPopup("Login bem sucedido!", "success");
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

  // Cadastro
  document.getElementById("register-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    // Capturar valores do formulário
    const nome = document.getElementById("register-name").value.trim();
    const username = document.getElementById("register-username").value.trim();
    const senha = document.getElementById("register-password").value.trim();
    const confirmSenha = document.getElementById("register-confirm-password").value.trim();

    // Validações básicas
    if (!nome || !username || !senha || !confirmSenha) {
      showPopup("Todos os campos são obrigatórios.", "error");
      return;
    }

    if (senha !== confirmSenha) {
      showPopup("As senhas não coincidem. Tente novamente.", "error");
      return;
    }

    try {
      // Enviar os dados para o servidor
      const response = await fetch(`${baseUrl}/funcionarios`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ nome, username, senha}),
      });

      if (response.status === 201) {
        showPopup("Cadastro realizado com sucesso! Faça login para continuar.", "success");
        // Limpar formulário e retornar ao login
        document.getElementById("register-form").reset();
        registerSection.style.display = "none";
        loginSection.style.display = "block";
      }
    } catch (error) {
      console.error("Erro ao cadastrar usuário:", error.message);
      showPopup("Erro ao cadastrar usuário. Tente novamente.", "error");
    }
  });

  // Logout
  logoutButton.addEventListener("click", function () {
    showConfirm(
        "Tem certeza que deseja sair?",
        () => {
          // Callback de confirmação
          document.getElementById("main-content").style.display = "none";
          document.getElementById("login-section").style.display = "block";
          document.getElementById("login-form").reset();
          document.getElementById("login-error").style.display = "none";
          document.querySelectorAll(".restricted").forEach((link) => {
            link.style.display = "none";
          });
          showPopup("Logout realizado com sucesso!", "success");
        },
        () => {
          // Callback de cancelamento
          showPopup("Logout cancelado!", "info");
        }
    );
  });
  // Alternar para a tela de cadastro
  goToRegister.addEventListener("click", function (e) {
    e.preventDefault();
    document.getElementById("login-section").style.display = "none";
    document.getElementById("register-section").style.display = "block";
  });

// Alternar para a tela de login
  goToLogin.addEventListener("click", function (e) {
    e.preventDefault();
    document.getElementById("register-section").style.display = "none";
    document.getElementById("login-section").style.display = "block";
  });


  // Listar Produtos na Home
  async function listarProdutosHome() {
    try {
      const response = await fetch(`${baseUrl}/produtos`, {
        method: "GET",
      });

      if (!response.ok) {
        const errorData = await response.json();
        showPopup(`Erro ao listar produtos: ${errorData.message || response.statusText}`, "error");
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
          showPopup("Funcionário criado com sucesso!", "success");
          listarFuncionarios();
          e.target.reset();
        } else {
          const errorData = await response.json();
          showPopup(`Erro ao criar funcionário: ${errorData.message || response.statusText}`, "error");
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
        showPopup(`Erro ao listar funcionários: ${errorData.message || response.statusText}`, "error");
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
    // Substituir o confirm() pelo showConfirm
    showConfirm(
        "Tem certeza que deseja deletar este funcionário?",
        async () => {
          // Callback para o botão "Sim"
          try {
            const response = await fetch(`${baseUrl}/funcionarios/${id}`, {
              method: "DELETE",
            });

            if (response.ok) {
              showPopup("Funcionário deletado com sucesso!", "success");
              listarFuncionarios();
            } else {
              const errorData = await response.json();
              showPopup(
                  `Erro ao deletar funcionário: ${
                      errorData.message || response.statusText
                  }`,
                  "error"
              );
            }
          } catch (error) {
            console.error("Erro ao deletar funcionário:", error.message);
            showPopup("Erro ao deletar funcionário.", "error");
          }
        },
        () => {
          // Callback para o botão "Não"
          showPopup("Ação cancelada!", "info");
        }
    );
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
              showPopup(`Erro ao atualizar nome: ${errorData.message || response.statusText}`, "error");
              return;
            }

            showPopup("Nome atualizado com sucesso!", "success");
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
              showPopup(`Erro ao atualizar senha!: ${errorData.message || response.statusText}`, "error");
              return;
            }

            showPopup("Senha atualizada com sucesso!", "success");
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
            showPopup(`Erro ao atualizar funcionário: ${errorData.message || response.statusText}`, "error");
            return;
          }

          showPopup("Funcionário atualizado com sucesso!", "success");
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
          showPopup("Mesa criada com sucesso!", "success");
          listarMesas();
          e.target.reset();
        } else {
          const errorData = await response.json();
          showPopup(`Erro ao criar mesa: ${errorData.message || response.statusText}`, "error");
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
        showPopup(`Erro ao listar mesas: ${errorData.message || response.statusText}`, "error");
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
    showConfirm(
        "Tem certeza que deseja deletar esta mesa?",
        async () => {
          try {
            const response = await fetch(`${baseUrl}/mesas/${id}`, {
              method: "DELETE",
            });

            if (response.ok) {
              showPopup("Mesa deletada com sucesso!", "success");
              listarMesas();
            } else {
              const errorData = await response.json();
              showPopup(`Erro ao deletar mesa: ${errorData.message || response.statusText}`, "error");
            }
          } catch (error) {
            console.error("Erro ao deletar mesa:", error.message);
          }
        },
        () => {
          showPopup("Ação cancelada!", "info");
        }
    );
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
          showPopup("Produto criado com sucesso!", "success");
          listarProdutos();
          e.target.reset();
        } else {
          const errorData = await response.json();
          showPopup(`Erro ao criar produto: ${errorData.message || response.statusText}`, "error");
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
        showPopup(`Erro ao listar produtos: ${errorData.message || response.statusText}`, "error");
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
    showConfirm(
        "Tem certeza que deseja deletar este produto?",
        async () => {
          try {
            const response = await fetch(`${baseUrl}/produtos/${id}`, {
              method: "DELETE",
            });

            if (response.ok) {
              showPopup("Produto deletado com sucesso!", "success");
              listarProdutos();
            } else {
              const errorData = await response.json();
              showPopup(`Erro ao deletar produto: ${errorData.message || response.statusText}`, "error");
            }
          } catch (error) {
            console.error("Erro ao deletar produto:", error.message);
          }
        },
        () => {
          showPopup("Ação cancelada!", "info");
        }
    );
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
              showPopup(`Erro ao atualizar preço: ${errorData.message || response.statusText}`, "error");
              return;
            }

            showPopup("Preço atualizado com sucesso!", "success");
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
            showPopup(`Erro ao atualizar produto: ${errorData.message || response.statusText}`, "error");
            return;
          }

          showPopup("Produto atualizado com sucesso!", "success");
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
        showPopup("Por favor preencha todos os campos corretamente", "info");
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
          showPopup("Pagamento adicionado com sucesso", "success");
          listarPagamentos();
          e.target.reset();
        } else {
          const errorData = await response.json();
          showPopup(`Erro ao adicionar pagamentos: ${errorData.message || response.statusText}`, "error");
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
        showPopup(`Erro ao listar pagamentos: ${errorData.message || response.statusText}`, "error");
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
    showConfirm(
        "Tem certeza que deseja deletar este pagamento?",
        async () => {
          try {
            const response = await fetch(`${baseUrl}/pagamentos/${id}`, {
              method: "DELETE",
            });

            if (response.ok) {
              showPopup("Pagamento deletado com sucesso!", "success");
              listarPagamentos();
            } else {
              const errorData = await response.json();
              showPopup(`Erro ao deletar pagamento: ${errorData.message || response.statusText}`, "error");
            }
          } catch (error) {
            console.error("Erro ao deletar pagamento:", error.message);
          }
        },
        () => {
          showPopup("Ação cancelada!", "info");
        }
    );
  }

  // Inicializar com a seção de login
  loginSection.style.display = "block";
  mainContent.style.display = "none";

  function showConfirm(message, onConfirm, onCancel) {
    // Criar o container do confirm
    const confirmContainer = document.createElement("div");
    confirmContainer.className = "confirm-container";

    // Criar a caixa de confirmação
    const confirmBox = document.createElement("div");
    confirmBox.className = "confirm-box";
    confirmBox.innerHTML = `
    <p>${message}</p>
    <div class="confirm-buttons">
      <button id="confirm-yes">Sim</button>
      <button id="confirm-no">Não</button>
    </div>
  `;

    // Adicionar a caixa ao container
    confirmContainer.appendChild(confirmBox);
    document.body.appendChild(confirmContainer);

    // Lidar com o clique no botão "Sim"
    document.getElementById("confirm-yes").onclick = () => {
      document.body.removeChild(confirmContainer);
      if (onConfirm) onConfirm();
    };

    // Lidar com o clique no botão "Não"
    document.getElementById("confirm-no").onclick = () => {
      document.body.removeChild(confirmContainer);
      if (onCancel) onCancel();
    };
  }

// Exemplo de uso
  document.getElementById("delete-button").addEventListener("click", () => {
    showConfirm(
        "Tem certeza que deseja deletar este item?",
        () => {
          alert("Item deletado com sucesso!");
        },
        () => {
          alert("Ação cancelada!");
        }
    );
  });


  function showPopup(message, type = "info", duration = 3000) {
    const popupContainer = document.getElementById("popup-container");
    if (!popupContainer) {
      console.error("Popup container not found in the DOM.");
      return;
    }

    const popup = document.createElement("div");
    popup.className = `popup ${type}`;
    popup.innerHTML = `
    <span class="popup-message">${message}</span>
    <button class="popup-close">×</button>
    <div class="popup-progress"></div>
  `;

    popupContainer.appendChild(popup);

    const closeButton = popup.querySelector(".popup-close");
    closeButton.addEventListener("click", () => {
      popup.classList.add("removing");
      setTimeout(() => {
        popup.remove();
      }, 500);
    });

    const progressBar = popup.querySelector(".popup-progress");
    progressBar.style.transition = `width ${duration}ms linear`;
    setTimeout(() => {
      progressBar.style.width = "100%";
    }, 10);

    const timeout = setTimeout(() => {
      popup.classList.add("removing");
      setTimeout(() => {
        popup.remove();
      }, 500);
    }, duration);

    closeButton.addEventListener("click", () => clearTimeout(timeout));
  }


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
});
