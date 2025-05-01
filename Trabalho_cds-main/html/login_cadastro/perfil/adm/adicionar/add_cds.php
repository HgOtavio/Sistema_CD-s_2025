<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Buscar artistas e músicas existentes
$sql_artistas = "SELECT id_artista, nomeArtista FROM Artista";
$result_artistas = $conn->query($sql_artistas);

$sql_musicas = "SELECT id_musica, nomeMusica FROM Musica";
$result_musicas = $conn->query($sql_musicas);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar CDs</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css">
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit_cds.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../../js/adm/add_alt_cds.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_ano.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_num.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_preco.js" defer></script>
</head>
<body>

    <!-- Cabeçalho da página (com user logado) -->
    <header> 

        <div id="parte_de_cima_cab">

              <!-- Logo da página -->
              <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            

            <div id="login_carrinho"> <!-- Conta e Carrinho -->
                    <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Imagem de perfil -->

                <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>
    </header>

    <main>
        <!-- Seção de Adicionar Cds -->
        <section id="adicionar">
        
            <!-- Título principal da página -->
            <h1 id="titulo">Adicionar Novo CD</h1>
            <form action="processar_cd.php" method="post" enctype="multipart/form-data">

            
                <!-- Div que contém os campos de entrada do formulário -->
                <div id="inputs">
                    
                    <!-- Div para os dados do Cd -->
                    <div id="cima">
                        <div class="separacao">
                            <!-- Subtítulo para a seção de dados do Cd -->
                            <h1 class="subtitulo" id="acesso">Dados do Cd:</h1>
                            
                            <!-- Campos de entrada -->
                            <input type="text" placeholder="Titulo" class="input" name="titulo" required>
                            <div>
                                <label for="upload" class="input add_perfil_img" id="add_perfil_img">Adicionar foto da Capa</label>
                                <input type="file" id="upload" hidden name="capa">
                            </div>
                            <input type="text" placeholder="Estoque" maxlength="20" class="input num"name="disponibilidade" required>
                            <input type="text" placeholder="Lançamento" class="input ano"  name="anoLancamento"  required>
                            <div id="destaque">
                                <h1 id="titulo_destaque">Destaque</h1>
                                <select name="destaque" required>
                                    <option value="Sim">Sim</option>
                                    <option value="Não">Não</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Div para o lado -->
                        <div class="separacao" id="direita">
                            
                            <!-- Campos de entrada -->
                            
                            <input type="text" placeholder="Preço" maxlength="20" class="input preco"  name="preco" step="0.10" min="1" required>
                            <input type="text" placeholder="Gênero" class="input" name="genero" required>
                            <input type="text" placeholder="Descrição" class="input" name="descricao" required>
                            <input type="text" placeholder="Artistas" class="input" id="searchArtista"  onkeyup="buscarArtistas()">
                            <div id="artistasSelecionados"></div>
                            <input type="text" placeholder="Musica" id="searchMusica" class="input" onkeyup="buscarMusicas()">
                            <div id="sugestoesArtistas"></div>

                            <div id="sugestoesMusicas"></div>
                            <div id="musicasSelecionadas"></div>
                        </div>
                        <!-- Botão de Adicionar -->
                        <button id="button">Adicionar</button>
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
// Dados carregados do PHP (você carrega os nomes dos artistas/músicas no JavaScript)
const artistas = [
    <?php
    $conn = new mysqli("localhost", "root", "", "LojaCDs");
    $result_artistas = $conn->query("SELECT id_artista, nomeArtista FROM Artista");
    while ($artista = $result_artistas->fetch_assoc()) {
        echo "{ id: " . $artista['id_artista'] . ", nome: '" . addslashes($artista['nomeArtista']) . "' },";
    }
    ?>
];

const musicas = [
    <?php
    $result_musicas = $conn->query("SELECT id_musica, nomeMusica FROM Musica");
    while ($musica = $result_musicas->fetch_assoc()) {
        echo "{ id: " . $musica['id_musica'] . ", nome: '" . addslashes($musica['nomeMusica']) . "' },";
    }
    $conn->close();
    ?>
];

// ARTISTAS
function buscarArtistas() {
    let input = document.getElementById('searchArtista').value.toLowerCase();
    let sugestoes = document.getElementById('sugestoesArtistas');
    sugestoes.innerHTML = '';

    if (input.length > 0) {
        artistas.forEach(function(artista) {
            if (artista.nome.toLowerCase().includes(input)) {
                let div = document.createElement('div');
                div.textContent = artista.nome;
                div.onclick = function() { selecionarArtista(artista); };
                sugestoes.appendChild(div);
            }
        });
    }
}

function selecionarArtista(artista) {
    let selecionados = document.getElementById('artistasSelecionados');
    
    // Evitar duplicado
    if (document.getElementById('artista_' + artista.id)) return;

    let div = document.createElement('div');
    div.innerHTML = artista.nome + ' <button type="button" onclick="this.parentNode.remove()">Remover</button>';
    div.id = 'artista_' + artista.id;

    // Cria input escondido
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'artistas[]';
    input.value = artista.id;
    div.appendChild(input);

    selecionados.appendChild(div);

    document.getElementById('sugestoesArtistas').innerHTML = '';
    document.getElementById('searchArtista').value = '';
}

// MÚSICAS
function buscarMusicas() {
    let input = document.getElementById('searchMusica').value.toLowerCase();
    let sugestoes = document.getElementById('sugestoesMusicas');
    sugestoes.innerHTML = '';

    if (input.length > 0) {
        musicas.forEach(function(musica) {
            if (musica.nome.toLowerCase().includes(input)) {
                let div = document.createElement('div');
                div.textContent = musica.nome;
                div.onclick = function() { selecionarMusica(musica); };
                sugestoes.appendChild(div);
            }
        });
    }
}

function selecionarMusica(musica) {
    let selecionados = document.getElementById('musicasSelecionadas');

    if (document.getElementById('musica_' + musica.id)) return;

    let div = document.createElement('div');
    div.innerHTML = musica.nome + ' <button type="button" onclick="this.parentNode.remove()">Remover</button>';
    div.id = 'musica_' + musica.id;

    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'musicas[]';
    input.value = musica.id;
    div.appendChild(input);

    selecionados.appendChild(div);

    document.getElementById('sugestoesMusicas').innerHTML = '';
    document.getElementById('searchMusica').value = '';
}
</script>

<style>
#sugestoesArtistas div, #sugestoesMusicas div {
    background: #eee;
    padding: 5px;
    cursor: pointer;
    margin-bottom: 2px;
}
#artistasSelecionados div, #musicasSelecionados div {
    margin-top: 5px;
}
button {
    margin-left: 5px;
    color: red;
    cursor: pointer;
}
</style>
</body>
</html>