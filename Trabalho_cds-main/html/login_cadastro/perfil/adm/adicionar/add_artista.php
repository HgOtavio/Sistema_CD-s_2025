<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Buscar CDs existentes
$sql_cd = "SELECT id_cd, titulo FROM CD";
$result_cd = $conn->query($sql_cd);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Artista</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../../js/mascaras/mascara_data.js" defer></script>
</head>
<body>

    <!-- Cabeçalho da página (com user logado) -->
    <header> 

        <div id="parte_de_cima_cab">

              <!-- Logo da página -->
              <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Barra de pesquisa -->

            <div id="login_carrinho"> <!-- Conta e Carrinho -->
                    <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Imagem de perfil -->

                <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>
    </header>

    <main>
         
            <!-- Seção de Adicionar Artista -->
            <section id="adicionar">
            <form action="processar_artista.php" method="post" enctype="multipart/form-data">

                <!-- Título principal da página -->
                <h1 id="titulo">Adicionar Novo Artista</h1>
                
                <!-- Div que contém os campos de entrada do formulário -->
                <div id="inputs">
                    
                    <!-- Div para os dados do artista-->
                    <div id="cima">
                        <div class="separacao">
                            <!-- Subtítulo para a seção de dados do artista -->
                            <h1 class="subtitulo" id="acesso">Dados do artista:</h1>
                            
                            <!-- Campos de entrada -->
                            <input type="text" placeholder="Nome" class="input"  name="nomeArtista" required>
                            <div>
                                <label for="upload" class="input add_perfil_img">Adicionar foto do Artista</label>
                                <input type="file" id="upload" name="fotoPerfil"  hidden accept="image/*">
                            </div>
                        </div>
                        
                        <!-- Div para os dados do artista -->
                        <div class="separacao" id="direita">
                            
                            <!-- Campos de entrada -->
                            <input type="text" placeholder="Data de Nascimento" maxlength="20" class="input data" name="dataNascimento"  required>
                            <input type="text" placeholder="Descrição" class="input" name="descricao" required>
                            <input type="text" id="cdInput" placeholder="Digite para buscar CDs" onkeyup="searchCDs()">
    <ul id="cdSuggestions" style="list-style: none; padding: 0; margin: 0;"></ul>

                            
                            <ul id="selectedCDs"></ul>
    <input type="hidden" name="cdsSelecionados" id="cdsSelecionados">

                        </div>
                        <!-- Botão de Adicionar -->
                        <button  type="submit" id="button">Adicionar</button>
                    </div>
                </div>
            </form>
            </section>
    </main>

    <!-- Seção de imagens à direita -->
    <div id="imgs_direita">
        <!-- Imagens decorativas à direita -->
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e2"></div>
    </div>

    <!-- Seção de imagens à esquerda -->
    <div id="imgs_esquerda">
        <!-- Imagens decorativas à esquerda -->
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d2">
    </div>

    <script>
    let selectedCDs = [];

    function searchCDs() {
        let query = document.getElementById('cdInput').value;

        if (query.length > 0) {
            fetch("search_cds.php?query=" + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    let suggestionsList = document.getElementById('cdSuggestions');
                    suggestionsList.innerHTML = '';
                    data.forEach(cd => {
                        let li = document.createElement('li');
                        li.textContent = cd.titulo;
                        li.style.cursor = 'pointer';
                        li.onclick = function() {
                            if (!selectedCDs.some(item => item.id_cd == cd.id_cd)) {
                                selectedCDs.push(cd);
                                updateSelectedCDs();
                            }
                            document.getElementById('cdInput').value = '';
                            suggestionsList.innerHTML = '';
                        };
                        suggestionsList.appendChild(li);
                    });
                });
        } else {
            document.getElementById('cdSuggestions').innerHTML = '';
        }
    }

    function updateSelectedCDs() {
        const ul = document.getElementById('selectedCDs');
        const hiddenInput = document.getElementById('cdsSelecionados');
        ul.innerHTML = '';
        selectedCDs.forEach(cd => {
            const li = document.createElement('li');
            li.textContent = cd.titulo;
            ul.appendChild(li);
        });
        hiddenInput.value = selectedCDs.map(cd => cd.id_cd).join(',');
    }
</script>


</body>
</html>