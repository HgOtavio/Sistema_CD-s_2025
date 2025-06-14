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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</head>
<style>/* Fundo da caixa */
/* Ajusta o X para ficar longe do texto */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    padding-right: 25px !important; /* dá espaço à direita pra caber o X */
    position: relative; /* para o X ser posicionado em relação a essa caixa */
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    position: absolute !important;
    right: 5px; /* distancia do canto direito */
    top: 50%;
    transform: translateY(-50%);
    color: #f3e8ff;
    font-weight: bold;
    cursor: pointer;
    padding-left: 5px;
    font-size: 14px;
    z-index: 10;
    transition: color 0.3s;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #9333ea;
}

/* Fundo da caixa */
.select2-container--default .select2-selection--multiple {
    background-color: #f3e8ff;
    border: 2px solid #a855f7;
    border-radius: 8px;
    padding: 8px 10px;  /* mais padding pra espaçamento */
    min-height: 40px;   /* altura mínima pra não ficar espremido */
    display: flex;
    flex-wrap: wrap;    /* permite as tags quebrarem linha */
    gap: 6px;           /* espaço entre as tags */
}

/* Quando seleciona (tag) */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #a855f7;
    border: 1px solid #9333ea;
    color: white;
    border-radius: 5px;
    padding: 4px 8px;  /* mais espaçamento interno */
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;          /* espaço entre texto e X */
    white-space: nowrap; /* não quebra o texto da tag */
}

/* Ícone de remover (X) */
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #f3e8ff;
    cursor: pointer;
    font-weight: bold;
    transition: color 0.3s;
}

/* Hover no X */
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #9333ea;
}

/* Caixinha de busca */
.select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
    color: #4b0082;
    font-size: 14px;
    min-width: 150px; /* largura mínima para digitar */
}

/* Dropdown dos itens */
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #a855f7;
    color: white;
}

/* Itens normais */
.select2-container--default .select2-results__option {
    color: #4b0082;
}

</style>

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
                            
                          <label>Selecione os CDs relacionados:</label>
<select id="select_cds" class="input" name="cdsSelecionados[]" multiple="multiple" required>
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
     <script>
    $(document).ready(function() {
        $('#select_cds').select2({
            placeholder: "Selecione os CDs",
            allowClear: true,
            width: '100%'
        });
    });
</script>

    <?php $conn->close(); ?>

   


</body>
</html>
