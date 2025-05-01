<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é do tipo administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Obtém os filtros da URL (caso existam)
$filtro_titulo = isset($_GET['titulo']) ? $_GET['titulo'] : '';
$filtro_genero = isset($_GET['genero']) ? $_GET['genero'] : '';
$filtro_ano = isset($_GET['ano']) ? $_GET['ano'] : '';
$filtro_disponibilidade = isset($_GET['disponibilidade']) ? $_GET['disponibilidade'] : '';
$filtro_destaque = isset($_GET['destaque']) ? $_GET['destaque'] : '';
$filtro_preco_min = isset($_GET['preco_min']) ? $_GET['preco_min'] : '';
$filtro_preco_max = isset($_GET['preco_max']) ? $_GET['preco_max'] : '';
$filtro_artista = isset($_GET['artista']) ? $_GET['artista'] : '';
$filtro_musica = isset($_GET['musica']) ? $_GET['musica'] : '';

// Consulta CDs com base nos filtros
// Consulta CDs com base nos filtros
$sql_cd = "SELECT DISTINCT CD.id_cd, CD.titulo, CD.capa, CD.disponibilidade, CD.preco, CD.destaque, CD.anoLancamento, CD.genero, CD.descricao AS descricao_cd, CD.numero_vendas 
           FROM CD
           LEFT JOIN CD_Artista ON CD.id_cd = CD_Artista.id_cd
           LEFT JOIN Artista ON CD_Artista.id_artista = Artista.id_artista
           LEFT JOIN CD_Musica ON CD.id_cd = CD_Musica.id_cd
           LEFT JOIN Musica ON CD_Musica.id_musica = Musica.id_musica
           WHERE 1=1";
if (!empty($filtro_titulo)) {
    $sql_cd .= " AND titulo LIKE '%$filtro_titulo%'";
}

if (!empty($filtro_genero)) {
    $sql_cd .= " AND genero LIKE '%$filtro_genero%'";
}

if (!empty($filtro_ano)) {
    $sql_cd .= " AND anoLancamento = $filtro_ano";
}

if (!empty($filtro_disponibilidade)) {
    $sql_cd .= " AND disponibilidade LIKE '%$filtro_disponibilidade%'";
}
// Filtragem por destaque
if (!empty($filtro_destaque)) {
    if ($filtro_destaque == 'Destaque') {
        // Filtro para destacar CDs com destaque
        $sql_cd .= " AND destaque = 'Destaque'";
    } elseif ($filtro_destaque == 'Não Destaque') {
        // Filtro para CDs sem destaque
        $sql_cd .= " AND (destaque IS NULL OR destaque != 'Destaque')";
    }
}
// Novo filtro por artista
if (!empty($filtro_artista)) {
    $sql_cd .= " AND Artista.nomeArtista LIKE '%$filtro_artista%'";
}

// Novo filtro por música
if (!empty($filtro_musica)) {
    $sql_cd .= " AND Musica.nomeMusica LIKE '%$filtro_musica%'";
}




if (!empty($filtro_preco_min)) {
    $sql_cd .= " AND preco >= $filtro_preco_min";
}

if (!empty($filtro_preco_max)) {
    $sql_cd .= " AND preco <= $filtro_preco_max";
}

