function showDetails(item) {
  document.getElementById('modal').style.display = 'block';
  document.getElementById('modal-content').innerHTML = `
      <span class="close" onclick="closeModal()">×</span>
      <h2>${item}</h2>
      <img src="https://via.placeholder.com/150" alt="${item}">
      <p>Descrição detalhada do ${item}.</p>
      <p>Preço: R$15,00</p>
      <p>Qual o ponto da carne?</p>
      <select>
          <option>Mal Passado</option>
          <option>Ao Ponto</option>
          <option>Bem Passado</option>
      </select>
      <p>Gostaria de remover algo?</p>
      <input type="text" placeholder="Escreva aqui...">
      <button>Adicionar ao Carrinho</button>
  `;
}

function closeModal() {
  document.getElementById('modal').style.display = 'none';
}

document.getElementById('login-btn').addEventListener('click', function() {
  window.location.href = 'login.html';
});
