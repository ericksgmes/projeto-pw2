const baseUrl = "http://localhost/restaurante-webservice";

document.addEventListener("DOMContentLoaded", function () {
  listarProdutos(); // Lista todos os produtos ao carregar a página

  document.getElementById("form-produto").addEventListener("submit", function (e) {
    e.preventDefault();

    const nome = document.getElementById("nome-produto").value.trim();
    const preco = parseFloat(document.getElementById("preco-produto").value);

    if (!nome || isNaN(preco) || preco <= 0) {
      alert("Por favor, preencha os campos corretamente!");
      return;
    }

    criarProduto(nome, preco);
    document.getElementById("form-produto").reset();
  });
});

async function criarProduto(nome, preco) {
  try {
    const response = await fetch(baseUrl + "/produtos", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        nome: nome,
        preco: preco
      })
    });

    if (response.ok) {
      alert("Produto adicionado com sucesso!");
      await listarProdutos(); // Atualiza a lista de produtos após adicionar um novo
    } else {
      const errorData = await response.json();
      alert(`Erro ao adicionar produto: ${errorData.message || response.statusText}`);
    }
  } catch (error) {
    console.error("Erro ao adicionar produto:", error.message);
  }
}

async function listarProdutos() {
  try {
    const response = await fetch(baseUrl + "/produtos", {
      method: "GET",
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error(`Erro ao listar produtos: ${errorData.message || response.statusText}`);
      alert(`Erro ao listar produtos: ${errorData.message || response.statusText}`);
      return;
    }

    const produtosResponse = await response.json();
    console.log("Produtos recebidos:", produtosResponse); // Adiciona log para verificar a resposta

    if (produtosResponse.status !== 'success' || !Array.isArray(produtosResponse.data)) {
      console.error("Formato inesperado na resposta da API.");
      return;
    }

    const produtos = produtosResponse.data; // Acessa a lista de produtos dentro do campo 'data'

    // Limpa a seção antes de listar produtos para evitar duplicação
    const produtosLista = document.getElementById("produtos-lista");

    if (!produtosLista) {
      console.error("Elemento com id 'produtos-lista' não encontrado no DOM.");
      return;
    }

    produtosLista.innerHTML = ""; // Limpa os produtos anteriores

    produtos.forEach(produto => {
      console.log("Adicionando produto:", produto); // Log para cada produto sendo adicionado

      const preco = parseFloat(produto.preco); // Converte o preço de string para número

      // Verifica se o valor é numérico antes de adicionar à lista
      if (isNaN(preco)) {
        console.error("Preço inválido para o produto:", produto);
        return;
      }

      const produtoDiv = document.createElement("div");
      produtoDiv.classList.add("card");

      produtoDiv.innerHTML = `
        <div class="description">
          <div class="item">${produto.nome}</div>
          <div class="price">R$${preco.toFixed(2)}</div>
        </div>
      `;

      produtosLista.appendChild(produtoDiv); // Adiciona cada produto à lista
    });

  } catch (e) {
    console.error("Erro ao listar produtos:", e.message);
  }
}
