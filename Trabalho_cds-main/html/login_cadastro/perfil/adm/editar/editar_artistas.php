<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é do tipo administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Verificar se foi passado um ID
if (!isset($_GET['id_artista']) || !is_numeric($_GET['id_artista'])) {
    die("ID do artista não informado ou inválido.");
}

$id_artista = intval($_GET['id_artista']);

// Se enviou o formulário (alteração)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeArtista = $_POST['nomeArtista'];

    // Verificar se a data foi passada corretamente
    if (isset($_POST['dataNascimento']) && !empty($_POST['dataNascimento'])) {
        $dataNascimento = DateTime::createFromFormat('m/d/Y', $_POST['dataNascimento']);
        
        if ($dataNascimento === false) {
            die("Formato de data inválido. Certifique-se de que a data está no formato 'm/d/Y'.");
        }

        // Se a data for válida, formata para 'Y-m-d'
        $dataNascimento = $dataNascimento->format('Y-m-d');
    } else {
        die("Data de nascimento não fornecida.");
    }

    $descricao = $_POST['descricao'];
    $cdsSelecionados = isset($_POST['cds']) ? $_POST['cds'] : [];

    // Atualizar os dados do artista
    $sql_update = "UPDATE Artista SET nomeArtista = ?, dataNascimento = ?, descricao = ? WHERE id_artista = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssi", $nomeArtista, $dataNascimento, $descricao, $id_artista);
    $stmt_update->execute();

    // Deletar CDs associados antigos
    $conn->query("DELETE FROM CD_Artista WHERE id_artista = $id_artista");

    // Inserir novos CDs associados
    foreach ($cdsSelecionados as $id_cd) {
        $stmt_insert_cd = $conn->prepare("INSERT INTO CD_Artista (id_artista, id_cd) VALUES (?, ?)");
        $stmt_insert_cd->bind_param("ii", $id_artista, $id_cd);
        $stmt_insert_cd->execute();
    }

    header("Location: editar_artista.php?id_artista=$id_artista&success=1");
    exit();
}

// Buscar dados do artista
$sql_artista = "SELECT * FROM Artista WHERE id_artista = ?";
$stmt_artista = $conn->prepare($sql_artista);
$stmt_artista->bind_param("i", $id_artista);
$stmt_artista->execute();
$result_artista = $stmt_artista->get_result();

if ($result_artista->num_rows == 0) {
    die("Artista não encontrado.");
}

$artista = $result_artista->fetch_assoc();

// Buscar CDs associados
$sql_cds_associados = "SELECT id_cd FROM CD_Artista WHERE id_artista = ?";
$stmt_cds_associados = $conn->prepare($sql_cds_associados);
$stmt_cds_associados->bind_param("i", $id_artista);
$stmt_cds_associados->execute();
$result_cds_associados = $stmt_cds_associados->get_result();

$cds_associados = [];
while ($cd = $result_cds_associados->fetch_assoc()) {
    $cds_associados[] = $cd['id_cd'];
}

// Buscar todos os CDs disponíveis
$sql_cds = "SELECT id_cd, titulo FROM CD";
$result_cds = $conn->query($sql_cds);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Artistas</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">
    <script src="../../../../../js/mascaras/mascara_data.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>

<header> 
    <div id="parte_de_cima_cab">
        <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
        <div id="login_carrinho">
            <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a>
            <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a>
        </div>
    </div>
</header>

<main>
<section id="adicionar">
    <form action="processar_edicao_artista.php" method="post" enctype="multipart/form-data">
        <!-- Adicionado campo oculto para enviar o id_artista -->
        <input type="hidden" name="id_artista" value="<?= htmlspecialchars($id_artista); ?>">

        <h1 id="titulo">Alterar Dados do Artista</h1>
        <div id="inputs">
            <div id="cima">
                <div class="separacao">
                    <h1 class="subtitulo" id="acesso">Dados do artista:</h1>
                    <input type="text" placeholder="Nome" class="input" name="nomeArtista" value="<?= htmlspecialchars($artista['nomeArtista']) ?>" required>
                    <input type="file" name="fotoPerfil">
                    <div>
                        <?php if ($artista['fotoPerfil']) : ?>
                            <p>Foto atual:</p>
                            <img src="../Artista/<?= htmlspecialchars($artista['fotoPerfil']) ?>" alt="Foto do Artista" width="100">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="separacao" id="direita">
                    <input type="text" placeholder="Data de Nascimento" class="input data" name="dataNascimento" value="<?= isset($artista['dataNascimento']) ? date('m/d/Y', strtotime($artista['dataNascimento'])) : '' ?>" required>
                    <textarea placeholder="Descrição" class="input" name="descricao" required><?= htmlspecialchars($artista['descricao']) ?></textarea>
                    <select id="cdsSelect" name="cds[]" multiple="multiple" style="width: 100%;" class="input">
                        <?php while ($cd = $result_cds->fetch_assoc()) : ?>
                            <option value="<?= $cd['id_cd'] ?>" <?= in_array($cd['id_cd'], $cds_associados) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cd['titulo']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button id="button" type="submit">Alterar</button>
            </div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#cdsSelect').select2({
        placeholder: "Selecione os CDs associados",
        allowClear: true
    });
});
</script>

</body>
</html>
