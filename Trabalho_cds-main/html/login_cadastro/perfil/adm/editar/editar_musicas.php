<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if (!isset($_GET['id_musica']) || !is_numeric($_GET['id_musica'])) {
    die("ID da música inválido.");
}

$id_musica = intval($_GET['id_musica']);

// Buscar dados da música
$sql_musica = "SELECT * FROM Musica WHERE id_musica = ?";
$stmt = $conn->prepare($sql_musica);
$stmt->bind_param("i", $id_musica);
$stmt->execute();
$result_musica = $stmt->get_result();
$musica = $result_musica->fetch_assoc();

if (!$musica) {
    die("Música não encontrada.");
}

// Buscar CDs associados à música
$sql_cds_associados = "SELECT CD.id_cd, CD.titulo FROM CD_Musica 
                       JOIN CD ON CD_Musica.id_cd = CD.id_cd 
                       WHERE CD_Musica.id_musica = ?";
$stmt = $conn->prepare($sql_cds_associados);
$stmt->bind_param("i", $id_musica);
$stmt->execute();
$result_cds_associados = $stmt->get_result();

$cds_associados = [];
while ($cd = $result_cds_associados->fetch_assoc()) {
    $cds_associados[] = $cd;
}

// Buscar todos os CDs disponíveis
$sql_cds = "SELECT id_cd, titulo FROM CD";
$result_cds = $conn->query($sql_cds);

$cds_disponiveis = [];
while ($cd = $result_cds->fetch_assoc()) {
    $cds_disponiveis[] = $cd;
}
 // Áudio
 $audio_path = "../../../../../audio/" . $musica['id_musica'] . ".mp3";
 $audio_player = file_exists($audio_path) ? 
     "<audio controls><source src='$audio_path' type='audio/mp3'>Seu navegador não suporta o áudio.</audio>" : 
     "Sem áudio";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Musicas</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css">
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit_musicas.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">
    <script src="../../../../../js/mascaras/mascara_temp.js" defer></script>
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
        <!-- Seção de Alterar Dados do Musica -->
        <section id="adicionar">
            <!-- Título principal da página -->
            <h1 id="titulo">Alterar Dados da Música</h1>
            <form action="salvar_edicao_musica.php" method="post" enctype="multipart/form-data">

                <!-- Div que contém os campos de entrada do formulário -->
                <div id="inputs">
                    <!-- Adicionando o campo hidden para o id_musica -->
                    <input type="hidden" name="id_musica" value="<?= htmlspecialchars($id_musica); ?>">

                    <!-- Div para os dados da música-->
                    <div id="cima">
                        <div class="separacao">
                            <h1 class="subtitulo" id="acesso">Dados da Música:</h1>
                            <input type="text" placeholder="Nome" class="input" name="nomeMusica" value="<?= htmlspecialchars($musica['nomeMusica']); ?>" required>
                            <input type="text" placeholder="Duração" class="input tempo" name="tempo" value="<?= htmlspecialchars($musica['tempo']); ?>" required>
                        </div>
                        
                        <!-- Div para os dados do artista -->
                        <div class="separacao" id="direita">
                            <div>
                            <div>
    <?= $audio_player ?><br><br>
</div>
                                <label for="upload" id="audio">Alterar áudio da Música</label>
                                <input type="file" class="input add_perfil_img" id="upload" hidden class="input">
                            </div> 
                            
                            <select class="input" name="cdsSelecionados[]" multiple size="5" required>
                                <?php foreach ($cds_disponiveis as $cd): 
                                    $selected = in_array($cd['id_cd'], array_column($cds_associados, 'id_cd')) ? 'selected' : '';
                                ?>
                                    <option value="<?= $cd['id_cd']; ?>" <?= $selected; ?>>
                                        <?= htmlspecialchars($cd['titulo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Botão de alterar -->
                        <button id="button" type="submit">Alterar</button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <!-- Seção de imagens decorativas -->
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

    <?php $conn->close(); ?>

</body>
</html>
