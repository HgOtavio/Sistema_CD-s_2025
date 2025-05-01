<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é do tipo administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Obtém o id do usuário logado
$id_usuario = $_SESSION['id_usuario'];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['artistas_selecionados'])) {
    foreach ($_POST['artistas_selecionados'] as $id_artista) {
        if (is_numeric($id_artista)) {
            $id_artista = intval($id_artista);

            $sql_delete_associacao = "DELETE FROM CD_Artista WHERE id_artista = ?";
            $stmt1 = $conn->prepare($sql_delete_associacao);
            $stmt1->bind_param("i", $id_artista);
            $stmt1->execute();

            $sql_delete = "DELETE FROM Artista WHERE id_artista = ?";
            $stmt2 = $conn->prepare($sql_delete);
            $stmt2->bind_param("i", $id_artista);
            $stmt2->execute();
        }
    }

    echo "<div id='mensagem' class='sucesso'>Artista(s) excluído(s) com sucesso!</div>";
}
// Filtros
$filtro_nome = $_GET['filtro_nome'] ?? '';
$filtro_nascimento = $_GET['filtro_nascimento'] ?? '';
$filtro_cds = $_GET['filtro_cds'] ?? '';

$where = [];
$params = [];
$types = '';

if (!empty($filtro_nome)) {
    $where[] = "Artista.nomeArtista LIKE ?";
    $params[] = "%$filtro_nome%";
    $types .= 's';
}

if (!empty($filtro_nascimento)) {
    $where[] = "Artista.dataNascimento = ?";
    $params[] = $filtro_nascimento;
    $types .= 's';
}

if (!empty($filtro_cds)) {
    $where[] = "Artista.id_artista IN (
        SELECT id_artista FROM CD_Artista
        INNER JOIN CD ON CD.id_cd = CD_Artista.id_cd
        WHERE CD.titulo LIKE ?
    )";
    $params[] = "%$filtro_cds%";
    $types .= 's';
}

$sql = "SELECT * FROM Artista";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Artistas</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar.css">
    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar_artista.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../../js/adm/gerenciar/gerenciar_filtro.js" defer></script>
    <script src="../../../../../js/mascara/mascara_data.js" defer></script>
</head>
<style>

.checkbox-imagem-wrapper {
    position: relative;
    width: 50px;
    height: 50px;
}

.checkbox-artista {
    width: 50%;
    height: 50%;
    opacity: 1;
    cursor: pointer;
}

.imagem-selecionado {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: none;
    object-fit: contain;
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

    <button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button>

    <h1 id="titulo">Gerenciar Artistas</h1>

    <button id="btn_filtro">Filtros</button>
    <form method="get" action="gerenciar_artistas.php">
            <section id="filtro">
                 <div id="separar">
                     <div class="lado">
                        <p class="p_filtro">
                            Nome:<input type="text" class="input_filtro" name="filtro_nome" value="<?= htmlspecialchars($_GET['filtro_nome'] ?? '') ?>">
                        </p>

                        <p class="p_filtro">
                            Nascimento:<input type="text" class="input_filtro data" name="filtro_nascimento" value="<?= htmlspecialchars($_GET['filtro_nascimento'] ?? '') ?>">
                        </p>
                    </div>
                        <p class="p_filtro">
                            CDs Associados:<input type="text" class="input_filtro" name="filtro_cds" value="<?= htmlspecialchars($_GET['filtro_cds'] ?? '') ?>">
                        </p>
                </div>
        <div>
            <button type="submit" id="button_filtro">Procurar</button>
            <a href="gerenciar_artistas.php"><button type="button" id="button_filtro">Todos</button></a>
        </div>
</section>
</form>


    <button class="button_voltar"><a href="../adicionar/add_artista.php" class="link_voltar">Adicionar Artistas</a></button>
   

    <section id="tabelao">
    <form method="post" action="gerenciar_artista.php">
    <button id="button_excluir" type="submit" onclick="return confirm('Tem certeza que deseja excluir os artistas selecionados?')">
        Excluir Selecionados
    </button>

    <table id="tabela">
        <thead>
            <tr id="itens_cabeca">
                <th class="titulo_item id">ID</th>
                <th class="titulo_item">Nome</th>
                <th class="titulo_item">Foto</th>
                <th class="titulo_item">Nascimento</th>
                <th class="titulo_item">Descrição</th>
                <th class="titulo_item">CDs Associados</th>
                <th class="titulo_item">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
            <tr class="informações">
                <th class="info"><?= htmlspecialchars($row['nomeArtista']) ?></th>
                <th class="info">
                    <?php if ($row['fotoPerfil']) : ?>
                        <img src="../../../../../img/artistas//<?= htmlspecialchars($row['fotoPerfil']) ?>" alt="Foto do Artista" width="50">
                    <?php else : ?>
                        Sem Foto
                    <?php endif; ?>
                </th>
                <th class="info"><?= htmlspecialchars($row['dataNascimento']) ?></th>
                <th class="info"><?= htmlspecialchars($row['descricao']) ?></th>
                <th class="info">
                    <?php
                    $id_artista = $row['id_artista'];
                    $sql_cds = "SELECT titulo FROM CD
                                INNER JOIN CD_Artista ON CD.id_cd = CD_Artista.id_cd
                                WHERE CD_Artista.id_artista = ?";
                    $stmt_cds = $conn->prepare($sql_cds);
                    $stmt_cds->bind_param("i", $id_artista);
                    $stmt_cds->execute();
                    $result_cds = $stmt_cds->get_result();

                    $cds = [];
                    while ($cd = $result_cds->fetch_assoc()) {
                        $cds[] = htmlspecialchars($cd['titulo']);
                    }

                    echo count($cds) > 0 ? implode(", ", $cds) : "Nenhum CD associado";
                    ?>
                </th>
                <th class="info">
                    <a href="../editar/editar_artistas.php?id_artista=<?= $row['id_artista'] ?>" class="link_acao">Editar</a><br>
                    <div class="checkbox-imagem-wrapper">
    <input type="checkbox" id="check<?= $row['id_artista'] ?>" name="artistas_selecionados[]" value="<?= $row['id_artista'] ?>" class="checkbox-artista">
    <img src="../../../../../img/perfil/disco.png" alt="Selecionado" class="imagem-selecionado" id="img<?= $row['id_artista'] ?>">
            </div>


                </th>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</form>
 
    </section>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".checkbox-artista");

    checkboxes.forEach(checkbox => {
        const img = document.getElementById("img" + checkbox.value);

        // Quando o checkbox muda
        checkbox.addEventListener("change", function () {
            if (this.checked) {
                this.style.display = "none";
                img.style.display = "block";
            }
        });

        // Quando clica na imagem
        img.addEventListener("click", function () {
            checkbox.checked = false;
            checkbox.style.display = "inline-block";
            img.style.display = "none";
        });
    });
});
</script>



</body>
</html>