$result_cd = $conn->query($sql_cd);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cds</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar_cds.css">
    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../../js/adm/gerenciar/gerenciar_filtro.js" defer></script>
    <script src="../../../../../js/mascara/mascara_ano.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_preco.js" defer></script>
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

    <button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button>
    
    
    <h1 id="titulo">Gerenciar CDs</h1>
    

    <button id="btn_filtro">Filtros</button>

    <section id="filtro">
        <form action="" method="GET">
            <div id="separar">
                <div class="separar_pc">
                    <div class="lado">
                        <p class="p_filtro">Titulo:<input type="text" class="input_filtro" name="titulo" value="<?= $filtro_titulo ?>"></p>
                        <p class="p_filtro">Gênero:<input type="text" class="input_filtro" name="genero" value="<?= $filtro_genero ?>"></p>
                    </div>
                    <div class="lado">
                        <p class="p_filtro">Lançamento:<input type="text" class="input_filtro ano" name="ano" value="<?= $filtro_ano ?>"></p>
                        <p class="p_filtro">Durabilidade:<input type="text" class="input_filtro tempo" name="disponibilidade" value="<?= $filtro_disponibilidade ?>"></p>
                    </div>
                    <div class="lado">
                        <p class="p_filtro">Preço max:<input type="number"  step="0.01" class="input_filtro preco"  name="preco_max" value="<?= $filtro_preco_max ?>"></p>
                        <p class="p_filtro">Preço min:<input type="number" step="0.01" class="input_filtro preco" name="preco_min" value="<?= $filtro_preco_min ?>" ></p>
                    </div>
                </div>
                <div class="separar_pc">
                    <div class="lado">
                        <p class="p_filtro">Artistas:<input name="artista" type="text" class="input_filtro" value="<?= $filtro_artista ?>" ></p>
                        <p class="p_filtro">Musicas:<input name="musica" type="text" class="input_filtro" value="<?= $filtro_musica ?>"></p>
                    </div>
                    <p class="p_filtro">Destaque:<input   type="text" class="input_filtro" name="destaque" value="<?= $filtro_destaque ?>"></p>
                </div>
            </div>
            <div>
                <button id="button_filtro" type="submit">Procurar</button>
                <button id="button_filtro" href="gerenciar_cds.php">Todos</button>
            </div>
        </form>  
    </section>

    <button class="button_voltar"><a href="#" class="link_voltar">Adicionar CDs</a></button>
    <button id="button_excluir">Excluir</button>
    
    <section id="tabelao">
        <table id="tabela">
            <thead>
                <tr id="itens_cabeca">
                    <th class="titulo_item ">ID</th>
                    <th class="titulo_item">Titulo</th>
                    <th class="titulo_item">Capa</th>
                    <th class="titulo_item">Estoque</th>
                    <th class="titulo_item">Preço</th>
                    <th class="titulo_item">Destaque</th>
                    <th class="titulo_item">Lançamento</th>
                    <th class="titulo_item">Gênero</th>
                    <th class="titulo_item">Descrição</th>
                    <th class="titulo_item">Artistas</th>
                    <th class="titulo_item">Musicas</th>
                    <th class="titulo_item">Promoção</th>
                    <th class="titulo_item">Preço Final</th>
                    <th class="titulo_item">Vendas</th>
                    <th class="titulo_item">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result_cd->num_rows > 0) {
                while ($cd = $result_cd->fetch_assoc()) {
                    $id_cd = $cd['id_cd'];

                    // Buscar os artistas associados ao CD
                    $sql_artistas = "SELECT Artista.nomeArtista FROM CD_Artista 
                                     JOIN Artista ON CD_Artista.id_artista = Artista.id_artista 
                                     WHERE CD_Artista.id_cd = $id_cd";
                    $result_artistas = $conn->query($sql_artistas);

                    $artistas = [];
                    while ($artista = $result_artistas->fetch_assoc()) {
                        $artistas[] = $artista['nomeArtista'];
                    }
                    $artistas_list = !empty($artistas) ? implode(", ", $artistas) : "Nenhum artista associado";

                    // Buscar as músicas associadas ao CD
                    $sql_musicas = "SELECT Musica.nomeMusica FROM CD_Musica 
                                    JOIN Musica ON CD_Musica.id_musica = Musica.id_musica 
                                    WHERE CD_Musica.id_cd = $id_cd";
                    $result_musicas = $conn->query($sql_musicas);

                    $musicas = [];
                    while ($musica = $result_musicas->fetch_assoc()) {
                        $musicas[] = $musica['nomeMusica'];
                    }
                    $musicas_list = !empty($musicas) ? implode(", ", $musicas) : "Nenhuma música associada";

                    // Buscar o desconto aplicado
                    $sql_promocao = "SELECT desconto FROM Promocao WHERE id_cd = $id_cd";
                    $result_promocao = $conn->query($sql_promocao);
                    $desconto = ($result_promocao->num_rows > 0) ? $result_promocao->fetch_assoc()['desconto'] : 0;

                    // Calcular o preço com desconto
                    $preco_original = $cd['preco'];
                    $preco_desconto = $preco_original - ($preco_original * ($desconto / 100));

                    echo "<tr class='informações' >
                            <th class='info' >{$cd['id_cd']}</th>
                            <th class='info' >{$cd['titulo']}</th>
                            <th class='info' ><img src='../{$cd['capa']}' alt='Capa do CD'></th>
                            <th class='info' >{$cd['disponibilidade']}</th>
                            <th class='info' >R$ {$preco_original}</th>
                          <th class='info' >";
                    
                    // Imagem de destaque
                    if ($cd['destaque'] == 'Destaque') {
                        echo "<img src='../imagens/destaque.png' alt='Destaque'>";
                    } else {
                        echo "<img src='../imagens/nao_destaque.png' alt='Não Destaque'>";
                    }

                    echo "</th>
                            <th class='info' >{$cd['anoLancamento']}</th>
                            <th class='info' >{$cd['genero']}</th>
                            <th class='info' >{$cd['descricao_cd']}</th>
                            <th class='info' >{$artistas_list}</th>
                            <th class='info' >{$musicas_list}</th>
                            <th class='info' >
                                <form action='promocao_cd.php' method='POST'>
                                    <input type='hidden' name='id_cd' value='{$cd['id_cd']}'>
                                    <input type='number' name='desconto' value='{$desconto}' min='0' max='100'>%
                                    <button type='submit'>Aplicar Desconto</button>
                                </form>
                            </th>
                            <th class='info' >R$ {$preco_desconto}</th>
                            <th class='info' >{$cd['numero_vendas']}</th>
                            <th class='info' >
                                <a href='../editar/editar_cds.php?id_cd={$cd['id_cd']}' class='link_acao'>Editar</a> <br>
                                <input type='checkbox'id='checkbox1'  value='{$cd['id_cd']}' class='input' /> <label for='checkbox1'></label>

                            </th>
                        </tr>";
                }
            } else {
                echo "<tr><th colspan='14'>Nenhum CD encontrado.</th></tr>";
            }
            ?>
            </tbody>
        </table>
    </section>
</body>
</html>