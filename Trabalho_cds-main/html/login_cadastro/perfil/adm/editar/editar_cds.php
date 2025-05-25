<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if (!isset($_GET['id_cd']) || !is_numeric($_GET['id_cd'])) {
    die("ID do CD inválido.");
}

$id_cd = intval($_GET['id_cd']);

// Buscar dados do CD
$sql_cd = "SELECT * FROM CD WHERE id_cd = ?";
$stmt_cd = $conn->prepare($sql_cd);
$stmt_cd->bind_param("i", $id_cd);
$stmt_cd->execute();
$result_cd = $stmt_cd->get_result();
$cd = $result_cd->fetch_assoc();

if (!$cd) {
    die("CD não encontrado.");
}

// Buscar artistas associados ao CD
$sql_artistas = "SELECT a.id_artista, a.nomeArtista 
                 FROM Artista a 
                 INNER JOIN CD_Artista ca ON a.id_artista = ca.id_artista 
                 WHERE ca.id_cd = ?";
$stmt_artistas = $conn->prepare($sql_artistas);
$stmt_artistas->bind_param("i", $id_cd);
$stmt_artistas->execute();
$result_artistas = $stmt_artistas->get_result();
$artistas_associados = [];
while ($artista = $result_artistas->fetch_assoc()) {
    $artistas_associados[] = $artista['id_artista'];
}

// Buscar músicas associadas ao CD
$sql_musicas = "SELECT m.id_musica, m.nomeMusica 
                FROM Musica m 
                INNER JOIN CD_Musica cm ON m.id_musica = cm.id_musica 
                WHERE cm.id_cd = ?";
