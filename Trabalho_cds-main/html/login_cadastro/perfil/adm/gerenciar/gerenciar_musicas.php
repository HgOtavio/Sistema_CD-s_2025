<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é do tipo administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}
// Filtros
$nomeMusicaFiltro = isset($_GET['nomeMusica']) ? $_GET['nomeMusica'] : '';
$ordemTempo = isset($_GET['ordemTempo']) ? $_GET['ordemTempo'] : '';
$cdFiltro = isset($_GET['cd']) ? $_GET['cd'] : '';

// Consulta
$sql_musica = "SELECT m.id_musica, m.nomeMusica, m.tempo, m.audio
               FROM Musica m
               LEFT JOIN CD_Musica cm ON m.id_musica = cm.id_musica
               LEFT JOIN CD c ON cm.id_cd = c.id_cd
               WHERE 1=1";

if (!empty($nomeMusicaFiltro)) {
    $sql_musica .= " AND m.nomeMusica LIKE '%$nomeMusicaFiltro%'";
}
if (!empty($cdFiltro)) {
    $sql_musica .= " AND c.titulo LIKE '%$cdFiltro%'";
}

if ($ordemTempo == 'maior') {
    $sql_musica .= " ORDER BY m.tempo DESC";
} elseif ($ordemTempo == 'menor') {
    $sql_musica .= " ORDER BY m.tempo ASC";
} else {
    $sql_musica .= " ORDER BY m.id_musica ASC";
}

$result_musica = $conn->query($sql_musica);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Músicas</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar.css">
    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar_musica.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../../js/adm/gerenciar/gerenciar_filtro.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_temp.js" defer></script>
</head>
<body>

    <!-- Cabeçalho da página (com user logado) -->
    <header> 
        <div id="parte_de_cima_cab">
            <!-- Logo da página -->
            <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Conta e Carrinho -->
            <div id="login_carrinho">
                <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a>
                <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a>
            </div>
        </div>
    </header>
    
    <button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button>

    <h1 id="titulo">Gerenciar Músicas</h1>
    <form method="GET" action="">

        <button id="btn_filtro">Filtros</button>

        <section id="filtro">
            <div id="separar">
                <div class="lado">
                    <p class="p_filtro">Nome:<input type="text" name="nomeMusica" class="input_filtro" value="<?php echo htmlspecialchars($nomeMusicaFiltro); ?>"></p>
                    <p class="p_filtro">Cds Associados:<input type="text" class="input_filtro" name="cd" value="<?php echo htmlspecialchars($cdFiltro); ?>"></p>
                </div>
                <div class="lado">
                    <p class="p_filtro">
                        Filtro de Duração:
                        <select class="input_filtro tempo" name="ordemTempo" >
                            <option value="">Selecione</option>
                            <option value="maior" <?php if($ordemTempo == 'maior') echo 'selected'; ?>>Maior Duração</option>
                            <option value="menor"  <?php if($ordemTempo == 'menor') echo 'selected'; ?>>Menor Duração</option>
                        </select>
                    </p>
                </div>
            </div>
            <div>
                <button id="button_filtro" type="submit">Procurar</button>
                <button id="button_filtro">Todos</button>
            </div>
        </section>
    </form>
    <button class="button_voltar"><a href="../adicionar/add_musica.php" class="link_voltar">Adicionar Músicas</a></button>
    

    <section id="tabelao">
     <form method="POST" action="deletar_varias_musicas.php">
     <button id="button_excluir" type="submit">Excluir</button>
            <table id="tabela">
                <thead>
                    <tr id="itens_cabeca">
                        <th class="titulo_item ">ID</th>
                        <th class="titulo_item">Nome</th>
                        <th class="titulo_item">Duração</th>
                        <th class="titulo_item">Áudio</th>
                        <th class="titulo_item">Cds Associados</th>
                        <th class="titulo_item">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if ($result_musica->num_rows > 0) {
                        while ($musica = $result_musica->fetch_assoc()) {
                            $id_musica = $musica['id_musica'];

                            // CDs associados
                            $sql_cds = "SELECT CD.titulo 
                                        FROM CD_Musica 
                                        JOIN CD ON CD_Musica.id_cd = CD.id_cd 
                                        WHERE CD_Musica.id_musica = ?";
                            $stmt_cds = $conn->prepare($sql_cds);
                            $stmt_cds->bind_param("i", $id_musica);
                            $stmt_cds->execute();
                            $result_cds = $stmt_cds->get_result();

                            $cds = [];
                            while ($cd = $result_cds->fetch_assoc()) {
                                $cds[] = $cd['titulo'];
                            }
                            $cds_list = !empty($cds) ? implode(", ", $cds) : "Nenhum CD associado";

                            // Áudio
                            $audio_path = "../../../../../audio/" . $musica['id_musica'] . ".mp3";
                            $audio_player = file_exists($audio_path) ? 
                                "<audio controls><source src='$audio_path' type='audio/mp3'>Seu navegador não suporta o áudio.</audio>" : 
                                "Sem áudio";

                            // Linha
                            echo "<tr class='informações'>
                                    <th  class='info'>{$musica['id_musica']}</th>
                                    <th  class='info'>{$musica['nomeMusica']}</th>
                                    <th class='info'>" . number_format($musica['tempo'], 2) . " minutos</td>
                                    <th class='info'>{$audio_player}</th>
                                    <th class='info'>{$cds_list}</th>
                                    <th class='info'>
                                        <a href='../editar/editar_musicas.php?id_musica={$musica['id_musica']}' class='link_acao'>Editar</a><br>

                                        <input type='checkbox' name='musicas_selecionadas[]' value='{$musica['id_musica']}' class='input' id='musica_{$musica['id_musica']}' />
                                        <label for='musica_{$musica['id_musica']}'></label>

                                        
                                
                                    </td>
                                </tr>";

                            $stmt_cds->close();
                        }
                    } else {
                        echo "<tr><th colspan='6'>Nenhuma Música encontrada</th></tr>";
                    }
                ?>      
                
                </tbody>
            </table>
      </form>
    </section>

</body>
</html>
