<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Buscar artistas ordenados
$sql_artistas = "SELECT id_artista, nomeArtista FROM Artista ORDER BY nomeArtista";
$result_artistas = $conn->query($sql_artistas);
$artistas = [];
if ($result_artistas) {
    while ($row = $result_artistas->fetch_assoc()) {
        $artistas[] = $row;
    }
}

// Buscar músicas ordenadas
$sql_musicas = "SELECT id_musica, nomeMusica FROM Musica ORDER BY nomeMusica";
$result_musicas = $conn->query($sql_musicas);
$musicas = [];
if ($result_musicas) {
    while ($row = $result_musicas->fetch_assoc()) {
        $musicas[] = $row;
    }
}

// Para adição nova, arrays vazios para artistas/músicas associados
$artistas_associados = [];
$musicas_associadas = [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Adicionar CDs</title>

    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css" />
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit_cds.css" />
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css" />

    <!-- CSS do Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery (necessário para Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JS do Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="../../../../../js/adm/add_alt_cds.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_ano.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_num.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_preco.js" defer></script>

    <script>
    $(document).ready(function() {
        $('#artistaSelect, #musicaSelect').select2({
            placeholder: "Selecione os itens",
            allowClear: true,
            width: '100%'
        });
    });
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
</head>
<body>

<header>
    <div id="parte_de_cima_cab">
        <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo" /></a>
        <div id="login_carrinho">
            <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil" /></a>
            <a href="../../butoes/carrinho.php"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho" /></a>
        </div>
    </div>
</header>

<main>
    <section id="adicionar">
        <h1 id="titulo">Adicionar Novo CD</h1>
        <form action="processar_cd.php" method="post" enctype="multipart/form-data">
            <div id="inputs">
                <div id="cima">
                    <div class="separacao">
                        <h1 class="subtitulo" id="acesso">Dados do Cd:</h1>

                        <input type="text" placeholder="Título" class="input" name="titulo" required />

                        <div>
                            <label for="upload" class="input add_perfil_img" id="add_perfil_img">Adicionar foto da Capa</label>
                            <input type="file" id="upload" hidden name="capa" accept="image/*" />
                        </div>

                        <input type="text" placeholder="Estoque" maxlength="20" class="input num" name="disponibilidade" required />

                        <input type="text" placeholder="Lançamento" class="input ano" name="anoLancamento" required />

                        <div id="destaque">
                            <h1 id="titulo_destaque">Destaque</h1>
                            <select name="destaque" required>
                                <option value="Destaque">Sim</option>
                                <option value="Não Destaque">Não</option>
                            </select>
                        </div>
                    </div>

                    <div class="separacao" id="direita">
                        <input type="text" placeholder="Preço" maxlength="20" class="input preco" name="preco" step="0.10" min="1" required />

                        <input type="text" placeholder="Gênero" class="input" name="genero" required />

                        <input type="text" placeholder="Descrição" class="input" name="descricao" required />

                        <label for="artistaSelect">Artistas</label>
                        <select id="artistaSelect" name="artistas[]" multiple="multiple" style="width: 100%;">
                            <?php foreach ($artistas as $artista): ?>
                                <option value="<?= $artista['id_artista'] ?>" <?= in_array($artista['id_artista'], $artistas_associados) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($artista['nomeArtista']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="musicaSelect">Músicas</label>
                        <select id="musicaSelect" name="musicas[]" multiple="multiple" style="width: 100%;">
                            <?php foreach ($musicas as $musica): ?>
                                <option value="<?= $musica['id_musica'] ?>" <?= in_array($musica['id_musica'], $musicas_associadas) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($musica['nomeMusica']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button id="button" type="submit">Adicionar</button>
                </div>
            </div>
        </form>
    </section>
</main>

<!-- Imagens decorativas à direita -->
<div id="imgs_direita">
    <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" /></div>
    <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" /></div>
    <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" /></div>
    <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e" /></div>
    <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e2" /></div>
</div>

<!-- Imagens decorativas à esquerda -->
<div id="imgs_esquerda">
    <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" />
    <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" />
    <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" />
    <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d" />
    <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d2" />
</div>

</body>
</html>