$stmt_musicas = $conn->prepare($sql_musicas);
$stmt_musicas->bind_param("i", $id_cd);
$stmt_musicas->execute();
$result_musicas = $stmt_musicas->get_result();
$musicas_associadas = [];
while ($musica = $result_musicas->fetch_assoc()) {
    $musicas_associadas[] = $musica['id_musica'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar CDs</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css">
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit_cds.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="../../../../../js/adm/add_alt_cds.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_num.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_preco.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_ano.js" defer></script>
    <script>
    $(document).ready(function() {
        // Inicializando o select2 para os selects de artistas e músicas
        $('select[name="artistasSelecionados[]"], select[name="musicasSelecionadas[]"]').select2({
            placeholder: "Selecione os itens",
            allowClear: true,
            width: '100%'  // Ajusta o width para ocupar todo o espaço disponível
        });
    });
    </script>
</head>
<body>

    <!-- Cabeçalho da página (com user logado) -->
    <header> 
        <div id="parte_de_cima_cab">
            <!-- Logo da página -->
            <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            <!-- Barra de pesquisa -->
            <div id="login_carrinho">
                <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a>
                <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a>
            </div>
        </div>
    </header>

    <main>
        <!-- Seção de Alterar Dados do Cds -->
        <section id="adicionar">
            <h1 id="titulo">Alterar Dados do CD</h1>
            <form action="atualizar_cd.php" method="post" enctype="multipart/form-data">
                <div id="inputs">
                    <div id="cima">
                        <div class="separacao">
                            <h1 class="subtitulo" id="acesso">Dados do Cd:</h1>
                            
                            <input type="hidden" name="id_cd" value="<?= htmlspecialchars($cd['id_cd']) ?>">

                            <input type="text" placeholder="Titulo" class="input" name="titulo" value="<?= htmlspecialchars($cd['titulo']) ?>" required>
                            <div>
                                <img src="../../../../../img/<?= htmlspecialchars($cd['capa']) ?>" alt="Capa do CD" width="150"><br><br>
                                <label for="upload" class="input add_perfil_img" id="add_perfil_img">Alterar foto da Capa</label>
                                <input type="file" id="upload" hidden name="nova_capa">
                            </div>
                            <div id="destaque" >
                                <h1 id="titulo_destaque">Destaque</h1>
                                <div id="inputs_destaque">
                                    <input class="input" type="text" name="destaque" value="<?= htmlspecialchars($cd['destaque']) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="separacao" id="direita">
                            <input type="text" placeholder="Estoque" maxlength="20" class="input num" name="disponibilidade" value="<?= htmlspecialchars($cd['disponibilidade']) ?>" required>
                            <input type="text" placeholder="Preço" maxlength="20" class="input preco" name="preco" value="<?= htmlspecialchars($cd['preco']) ?>" required>
                            <input type="text" placeholder="Lançamento" class="input ano" name="anoLancamento" value="<?= htmlspecialchars($cd['anoLancamento']) ?>" required>
                            <input type="text" placeholder="Gênero" class="input" name="genero" value="<?= htmlspecialchars($cd['genero']) ?>" required>
                            <textarea class="input" name="descricao" required><?= htmlspecialchars($cd['descricao']) ?></textarea>
                            
                            <!-- Artistas associados -->
                            <!-- Artistas associados -->
    <input type="text" id="artistaSearch" onkeyup="searchArtistas()" placeholder="Pesquise artistas" class="input">
    <div id="artistaSuggestions" ></div>
    <div id="selectedArtistas"></div> <!-- Exibição dos artistas selecionados -->


                             <!-- Músicas associadas -->
    <input type="text" id="musicaSearch" onkeyup="searchMusicas()" placeholder="Pesquise músicas" class="input">
    <div id="musicaSuggestions"></div>
    <div id="selectedMusicas"></div> <!-- Exibição das músicas selecionadas -->
                        </div>
                    </div>
                    <button id="button">Alterar</button>
                </div>
            </form>
        </section>
    </main>

    <div id="imgs_direita">
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e2"></div>
    </div>

    <div id="imgs_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d2">
    </div>
    <script>
    var artistasSelecionados = [];
    var musicasSelecionadas = [];

    // Função de pesquisa de artistas
    function searchArtistas() {
        var input = document.getElementById('artistaSearch');
        var filter = input.value.toUpperCase();
        var div = document.getElementById('artistaSuggestions');
        div.innerHTML = ""; // Limpa as sugestões

        // Filtra as sugestões
        <?php foreach ($artistas as $artista): ?>
            if ("<?= $artista['nomeArtista'] ?>".toUpperCase().indexOf(filter) > -1) {
                var divItem = document.createElement("div");
                divItem.textContent = "<?= $artista['nomeArtista'] ?>";
                divItem.classList.add('suggestion-item');
                divItem.onclick = function() { selectArtista(<?= $artista['id_artista'] ?>, '<?= $artista['nomeArtista'] ?>'); };
                div.appendChild(divItem);
            }
        <?php endforeach; ?>
    }

    // Função de pesquisa de músicas
    function searchMusicas() {
        var input = document.getElementById('musicaSearch');
        var filter = input.value.toUpperCase();
        var div = document.getElementById('musicaSuggestions');
        div.innerHTML = ""; // Limpa as sugestões

        // Filtra as sugestões
        <?php foreach ($musicas as $musica): ?>
            if ("<?= $musica['nomeMusica'] ?>".toUpperCase().indexOf(filter) > -1) {
                var divItem = document.createElement("div");
                divItem.textContent = "<?= $musica['nomeMusica'] ?>";
                divItem.classList.add('suggestion-item');
                divItem.onclick = function() { selectMusica(<?= $musica['id_musica'] ?>, '<?= $musica['nomeMusica'] ?>'); };
                div.appendChild(divItem);
            }
        <?php endforeach; ?>
    }

    // Função para selecionar artista
    function selectArtista(id, nome) {
        if (!artistasSelecionados.includes(id)) {
            artistasSelecionados.push(id);
            var div = document.createElement("div");
            div.textContent = nome;
            var removeBtn = document.createElement("button");
            removeBtn.textContent = "Remover";
            removeBtn.onclick = function() { removeArtista(id, div); };
            div.appendChild(removeBtn);
            document.getElementById('selectedArtistas').appendChild(div);
            updateArtistasSearch();
        }
    }

    // Função para selecionar música
    function selectMusica(id, nome) {
        if (!musicasSelecionadas.includes(id)) {
            musicasSelecionadas.push(id);
            var div = document.createElement("div");
            div.textContent = nome;
            var removeBtn = document.createElement("button");
            removeBtn.textContent = "Remover";
            removeBtn.onclick = function() { removeMusica(id, div); };
            div.appendChild(removeBtn);
            document.getElementById('selectedMusicas').appendChild(div);
            updateMusicasSearch();
        }
    }

    // Função para remover artista
    function removeArtista(id, div) {
        artistasSelecionados = artistasSelecionados.filter(function(item) {
            return item !== id;
        });
        div.remove();
    }

    // Função para remover música
    function removeMusica(id, div) {
        musicasSelecionadas = musicasSelecionadas.filter(function(item) {
            return item !== id;
        });
        div.remove();
    }

    // Atualiza o campo de busca de artistas
    function updateArtistasSearch() {
        var input = document.getElementById('artistaSearch');
        input.value = '';  // Limpa o campo de pesquisa
        document.getElementById('artistaSuggestions').innerHTML = ''; // Limpa sugestões
    }

    // Atualiza o campo de busca de músicas
    function updateMusicasSearch() {
        var input = document.getElementById('musicaSearch');
        input.value = '';  // Limpa o campo de pesquisa
        document.getElementById('musicaSuggestions').innerHTML = ''; // Limpa sugestões
    }
</script>

</body>
</html>
