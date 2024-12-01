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

  // const imagemUsuarios = {
  //   "Carlos Souza": ".assets/imgUsers/carlos_souza.webp",
  //   "João Silva": "./assets/imgUsers/joao_silva.webp",
  //   "Maria Oliveira": ".assets/imgUsers/maria_oliveira.webp",
  // };

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
          // Fazer requisição ao backend para autenticação
          const response = await fetch(`${baseUrl}/auth`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({ username, senha }), // Enviar username e senha no corpo da requisição
          });

          const result = await response.json();

          if (response.ok) {
            // Armazenar o token no localStorage
            localStorage.setItem("token", result.data.token);

            showPopup("Login bem-sucedido!", "success");
            loginSection.style.display = "none";
            mainContent.style.display = "block";
            await exibirUsuarioLogado();
            mostrarSecao("home");

            // Exibir links restritos se necessário
            document.querySelectorAll(".restricted").forEach((link) => {
              link.style.display = "block";
            });
          } else {
            // Exibir erro retornado pelo backend
            showPopup(result.message || "Erro no login.", "error");
          }
        } catch (error) {
          console.error("Erro ao tentar fazer login:", error.message);
          showPopup("Erro ao tentar fazer login. Tente novamente.", "error");
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
      const response = await fetch(`${baseUrl}/signup`, {
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
          localStorage.removeItem("token"); // Remove o token do localStorage
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
      const token = localStorage.getItem("token");
      const response = await fetch(`${baseUrl}/produtos`, {
        method: "GET",
        headers: {
          "Authorization": `Bearer ${token}`
        },
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

// Usuários - CRUD
  document
      .getElementById("form-usuario")
      .addEventListener("submit", async function (e) {
        e.preventDefault();

        const nome = document.getElementById("nome-usuario").value.trim();
        const username = document.getElementById("username-usuario").value.trim();
        const senha = document.getElementById("senha-usuario").value.trim();

        try {
          const token = localStorage.getItem("token");
          const response = await fetch(`${baseUrl}/usuarios`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({
              nome,
              username,
              senha,
            }),
          });

          if (response.status === 201) {
            showPopup("Usuário criado com sucesso!", "success");
            await listarUsuarios();
            e.target.reset();
          } else {
            const errorData = await response.json();
            showPopup(
                `Erro ao criar usuário: ${
                    errorData.message || response.statusText
                }`,
                "error"
            );
          }
        } catch (error) {
          console.error("Erro ao criar usuário:", error.message);
        }
      });

  async function exibirUsuarioLogado() {
    try {
      const token = localStorage.getItem("token");

      if (!token) {
        showPopup("Token não encontrado. Faça login novamente.", "error");
        return;
      }

      // Decodificar o token JWT para obter as informações do usuário logado
      const decodedToken = JSON.parse(atob(token.split('.')[1])); // Decodifica o payload do JWT
      const nome = decodedToken.nome; // Obtém o nome do usuário logado
      const username = decodedToken.username; // Obtém o username do usuário logado

      // Preencher as informações na barra superior
      document.getElementById("usuario-logado-nome").textContent = nome;
      document.getElementById("usuario-logado-username").textContent = `@${username}`;
    } catch (error) {
      console.error("Erro ao exibir usuário logado:", error.message);
      showPopup("Erro ao carregar informações do usuário logado.", "error");
    }
  }

  function abrirModalEditarUsuario(id, nome, username, is_admin) {
    const modal = document.getElementById("modal-editar-usuario");
    const inputId = document.getElementById("editar-id-usuario");
    const inputNome = document.getElementById("editar-nome-usuario");
    const inputUsername = document.getElementById("editar-username-usuario");
    const inputSenha = document.getElementById("editar-senha-usuario");
    const selectIsAdmin = document.getElementById("editar-is-admin-usuario");

    // Preencher os campos com os valores atuais
    inputId.value = id;
    inputNome.value = nome;
    inputUsername.value = username;
    inputSenha.value = ""; // Senha em branco para não alterar
    selectIsAdmin.value = is_admin; // Define o valor de is_admin no select

    // Atribuir os valores originais para comparação
    inputNome.setAttribute("data-original-nome", nome);
    inputUsername.setAttribute("data-original-username", username);
    selectIsAdmin.setAttribute("data-original-is_admin", is_admin);

    // Mostrar o modal
    modal.style.display = "block";

    // Fechar modal ao clicar no botão de fechar
    const fecharModal = document.getElementById("fechar-modal-usuario");
    fecharModal.addEventListener("click", function () {
      modal.style.display = "none";
    });
  }


  async function listarUsuarios() {
    try {
      const token = localStorage.getItem("token");

      if (!token) {
        showPopup("Token não encontrado. Faça login novamente.", "error");
        return;
      }

      const response = await fetch(`${baseUrl}/usuarios`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        showPopup(
            `Erro ao listar usuários: ${errorData.message || response.statusText}`,
            "error"
        );
        return;
      }

      const usuarios = await response.json();
      const listaUsuarios = document.getElementById("lista-usuarios");
      listaUsuarios.innerHTML = "";

      usuarios.data.forEach((usuario) => {
        const usuarioDiv = document.createElement("div");
        usuarioDiv.classList.add("item");

        usuarioDiv.innerHTML = `
        <p><strong>Nome:</strong> ${usuario.nome}</p>
        <p><strong>Username:</strong> ${usuario.username}</p>
        <p><strong>Admin:</strong> ${usuario.is_admin ? "Sim" : "Não"}</p>
        <div class="action-buttons">
          <button class="editar-usuario" 
            data-id="${usuario.id}" 
            data-nome="${usuario.nome}" 
            data-username="${usuario.username}" 
            data-is_admin="${usuario.is_admin}">
            Editar
          </button>
          <button class="deletar-usuario" data-id="${usuario.id}">Deletar</button>
        </div>
      `;

        listaUsuarios.appendChild(usuarioDiv);
      });

      async function deletarUsuario(id) {
        showConfirm(
            "Tem certeza que deseja deletar este usuário?",
            async () => {
              try {
                const token = localStorage.getItem("token");
                const response = await fetch(`${baseUrl}/usuarios/${id}`, {
                  method: "DELETE",
                  headers: {
                    Authorization: `Bearer ${token}`,
                  },
                });

                if (response.ok) {
                  showPopup("Usuário deletado com sucesso!", "success");
                  await listarUsuarios();
                } else {
                  const errorData = await response.json();
                  showPopup(
                      `Erro ao deletar usuário: ${
                          errorData.message || response.statusText
                      }`,
                      "error"
                  );
                }
              } catch (error) {
                console.error("Erro ao deletar usuário:", error.message);
                showPopup("Erro ao deletar usuário.", "error");
              }
            },
            () => {
              showPopup("Ação cancelada!", "info");
            }
        );
      }

      // Eventos para editar usuários
      document.querySelectorAll(".editar-usuario").forEach((button) => {
        button.addEventListener("click", function () {
          const id = this.getAttribute("data-id");
          const nome = this.getAttribute("data-nome");
          const username = this.getAttribute("data-username");
          const is_admin = parseInt(this.getAttribute("data-is_admin"), 10); // Converte para número

          console.log("Dados capturados para edição:", {
            id,
            nome,
            username,
            is_admin,
          });

          abrirModalEditarUsuario(id, nome, username, is_admin);
        });
      });

      // Eventos para deletar usuários
      document.querySelectorAll(".deletar-usuario").forEach((button) => {
        button.addEventListener("click", async function () {
          const id = this.getAttribute("data-id");
          await deletarUsuario(id);
        });
      });
    } catch (error) {
      console.error("Erro ao listar usuários:", error.message);
      showPopup("Erro ao listar usuários. Tente novamente mais tarde.", "error");
    }
  }

  document.getElementById("form-editar-usuario").addEventListener("submit", async function (e) {
    e.preventDefault();

    const id = document.getElementById("editar-id-usuario").value;
    const nome = document.getElementById("editar-nome-usuario").value.trim();
    const username = document.getElementById("editar-username-usuario").value.trim();
    const senha = document.getElementById("editar-senha-usuario").value.trim();
    const is_admin = parseInt(document.getElementById("editar-is-admin-usuario").value, 10);
    const token = localStorage.getItem("token");

    // Valores originais
    const nomeOriginal = document.getElementById("editar-nome-usuario").getAttribute("data-original-nome");
    const usernameOriginal = document.getElementById("editar-username-usuario").getAttribute("data-original-username");
    const is_adminOriginal = parseInt(document.getElementById("editar-is-admin-usuario").getAttribute("data-original-is_admin"), 10);

    // Construir payload apenas com campos alterados
    const payload = {};
    if (nome && nome !== nomeOriginal) payload.nome = nome;
    if (username && username !== usernameOriginal) payload.username = username;
    if (senha) payload.novaSenha = senha; // Atualiza a senha apenas se preenchida
    if (is_admin !== is_adminOriginal) payload.is_admin = is_admin;

    // Verifica se algo foi alterado
    if (Object.keys(payload).length === 0) {
      console.log("Nenhuma alteração detectada.");
      showPopup("Nenhuma alteração foi detectada.", "info");
      return;
    }

    console.log("Dados a serem enviados:", payload);

    try {
      const response = await fetch(`${baseUrl}/usuarios/${id}/parcial`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const errorData = await response.json();
        console.error("Erro ao atualizar usuário:", errorData);
        showPopup(`Erro ao atualizar usuário: ${errorData.message || response.statusText}`, "error");
        return;
      }

      console.log("Usuário atualizado com sucesso!");
      showPopup("Usuário atualizado com sucesso!", "success");

      await listarUsuarios();
      document.getElementById("modal-editar-usuario").style.display = "none";
    } catch (error) {
      console.error("Erro ao atualizar usuário:", error.message);
      showPopup("Erro ao atualizar usuário. Tente novamente.", "error");
    }
  });

  // Mesas - CRUD
  document
    .getElementById("form-mesa")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      const numero = document.getElementById("numero-mesa").value.trim();

      try {
        const token = localStorage.getItem("token");
        const response = await fetch(`${baseUrl}/mesas`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`
          },
          body: JSON.stringify({ numero }),
        });

        if (response.status === 201) {
          showPopup("Mesa criada com sucesso!", "success");
          await listarMesas();
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
      const token = localStorage.getItem("token");
      const response = await fetch(`${baseUrl}/mesas`, {
        method: "GET",
        headers: {
          "Authorization": `Bearer ${token}`
        },
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
            const token = localStorage.getItem("token");
            const response = await fetch(`${baseUrl}/mesas/${id}`, {
              method: "DELETE",
              headers: {
                "Authorization": `Bearer ${token}`
              },
            });

            if (response.ok) {
              showPopup("Mesa deletada com sucesso!", "success");
              await listarMesas();
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
        const token = localStorage.getItem("token");
        const response = await fetch(`${baseUrl}/produtos`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`
          },
          body: JSON.stringify({ nome, preco }),
        });

        if (response.status === 201) {
          showPopup("Produto criado com sucesso!", "success");
          await listarProdutos();
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
      const token = localStorage.getItem("token");
      const response = await fetch(`${baseUrl}/produtos`, {
        method: "GET",
        headers: {
          "Authorization": `Bearer ${token}`
        },
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
            const token = localStorage.getItem("token");
            const response = await fetch(`${baseUrl}/produtos/${id}`, {
              method: "DELETE",
              headers: {
                "Authorization": `Bearer ${token}`
              },
            });

            if (response.ok) {
              showPopup("Produto deletado com sucesso!", "success");
              await listarProdutos();
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
    if (event.target === modalEditarProduto) {
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
            const token = localStorage.getItem("token");
            const response = await fetch(`${baseUrl}/produtos/${id}/preco`, {
              method: "PUT",
              headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
              },
              body: JSON.stringify({ preco }),
            });

            if (!response.ok) {
              const errorData = await response.json();
              showPopup(`Erro ao atualizar preço: ${errorData.message || response.statusText}`, "error");
              return;
            }

            showPopup("Preço atualizado com sucesso!", "success");
            await listarProdutos();
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
          const token = localStorage.getItem("token");
          const response = await fetch(`${baseUrl}/produtos/${id}`, {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
              "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify(dadosAtualizados),
          });

          if (!response.ok) {
            const errorData = await response.json();
            showPopup(`Erro ao atualizar produto: ${errorData.message || response.statusText}`, "error");
            return;
          }

          showPopup("Produto atualizado com sucesso!", "success");
          await listarProdutos();
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

      const numero = parseInt(
          document.getElementById("numero-mesa-pagamento").value
      );
      const metodo = document.getElementById("metodo-pagamento").value.trim();
      const valor = parseFloat(
          document.getElementById("valor-pagamento").value
      );

      // Verificar se os campos estão preenchidos corretamente
      if (isNaN(numero) || !metodo || isNaN(valor)) {
        showPopup("Por favor preencha todos os campos corretamente", "info");
        return;
      }

      try {
        const token = localStorage.getItem("token");
        const response = await fetch(`${baseUrl}/pagamentos`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({ metodo, valor, numero }),
        });

        if (response.status === 201) {
          showPopup("Pagamento adicionado com sucesso", "success");
          await listarPagamentos();
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
      const token = localStorage.getItem("token");
      const response = await fetch(`${baseUrl}/pagamentos`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
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
          <p><strong>Número da Mesa:</strong> ${pagamento.numero_mesa}</p>
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
            const token = localStorage.getItem("token");
            const response = await fetch(`${baseUrl}/pagamentos/${id}`, {
              method: "DELETE",
              headers: {
                Authorization: `Bearer ${token}`,
              },
            });

            if (response.ok) {
              showPopup("Pagamento deletado com sucesso!", "success");
              await listarPagamentos();
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

  document.getElementById("adicionar-produto-button").addEventListener("click", async function (e) {
    e.preventDefault();

    const mesaId = document.getElementById("mesa-id").value.trim();
    const produtoId = document.getElementById("produto-id").value.trim();
    const quantidade = document.getElementById("quantidade-produto").value.trim();

    if (!mesaId || !produtoId || !quantidade) {
      showPopup("Por favor, preencha todos os campos.", "info");
      return;
    }

    try {
      const token = localStorage.getItem("token");
      const response = await fetch(`${baseUrl}/produtos-mesa`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          mesa_id: parseInt(mesaId),
          produto_id: parseInt(produtoId),
          quantidade: parseInt(quantidade),
        }),
      });

      if (response.ok) {
        showPopup("Produto adicionado à mesa com sucesso!", "success");
        // Você pode chamar uma função para listar produtos da mesa, se necessário
      } else {
        const errorData = await response.json();
        showPopup(`Erro ao adicionar produto: ${errorData.message || response.statusText}`, "error");
      }
    } catch (error) {
      console.error("Erro ao adicionar produto:", error.message);
      showPopup("Erro ao adicionar produto. Tente novamente.", "error");
    }
  });

// Listener para o botão de "Buscar Produtos"
  document.getElementById("buscar-produtos-button").addEventListener("click", async () => {
    const numeroMesa = document.getElementById("mesa-id-produtos").value.trim();

    // Verifica se o número da mesa foi preenchido
    if (!numeroMesa) {
      showPopup("Por favor, insira o número da mesa.", "error");
      return;
    }

    await listarProdutosMesa(numeroMesa); // Chama a função para listar os produtos da mesa específica
  });

// Função que realiza a requisição para listar os produtos de uma mesa específica
  async function listarProdutosMesa(numeroMesa) {
    console.log(`Buscando produtos da mesa: ${numeroMesa}`);

    try {
      const token = localStorage.getItem("token");

      if (!token) {
        console.error("Token não encontrado.");
        showPopup("Token não encontrado. Faça login novamente.", "error");
        return;
      }

      // Requisição GET para buscar produtos da mesa específica
      const response = await fetch(`${baseUrl}/produtos-mesa/${numeroMesa}`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        console.error("Erro ao buscar produtos da mesa:", errorData.message || response.statusText);
        showPopup(
            `Erro ao listar produtos da mesa: ${errorData.message || response.statusText}`,
            "error"
        );
        return;
      }

      const produtosMesa = await response.json();
      console.log("Produtos encontrados:", produtosMesa);

      const listaProdutosMesa = document.getElementById("lista-produtos-mesa");
      listaProdutosMesa.innerHTML = "";

      if (produtosMesa.data.length === 0) {
        listaProdutosMesa.innerHTML = "<p>Nenhum produto adicionado a esta mesa.</p>";
        return;
      }

      produtosMesa.data.forEach((produto) => {
        const produtoDiv = document.createElement("div");
        produtoDiv.classList.add("item");

        produtoDiv.innerHTML = `
        <p><strong>Produto:</strong> ${produto.nome_produto}</p>
        <p><strong>Preço:</strong> R$${parseFloat(produto.preco_produto).toFixed(2)}</p>
        <p><strong>Quantidade:</strong> ${produto.quantidade}</p>
        <p><strong>Total:</strong> R$${(produto.quantidade * produto.preco_produto).toFixed(2)}</p>
      `;

        listaProdutosMesa.appendChild(produtoDiv);
      });
    } catch (error) {
      console.error("Erro ao listar produtos da mesa:", error.message);
      showPopup("Erro ao listar produtos da mesa. Tente novamente mais tarde.", "error");
    }
  }
  async function listarProdutosParaCompra() {
    try {
      const token = localStorage.getItem("token");

      if (!token) {
        showPopup("Token não encontrado. Faça login novamente.", "error");
        return;
      }

      // Requisição para buscar os produtos disponíveis
      const response = await fetch(`${baseUrl}/produtos`, {
        method: "GET",
        headers: {
          "Authorization": `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json();
        showPopup(`Erro ao listar produtos: ${errorData.message || response.statusText}`, "error");
        return;
      }

      const produtos = await response.json();
      const listaProdutos = document.getElementById("lista-produtos-compra"); // Mudou para 'lista-produtos-compra'

      listaProdutos.innerHTML = ""; // Limpa a lista de produtos antes de repovoá-la

      produtos.data.forEach((produto) => {
        const imagem =
            imagemProdutos[produto.nome] || "./assets/imgProducts/default.jpeg"; // Definir uma imagem padrão se não tiver imagem específica
        const produtoDiv = document.createElement("div");
        produtoDiv.classList.add("item");
        produtoDiv.innerHTML = `
              <img src="${imagem}" alt="${produto.nome}">
              <p><strong>Nome:</strong> ${produto.nome}</p>
              <p><strong>Preço:</strong> R$${parseFloat(produto.preco).toFixed(2)}</p>
              
              <!-- Dropdown para selecionar a quantidade -->
              <label for="quantidade-${produto.id}"><strong>Quantidade:</strong></label>
              <select id="quantidade-${produto.id}" class="quantidade-produto">
                <!-- Vamos criar opções de 1 até 10, mas isso pode ser ajustado -->
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
              </select>
              
              <button class="adicionar-produto" data-id="${produto.id}" data-nome="${produto.nome}" data-preco="${produto.preco}">Adicionar à Mesa</button>
            `;
        listaProdutos.appendChild(produtoDiv);
      });

      // Eventos para adicionar o produto à mesa
      document.querySelectorAll(".adicionar-produto").forEach((button) => {
        button.addEventListener("click", function () {
          const idProduto = this.getAttribute("data-id");
          const nomeProduto = this.getAttribute("data-nome");
          const precoProduto = this.getAttribute("data-preco");
          const quantidade = document.getElementById(`quantidade-${idProduto}`).value;

          if (quantidade < 1) {
            showPopup("Por favor, insira uma quantidade válida.", "error");
            return;
          }

          // Chama a função para adicionar o produto à mesa
          adicionarProdutoMesa(idProduto, quantidade);
        });
      });

    } catch (error) {
      console.error("Erro ao listar produtos:", error.message);
      showPopup("Erro ao listar produtos. Tente novamente mais tarde.", "error");
    }
  }

  function mostrarSecao(sectionId) {
    document.querySelectorAll(".section").forEach((section) => {
      section.classList.remove("active");
    });
    document.getElementById(sectionId).classList.add("active");

    // Chamar a função de listagem correspondente
    if (sectionId === "home") {
      listarProdutosHome();
    } else if (sectionId === "usuarios") {
      listarUsuarios();
    } else if (sectionId === "mesas") {
      listarMesas();
    } else if (sectionId === "produtos") {
      listarProdutos();
    } else if (sectionId === "pagamentos") {
      listarPagamentos();
    } else if (sectionId === "produtos-mesa") {
      listarProdutosMesa();
    } else if (sectionId === "adicionar-produto") {
      listarProdutosParaCompra();
    }
  }
});
