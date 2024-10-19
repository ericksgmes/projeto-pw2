const base = "http://localhost/~aluno/webservice";

async function carregarUsuarios() {
   
    console.log(`${base}/usuario.php`);
    let resultado = await fetch(`${base}/usuario.php`);
    if(!resultado.ok) {
        console.log("Erro ao carregar usuários");
        return;
    }
    let json = await resultado.json();

    let tbody = document.querySelector("tbody");
    tbody.innerHTML = "";
    for(let usuario of json) {
        let tr = document.createElement("tr");
        let td1 = document.createElement("td");
        td1.innerText = usuario.id;
        let td2 = document.createElement("td");
        td2.innerText = usuario.nome;
        let td3 = document.createElement("td");
        td3.innerText = usuario.data_nascimento;
        tr.append(td1);
        tr.append(td2);
        tr.append(td3);
        tbody.append(tr);
    }
}

async function cadastrarUsuario(e) {
    e.preventDefault();

    let nome = document.querySelector("[name=nome]").value;
    let data = document.querySelector("[name=data]").value;

    const config = {
        method: "POST",
        body: JSON.stringify({
            nome: nome,
            data_nascimento: data
        })
    }    

    let resultado = await fetch(`${base}/usuario.php`, config);
    if(!resultado.ok) {
        console.log("Erro ao cadastrar usuário")
        return;
    }
    let json = await resultado.json();
    alert(json.msg);
    carregarUsuarios();
}

carregarUsuarios();

let form = document.querySelector("form");
form.addEventListener("submit", cadastrarUsuario);