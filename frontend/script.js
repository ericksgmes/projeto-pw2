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


    const loginSection = document.getElementById("login-section");
    const mainContent = document.getElementById("main-content");
    const logoutButton = document.getElementById("logout-button");
    const goToRegister = document.getElementById("go-to-register");
    const goToLogin = document.getElementById("go-to-login");
    const registerSection = document.getElementById("register-section");


    document.querySelectorAll(".nav-link").forEach((link) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            const sectionId = link.getAttribute("href").substring(1);
            mostrarSecao(sectionId);
        });
    });


    document
        .getElementById("login-form")
        .addEventListener("submit", async function (e) {
            e.preventDefault();

            const username = document.getElementById("username-login").value.trim();
            const senha = document.getElementById("senha-login").value.trim();

            try {

                const response = await fetch(`${baseUrl}/auth`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({username, senha}),
                });

                const result = await response.json();

                if (response.ok) {

                    localStorage.setItem("token", result.data.token);

                    showPopup("Login bem-sucedido!", "success");
                    loginSection.style.display = "none";
                    mainContent.style.display = "block";
                    await exibirUsuarioLogado();
                    mostrarSecao("home");

                    hide();
                } else {

                    showPopup(result.message || "Erro no login.", "error");
                }
            } catch (error) {
                console.error("Erro ao tentar fazer login:", error.message);
                showPopup("Erro ao tentar fazer login. Tente novamente.", "error");
            }
        });


    document
        .getElementById("register-form")
        .addEventListener("submit", async function (e) {
            e.preventDefault();


            const nome = document.getElementById("register-name").value.trim();
            const username = document
                .getElementById("register-username")
                .value.trim();
            const senha = document.getElementById("register-password").value.trim();
            const confirmSenha = document
                .getElementById("register-confirm-password")
                .value.trim();


            if (!nome || !username || !senha || !confirmSenha) {
                showPopup("Todos os campos são obrigatórios.", "error");
                return;
            }

            if (senha !== confirmSenha) {
                showPopup("As senhas não coincidem. Tente novamente.", "error");
                return;
            }

            try {

                const response = await fetch(`${baseUrl}/signup`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({nome, username, senha}),
                });

                if (response.status === 201) {
                    showPopup(
                        "Cadastro realizado com sucesso! Faça login para continuar.",
                        "success"
                    );

                    document.getElementById("register-form").reset();
                    registerSection.style.display = "none";
                    loginSection.style.display = "block";
                }
            } catch (error) {
                console.error("Erro ao cadastrar usuário:", error.message);
                showPopup("Erro ao cadastrar usuário. Tente novamente.", "error");
            }
        });

    logoutButton.addEventListener("click", function () {
        showConfirm(
            "Tem certeza que deseja sair?",
            () => {

                localStorage.removeItem("token");
                document.getElementById("main-content").style.display = "none";
                document.getElementById("login-section").style.display = "block";
                document.getElementById("login-form").reset();
                document.getElementById("login-error").style.display = "none";
                document.querySelectorAll(".restricted").forEach((link) => {
                    link.style.display = "none";
                });


                location.reload();

                showPopup("Logout realizado com sucesso!", "success");
            },
            () => {

                showPopup("Logout cancelado!", "info");
            }
        );
    });


    goToRegister.addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("login-section").style.display = "none";
        document.getElementById("register-section").style.display = "block";
    });


    goToLogin.addEventListener("click", function (e) {
        e.preventDefault();
        document.getElementById("register-section").style.display = "none";
        document.getElementById("login-section").style.display = "block";
    });


    async function listarProdutosHome() {
        try {
            const token = localStorage.getItem("token");
            const response = await fetch(`${baseUrl}/produtos`, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                showPopup(
                    `Erro ao listar produtos: ${
                        errorData.message || response.statusText
                    }`,
                    "error"
                );
                return;
            }

            const produtos = await response.json();
            const listaProdutosHome = document.getElementById("lista-produtos-home");
            listaProdutosHome.innerHTML = "";

            produtos.data.forEach((produto) => {
                const produtoDiv = document.createElement("div");
                produtoDiv.classList.add("item");


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


            const decodedToken = JSON.parse(atob(token.split(".")[1]));
            const nome = decodedToken.nome;
            const username = decodedToken.username;


            document.getElementById("usuario-logado-nome").textContent = nome;
            document.getElementById(
                "usuario-logado-username"
            ).textContent = `@${username}`;
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


        inputId.value = id;
        inputNome.value = nome;
        inputUsername.value = username;
        inputSenha.value = "";
        selectIsAdmin.value = is_admin;


        inputNome.setAttribute("data-original-nome", nome);
        inputUsername.setAttribute("data-original-username", username);
        selectIsAdmin.setAttribute("data-original-is_admin", is_admin);


        modal.style.display = "block";


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
                    `Erro ao listar usuários: ${
                        errorData.message || response.statusText
                    }`,
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
          <button class="deletar-usuario" data-id="${
                    usuario.id
                }">Deletar</button>
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


            document.querySelectorAll(".editar-usuario").forEach((button) => {
                button.addEventListener("click", function () {
                    const id = this.getAttribute("data-id");
                    const nome = this.getAttribute("data-nome");
                    const username = this.getAttribute("data-username");
                    const is_admin = parseInt(this.getAttribute("data-is_admin"), 10);

                    console.log("Dados capturados para edição:", {
                        id,
                        nome,
                        username,
                        is_admin,
                    });

                    abrirModalEditarUsuario(id, nome, username, is_admin);
                });
            });


            document.querySelectorAll(".deletar-usuario").forEach((button) => {
                button.addEventListener("click", async function () {
                    const id = this.getAttribute("data-id");
                    await deletarUsuario(id);
                });
            });
        } catch (error) {
            console.error("Erro ao listar usuários:", error.message);
            showPopup(
                "Erro ao listar usuários. Tente novamente mais tarde.",
                "error"
            );
        }
    }

    document
        .getElementById("form-editar-usuario")
        .addEventListener("submit", async function (e) {
            e.preventDefault();

            const id = document.getElementById("editar-id-usuario").value;
            const nome = document.getElementById("editar-nome-usuario").value.trim();
            const username = document
                .getElementById("editar-username-usuario")
                .value.trim();
            const senha = document
                .getElementById("editar-senha-usuario")
                .value.trim();
            const is_admin = parseInt(
                document.getElementById("editar-is-admin-usuario").value,
                10
            );
            const token = localStorage.getItem("token");


            const nomeOriginal = document
                .getElementById("editar-nome-usuario")
                .getAttribute("data-original-nome");
            const usernameOriginal = document
                .getElementById("editar-username-usuario")
                .getAttribute("data-original-username");
            const is_adminOriginal = parseInt(
                document
                    .getElementById("editar-is-admin-usuario")
                    .getAttribute("data-original-is_admin"),
                10
            );


            const payload = {};
            if (nome && nome !== nomeOriginal) payload.nome = nome;
            if (username && username !== usernameOriginal)
                payload.username = username;
            if (senha) payload.novaSenha = senha;
            if (is_admin !== is_adminOriginal) payload.is_admin = is_admin;


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
                    showPopup(
                        `Erro ao atualizar usuário: ${
                            errorData.message || response.statusText
                        }`,
                        "error"
                    );
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
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify({numero}),
                });

                if (response.status === 201) {
                    showPopup("Mesa criada com sucesso!", "success");
                    await listarMesas();
                    e.target.reset();
                } else {
                    const errorData = await response.json();
                    showPopup(
                        `Erro ao criar mesa: ${errorData.message || response.statusText}`,
                        "error"
                    );
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
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                showPopup(
                    `Erro ao listar mesas: ${errorData.message || response.statusText}`,
                    "error"
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
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    if (response.ok) {
                        showPopup("Mesa deletada com sucesso!", "success");
                        await listarMesas();
                    } else {
                        const errorData = await response.json();
                        showPopup(
                            `Erro ao deletar mesa: ${
                                errorData.message || response.statusText
                            }`,
                            "error"
                        );
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
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify({nome, preco}),
                });

                if (response.status === 201) {
                    showPopup("Produto criado com sucesso!", "success");
                    await listarProdutos();
                    e.target.reset();
                } else {
                    const errorData = await response.json();
                    showPopup(
                        `Erro ao criar produto: ${
                            errorData.message || response.statusText
                        }`,
                        "error"
                    );
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
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                showPopup(
                    `Erro ao listar produtos: ${
                        errorData.message || response.statusText
                    }`,
                    "error"
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
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    if (response.ok) {
                        showPopup("Produto deletado com sucesso!", "success");
                        await listarProdutos();
                    } else {
                        const errorData = await response.json();
                        showPopup(
                            `Erro ao deletar produto: ${
                                errorData.message || response.statusText
                            }`,
                            "error"
                        );
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
                            Authorization: `Bearer ${token}`,
                        },
                        body: JSON.stringify({preco}),
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        showPopup(
                            `Erro ao atualizar preço: ${
                                errorData.message || response.statusText
                            }`,
                            "error"
                        );
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
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify(dadosAtualizados),
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    showPopup(
                        `Erro ao atualizar produto: ${
                            errorData.message || response.statusText
                        }`,
                        "error"
                    );
                    return;
                }

                showPopup("Produto atualizado com sucesso!", "success");
                await listarProdutos();
                modalEditarProduto.style.display = "none";
            } catch (error) {
                console.error("Erro ao atualizar produto:", error.message);
            }
        });


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
                    body: JSON.stringify({metodo, valor, numero}),
                });

                if (response.status === 201) {
                    showPopup("Pagamento adicionado com sucesso", "success");
                    await listarPagamentos();
                    e.target.reset();
                } else {
                    const errorData = await response.json();
                    showPopup(
                        `Erro ao adicionar pagamentos: ${
                            errorData.message || response.statusText
                        }`,
                        "error"
                    );
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
                showPopup(
                    `Erro ao listar pagamentos: ${
                        errorData.message || response.statusText
                    }`,
                    "error"
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
          <p><strong>Número da Mesa:</strong> ${pagamento.numero_mesa}</p>
          <div class="action-buttons">
            <button class="deletar-pagamento" data-id="${
                    pagamento.id
                }">Deletar</button>
          </div>
        `;
                listaPagamentos.appendChild(pagamentoDiv);
            });


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
                        showPopup(
                            `Erro ao deletar pagamento: ${
                                errorData.message || response.statusText
                            }`,
                            "error"
                        );
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


    loginSection.style.display = "block";
    mainContent.style.display = "none";

    function showConfirm(message, onConfirm, onCancel) {

        const confirmContainer = document.createElement("div");
        confirmContainer.className = "confirm-container";


        const confirmBox = document.createElement("div");
        confirmBox.className = "confirm-box";
        confirmBox.innerHTML = `
    <p>${message}</p>
    <div class="confirm-buttons">
      <button id="confirm-yes">Sim</button>
      <button id="confirm-no">Não</button>
    </div>
  `;


        confirmContainer.appendChild(confirmBox);
        document.body.appendChild(confirmContainer);


        document.getElementById("confirm-yes").onclick = () => {
            document.body.removeChild(confirmContainer);
            if (onConfirm) onConfirm();
        };


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

    document
        .getElementById("adicionar-produto-button")
        .addEventListener("click", async function (e) {
            e.preventDefault();

            const mesaId = document.getElementById("mesa-id").value.trim();
            const produtoId = document.getElementById("produto-id").value.trim();
            const quantidade = document
                .getElementById("quantidade-produto")
                .value.trim();

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

                } else {
                    const errorData = await response.json();
                    showPopup(
                        `Erro ao adicionar produto: ${
                            errorData.message || response.statusText
                        }`,
                        "error"
                    );
                }
            } catch (error) {
                console.error("Erro ao adicionar produto:", error.message);
                showPopup("Erro ao adicionar produto. Tente novamente.", "error");
            }
        });


    document
        .getElementById("buscar-produtos-button")
        .addEventListener("click", async () => {
            const numeroMesa = document
                .getElementById("mesa-id-produtos")
                .value.trim();


            if (!numeroMesa) {
                showPopup("Por favor, insira o número da mesa.", "error");
                return;
            }

            await listarProdutosMesa(numeroMesa);
        });

    let carrinho = [];
    document
        .getElementById("btn-exibir-carrinho")
        .addEventListener("click", () => {
            const modal = document.getElementById("modal-carrinho");
            const carrinhoLista = document.getElementById("carrinho-lista");

            carrinhoLista.innerHTML = "";

            carrinho.forEach((produto) => {
                const item = document.createElement("li");
                item.textContent = `${produto.nome} - Quantidade: ${produto.quantidade}`;
                carrinhoLista.appendChild(item);
            });


            modal.style.display = "block";
        });


    document
        .getElementById("fechar-modal-carrinho")
        .addEventListener("click", () => {
            document.getElementById("modal-carrinho").style.display = "none";
        });

    function adicionarAoCarrinho(idProduto, nomeProduto, precoProduto, quantidade) {
        const quantidadeInt = Math.max(1, parseInt(quantidade, 10));

        const produtoExistente = carrinho.find((item) => item.id === idProduto);

        if (produtoExistente) {
            produtoExistente.quantidade += quantidadeInt;
        } else {
            carrinho.push({
                id: idProduto,
                nome: nomeProduto,
                preco: precoProduto,
                quantidade: quantidadeInt,
            });
        }

        // Atualiza o carrinho renderizado e o badge
        renderizarCarrinho();
        atualizarBadgeCarrinho();

        // Exibe o pop-up de confirmação
        showPopup(
            `O produto <strong>${nomeProduto}</strong> foi adicionado ao carrinho com sucesso!`,
            "success"
        );
    }

    function atualizarBadgeCarrinho() {
        const carrinhoBadge = document.getElementById("carrinho-badge");

        if (!carrinhoBadge) {
            console.error("Elemento do badge do carrinho não encontrado!");
            return;
        }

        // Soma as quantidades de itens no carrinho
        const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);

        // Atualiza o texto do badge
        carrinhoBadge.textContent = totalItens;

        // Exibe ou esconde o badge dependendo do total de itens
        if (totalItens === 0) {
            carrinhoBadge.style.visibility = "hidden";
        } else {
            carrinhoBadge.style.visibility = "visible";
        }
    }

    async function listarProdutosMesa(numeroMesa) {
        console.log(`Buscando produtos da mesa: ${numeroMesa}`);

        try {
            const token = localStorage.getItem("token");

            if (!token) {
                console.error("Token não encontrado.");
                showPopup("Token não encontrado. Faça login novamente.", "error");
                return;
            }


            const response = await fetch(`${baseUrl}/produtos-mesa/${numeroMesa}`, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error(
                    "Erro ao buscar produtos da mesa:",
                    errorData.message || response.statusText
                );
                showPopup(
                    `Erro ao listar produtos da mesa: ${
                        errorData.message || response.statusText
                    }`,
                    "error"
                );
                return;
            }

            const produtosMesa = await response.json();
            console.log("Produtos encontrados:", produtosMesa);

            const listaProdutosMesa = document.getElementById("lista-produtos-mesa");
            listaProdutosMesa.innerHTML = "";

            if (produtosMesa.data.length === 0) {
                listaProdutosMesa.innerHTML =
                    "<p>Nenhum produto adicionado a esta mesa.</p>";
                return;
            }

            produtosMesa.data.forEach((produto) => {
                const produtoDiv = document.createElement("div");
                produtoDiv.classList.add("item");

                produtoDiv.innerHTML = `
                <p><strong>Produto:</strong> ${produto.nome_produto}</p>
                <p><strong>Preço:</strong> R$${parseFloat(
                    produto.preco_produto
                ).toFixed(2)}</p>
                <p><strong>Quantidade:</strong> ${produto.quantidade}</p>
                <p><strong>Total:</strong> R$${(
                    produto.quantidade * produto.preco_produto
                ).toFixed(2)}</p>
                <!-- Botões para editar e deletar -->
                <button class="editar-produto" data-id="${
                    produto.id
                }" data-nome="${produto.nome_produto}" data-quantidade="${
                    produto.quantidade
                }">Editar Quantidade</button>
                <button class="deletar-produto" data-id="${
                    produto.id
                }">Deletar</button>
            `;

                listaProdutosMesa.appendChild(produtoDiv);
            });


            document.querySelectorAll(".editar-produto").forEach((button) => {
                button.addEventListener("click", function () {
                    const idProduto = this.getAttribute("data-id");
                    const nomeProduto = this.getAttribute("data-nome");
                    const quantidadeProduto = this.getAttribute("data-quantidade");

                    console.log("Editar produto com ID:", idProduto);


                    editarProduto(idProduto, nomeProduto, quantidadeProduto);
                });
            });


            document.querySelectorAll(".deletar-produto").forEach((button) => {
                button.addEventListener("click", async function () {
                    const idProduto = this.getAttribute("data-id");
                    const numeroMesa = document
                        .getElementById("mesa-id-produtos")
                        .value.trim();

                    console.log("Deletar produto com ID:", idProduto, "da mesa:", numeroMesa);

                    await deletarProdutoMesa(idProduto, numeroMesa);
                });
            });
        } catch (error) {
            console.error("Erro ao listar produtos da mesa:", error.message);
            showPopup(
                "Erro ao listar produtos da mesa. Tente novamente mais tarde.",
                "error"
            );
        }
    }


    async function deletarProdutoMesa(idProduto, numeroMesa) {
        try {
            const token = localStorage.getItem("token");

            const response = await fetch(`${baseUrl}/produtos-mesa/${idProduto}`, {
                method: "DELETE",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error(
                    "Erro ao deletar produto:",
                    errorData.message || response.statusText
                );
                showPopup(
                    `Erro ao deletar produto: ${
                        errorData.message || response.statusText
                    }`,
                    "error"
                );
                return;
            }

            console.log("Produto deletado com sucesso");
            showPopup("Produto removido da mesa com sucesso.", "success");

            await listarProdutosMesa(numeroMesa);
        } catch (error) {
            console.error("Erro ao deletar produto:", error.message);
            showPopup("Erro ao deletar produto. Tente novamente.", "error");
        }
    }


    async function listarProdutosParaCompra() {
        try {
            const token = localStorage.getItem("token");

            if (!token) {
                showPopup("Token não encontrado. Faça login novamente.", "error");
                return;
            }


            const response = await fetch(`${baseUrl}/produtos`, {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                showPopup(
                    `Erro ao listar produtos: ${
                        errorData.message || response.statusText
                    }`,
                    "error"
                );
                return;
            }

            const produtos = await response.json();
            const listaProdutos = document.getElementById("lista-produtos-compra");

            listaProdutos.innerHTML = "";

            produtos.data.forEach((produto) => {
                const produtoDiv = document.createElement("div");
                produtoDiv.classList.add("item");
                produtoDiv.innerHTML = `
        <p><strong>Nome:</strong> ${produto.nome}</p>
        <p><strong>Preço:</strong> R$${parseFloat(produto.preco).toFixed(2)}</p>
        <label for="quantidade-${
                    produto.id
                }"><strong>Quantidade:</strong></label>
        <select id="quantidade-${produto.id}">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>
        <button class="adicionar-produto" data-id="${produto.id}" data-nome="${
                    produto.nome
                }" data-preco="${produto.preco}">Adicionar ao Carrinho</button>
      `;
                listaProdutos.appendChild(produtoDiv);
            });


            document.querySelectorAll(".adicionar-produto").forEach((button) => {
                button.addEventListener("click", function () {
                    const idProduto = this.getAttribute("data-id");
                    const nomeProduto = this.getAttribute("data-nome");
                    const precoProduto = this.getAttribute("data-preco");
                    const quantidade = document.getElementById(
                        `quantidade-${idProduto}`
                    ).value;

                    if (quantidade < 1) {
                        showPopup("Por favor, insira uma quantidade válida.", "error");
                        return;
                    }


                    adicionarAoCarrinho(idProduto, nomeProduto, precoProduto, quantidade);
                });
            });
        } catch (error) {
            console.error("Erro ao listar produtos:", error.message);
            showPopup(
                "Erro ao listar produtos. Tente novamente mais tarde.",
                "error"
            );
        }
    }


    function renderizarCarrinho() {
        const carrinhoLista = document.getElementById("carrinho-lista");
        carrinhoLista.innerHTML = "";

        if (carrinho.length === 0) {
            carrinhoLista.innerHTML = "<p>Seu carrinho está vazio.</p>";
            return;
        }

        carrinho.forEach((produto) => {
            const itemCarrinho = document.createElement("li");
            itemCarrinho.innerHTML = `
      <strong>${produto.nome}</strong> | Quantidade: ${produto.quantidade} | Preço: R$${(produto.preco * produto.quantidade).toFixed(2)}
      <button class="remover-produto-carrinho" data-id="${produto.id}">Remover</button>
    `;


            carrinhoLista.appendChild(itemCarrinho);


            const removerBtn = itemCarrinho.querySelector(".remover-produto-carrinho");
            removerBtn.addEventListener("click", () => {

                removerDoCarrinho(produto.id);

                renderizarCarrinho();
            });
        });
    }


    function removerDoCarrinho(idProduto) {
        carrinho = carrinho.filter((produto) => produto.id !== idProduto);
        renderizarCarrinho();
    }


    document
        .getElementById("finalizar-pedido")
        .addEventListener("click", async function () {
            const numeroMesa = document.getElementById("numero-mesa-modal").value;

            if (!numeroMesa) {
                showPopup("Por favor, insira o número da mesa.", "error");
                return;
            }


            const produtosPedido = carrinho.map((item) => ({
                id_prod: item.id,
                quantidade: item.quantidade,
            }));

            try {
                const token = localStorage.getItem("token");

                const response = await fetch(`${baseUrl}/produtos-mesa`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify({
                        numero_mesa: numeroMesa,
                        produtos: produtosPedido,
                    }),
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    showPopup(
                        `Erro ao fazer pedido: ${errorData.message || response.statusText}`,
                        "error"
                    );
                    return;
                }

                await response.json();
                showPopup("Pedido realizado com sucesso!", "success");


                carrinho = [];
                renderizarCarrinho();


                fecharModalCarrinho();
            } catch (error) {
                console.error("Erro ao finalizar pedido:", error.message);
                showPopup("Erro ao finalizar pedido. Tente novamente.", "error");
            }
        });


    function abrirModalCarrinho() {
        const modal = document.getElementById("modal-carrinho");
        modal.style.display = "block";
    }


    function fecharModalCarrinho() {
        const modal = document.getElementById("modal-carrinho");
        modal.style.display = "none";
    }

    document
        .getElementById("btn-exibir-carrinho")
        .addEventListener("click", abrirModalCarrinho);


    document
        .getElementById("fechar-modal-carrinho")
        .addEventListener("click", fecharModalCarrinho);


    window.addEventListener("click", function (event) {
        const modal = document.getElementById("modal-carrinho");
        if (event.target === modal) {
            fecharModalCarrinho();
        }
    });


    function editarProduto(idProduto, nomeProduto, quantidadeProduto) {

        document.getElementById("editar-id-produto-id").value = idProduto;
        document.getElementById("editar-nome-produto-id").value = nomeProduto;
        document.getElementById("editar-quantidade-produto-id").value =
            quantidadeProduto;


        const modal = document.getElementById("modal-editar-produto-id");
        modal.style.display = "block";
    }

    document
        .getElementById("form-editar-produto-id")
        .addEventListener("submit", async function (e) {
            e.preventDefault();

            const idProduto = document.getElementById("editar-id-produto-id").value;
            const quantidadeProduto = document.getElementById(
                "editar-quantidade-produto-id"
            ).value;
            const numeroMesa = document
                .getElementById("mesa-id-produtos")
                .value.trim();

            if (!numeroMesa) {
                showPopup("Número da mesa não encontrado. Tente novamente.", "error");
                return;
            }

            if (quantidadeProduto <= 0) {
                showPopup("A quantidade deve ser maior que zero.", "error");
                return;
            }

            try {
                const token = localStorage.getItem("token");
                const response = await fetch(`${baseUrl}/produtos-mesa/${idProduto}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify({quantidade: quantidadeProduto}),
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    showPopup(
                        `Erro ao editar quantidade: ${
                            errorData.message || response.statusText
                        }`,
                        "error"
                    );
                    return;
                }

                showPopup("Quantidade do produto atualizada com sucesso.", "success");
                fecharModalEditarProduto();

                await listarProdutosMesa(numeroMesa);
            } catch (error) {
                console.error("Erro ao editar quantidade do produto:", error.message);
                showPopup("Erro ao editar produto. Tente novamente.", "error");
            }
        });

    document
        .getElementById("fechar-modal-editar-produto-id")
        .addEventListener("click", function () {
            const modal = document.getElementById("modal-editar-produto-id");
            modal.style.display = "none";
        });


    window.addEventListener("click", function (event) {
        const modal = document.getElementById("modal-editar-produto");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    function hide() {
        const token = localStorage.getItem("token");

        if (token) {
            try {
                const decodedToken = jwt_decode(token);

                const isAdmin = decodedToken.is_admin;

                const restrictedLinks = document.querySelectorAll("nav .restricted");

                restrictedLinks.forEach(link => {
                    if (!isAdmin) {

                        link.style.display = "none";
                    }
                });


                const currentSection = window.location.hash.replace("#", "");
                if (!isAdmin && ["usuarios", "mesas", "produtos", "pagamentos", "produtos-mesa"].includes(currentSection)) {
                    window.location.href = "403.html";
                }
            } catch (e) {
                console.error("Erro ao decodificar o token:", e);
            }
        }
    }

    function mostrarSecao(sectionId) {
        const token = localStorage.getItem("token");

        if (token) {
            try {
                const decodedToken = jwt_decode(token);

                const isAdmin = decodedToken.is_admin;


                if (!isAdmin && (sectionId === "usuarios" || sectionId === "produtos" || sectionId === "pagamentos" || sectionId === "mesas" || sectionId === "produtos-mesa")) {

                    window.location.href = "403.html";
                    return;
                }
            } catch (e) {
                console.error("Erro ao decodificar o token:", e);
            }
        }

        document.querySelectorAll(".section").forEach((section) => {
            section.classList.remove("active");
        });
        document.getElementById(sectionId).classList.add("active");

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
        } else if (sectionId === "adicionar-produto") {
            listarProdutosParaCompra();
        }
    }

});
