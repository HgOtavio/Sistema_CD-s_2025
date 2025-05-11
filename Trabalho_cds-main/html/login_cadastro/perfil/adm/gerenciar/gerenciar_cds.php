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

// Consultas para opções de filtro
$sql_titulos = "SELECT DISTINCT titulo FROM CD";
$result_titulos = $conn->query($sql_titulos);

$sql_generos = "SELECT DISTINCT genero FROM CD";
$result_generos = $conn->query($sql_generos);

$sql_artistas = "SELECT DISTINCT nomeArtista FROM Artista";
$result_artistas = $conn->query($sql_artistas);

$sql_musicas = "SELECT DISTINCT nomeMusica FROM Musica";
$result_musicas = $conn->query($sql_musicas);

$sql_anos = "SELECT DISTINCT anoLancamento FROM CD";
$result_anos = $conn->query($sql_anos);

$sql_disponibilidades = "SELECT DISTINCT disponibilidade FROM CD";
$result_disponibilidades = $conn->query($sql_disponibilidades);

$sql_preco_max = "SELECT DISTINCT preco FROM CD";
$result_preco_max = $conn->query($sql_preco_max);

// Busca dados do usuário logado
$sql_usuario_logado = "SELECT login, foto_perfil FROM Usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_usuario_logado);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_usuario_logado = $stmt->get_result();
$usuario_logado = $result_usuario_logado->fetch_assoc();

if (!$usuario_logado) {
    header("Location: ../php/login.php");
    exit();
}

$login_usuario_logado = $usuario_logado['login'];
$foto_perfil_usuario = $usuario_logado['foto_perfil'];

// Define caminho da foto
$caminho_foto = "../../../../../img/php_cliente/uploads/" . basename($foto_perfil_usuario);
$foto_exibir = (!empty($foto_perfil_usuario) && file_exists($caminho_foto)) 
    ? $caminho_foto 
    : "../php_cliente/uploads/default.png";

// Consulta principal sem JOINs
$sql_cd = "SELECT CD.id_cd, CD.titulo, CD.capa, CD.disponibilidade, CD.preco, CD.destaque, CD.anoLancamento, CD.genero, CD.descricao AS descricao_cd, CD.numero_vendas 
           FROM CD
           WHERE 1=1";

// Filtros aplicados à consulta
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

if (!empty($filtro_destaque)) {
    if ($filtro_destaque == 'Destaque') {
        $sql_cd .= " AND destaque = 'Destaque'";
    } elseif ($filtro_destaque == 'Não Destaque') {
        $sql_cd .= " AND (destaque IS NULL OR destaque != 'Destaque')";
    }
}

if (!empty($filtro_artista)) {
    $sql_cd .= " AND id_cd IN (
        SELECT id_cd FROM CD_Artista 
        JOIN Artista ON CD_Artista.id_artista = Artista.id_artista 
        WHERE nomeArtista LIKE '%$filtro_artista%'
    )";
}

if (!empty($filtro_musica)) {
    $sql_cd .= " AND id_cd IN (
        SELECT id_cd FROM CD_Musica 
        JOIN Musica ON CD_Musica.id_musica = Musica.id_musica 
        WHERE nomeMusica LIKE '%$filtro_musica%'
    )";
}

if (!empty($filtro_preco_min)) {
    $sql_cd .= " AND preco >= $filtro_preco_min";
}

if (!empty($filtro_preco_max)) {
    $sql_cd .= " AND preco <= $filtro_preco_max";
}

// Executa a consulta final
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
                    <a href="../../adm.php"><img src="<?php echo $foto_exibir; ?>" alt="Perfil" id="Perfil"></a><!-- Imagem de perfil -->

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
                <!-- Filtro Título -->
                <p class="p_filtro">Título:
                    <input type="text" class="input_filtro" name="titulo" value="<?= $filtro_titulo ?>" list="titulos_sugestoes">
                    <datalist id="titulos_sugestoes">
                        <?php while ($titulo = $result_titulos->fetch_assoc()) { ?>
                            <option value="<?= $titulo['titulo'] ?>">
                        <?php } ?>
                    </datalist>
                </p>

                <!-- Filtro Gênero -->
                <p class="p_filtro">Gênero:
                    <input type="text" class="input_filtro" name="genero" value="<?= $filtro_genero ?>" list="generos_sugestoes">
                    <datalist id="generos_sugestoes">
                        <?php while ($genero = $result_generos->fetch_assoc()) { ?>
                            <option value="<?= $genero['genero'] ?>">
                        <?php } ?>
                    </datalist>
                </p>
            </div>

            <div class="lado">
                <!-- Filtro Lançamento (AnoLancamento) -->
                <p class="p_filtro">Lançamento:
                    <input type="text" class="input_filtro ano" name="ano" value="<?= $filtro_ano ?>" list="anos_sugestoes">
                    <datalist id="anos_sugestoes">
                        <?php while ($ano = $result_anos->fetch_assoc()) { ?>
                            <option value="<?= $ano['anoLancamento'] ?>">
                        <?php } ?>
                    </datalist>
                </p>

                <!-- Filtro Disponibilidade (alterado de "Durabilidade") -->
                <p class="p_filtro">Disponibilidade:
                    <input type="text" class="input_filtro tempo" name="disponibilidade" value="<?= $filtro_disponibilidade ?>" list="disponibilidades_sugestoes">
                    <datalist id="disponibilidades_sugestoes">
                        <?php while ($disponibilidade = $result_disponibilidades->fetch_assoc()) { ?>
                            <option value="<?= $disponibilidade['disponibilidade'] ?>">
                        <?php } ?>
                    </datalist>
                </p>
            </div>

            <div class="lado">
               

                <!-- Filtro Preço Mínimo -->
                <p class="p_filtro">Preço Min:
                    <input type="number" step="0.01" class="input_filtro preco" name="preco_min" value="<?= $filtro_preco_min ?>" list="preco_min_sugestoes">
                    <datalist id="preco_min_sugestoes">
                        <?php while ($preco = $result_preco_max->fetch_assoc()) { ?>
                            <option value="<?= $preco['preco'] ?>">
                        <?php } ?>
                    </datalist>
                </p>
                 <!-- Filtro Preço Máximo -->
                 <p class="p_filtro">Preço Max:
                    <input type="number" step="0.01" class="input_filtro preco" name="preco_max" value="<?= $filtro_preco_max ?>" list="preco_max_sugestoes">
                    <datalist id="preco_max_sugestoes">
                        <?php while ($preco = $result_preco_max->fetch_assoc()) { ?>
                            <option value="<?= $preco['preco'] ?>">
                        <?php } ?>
                    </datalist>
                </p>
            </div>
        </div>

        <div class="separar_pc">
            <div class="lado">
                <!-- Filtro Artistas -->
                <p class="p_filtro">Artistas:
                    <input name="artista" type="text" class="input_filtro" value="<?= $filtro_artista ?>" list="artistas_sugestoes">
                    <datalist id="artistas_sugestoes">
                        <?php while ($artista = $result_artistas->fetch_assoc()) { ?>
                            <option value="<?= $artista['nomeArtista'] ?>">
                        <?php } ?>
                    </datalist>
                </p>

                <!-- Filtro Músicas -->
                <p class="p_filtro">Músicas:
                    <input name="musica" type="text" class="input_filtro" value="<?= $filtro_musica ?>" list="musicas_sugestoes">
                    <datalist id="musicas_sugestoes">
                        <?php while ($musica = $result_musicas->fetch_assoc()) { ?>
                            <option value="<?= $musica['nomeMusica'] ?>">
                        <?php } ?>
                    </datalist>
                </p>
            </div>

            <p class="p_filtro">Destaque:
                <input type="text" class="input_filtro" name="destaque" value="<?= $filtro_destaque ?>" list="destaque_sugestoes">
                <datalist id="destaque_sugestoes">
                    <option value="Sim">
                    <option value="Não">
                </datalist>
            </p>
        </div>
    </div>

    <div>
        <button id="button_filtro" type="submit">Procurar</button>
        <button id="button_filtro" href="gerenciar_cds.php">Todos</button>
    </div>
</form>

    </section>

    <button class="button_voltar"><a href="../adicionar/add_cds.php" class="link_voltar">Adicionar CDs</a></button>
    <form action="deletar_cd.php" method="POST">

    <button id="button_excluir" type="submit">Excluir</button>
    
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

        // Buscar artistas
        $sql_artistas = "SELECT A.nomeArtista FROM CD_Artista CA
                         JOIN Artista A ON CA.id_artista = A.id_artista
                         WHERE CA.id_cd = ?";
        $stmt_artistas = $conn->prepare($sql_artistas);
        $stmt_artistas->bind_param("i", $id_cd);
        $stmt_artistas->execute();
        $result_artistas = $stmt_artistas->get_result();

        $artistas = [];
        while ($artista = $result_artistas->fetch_assoc()) {
            $artistas[] = $artista['nomeArtista'];
        }
        $artistas_list = !empty($artistas) ? implode(", ", $artistas) : "Nenhum artista associado";

        // Buscar músicas
        $sql_musicas = "SELECT M.nomeMusica FROM CD_Musica CM
                        JOIN Musica M ON CM.id_musica = M.id_musica
                        WHERE CM.id_cd = ?";
        $stmt_musicas = $conn->prepare($sql_musicas);
        $stmt_musicas->bind_param("i", $id_cd);
        $stmt_musicas->execute();
        $result_musicas = $stmt_musicas->get_result();

        $musicas = [];
        while ($musica = $result_musicas->fetch_assoc()) {
            $musicas[] = $musica['nomeMusica'];
        }
        $musicas_list = !empty($musicas) ? implode(", ", $musicas) : "Nenhuma música associada";

        // Buscar desconto
        $sql_promocao = "SELECT desconto FROM Promocao WHERE id_cd = ?";
        $stmt_promocao = $conn->prepare($sql_promocao);
        $stmt_promocao->bind_param("i", $id_cd);
        $stmt_promocao->execute();
        $result_promocao = $stmt_promocao->get_result();
        $desconto = ($result_promocao->num_rows > 0) ? $result_promocao->fetch_assoc()['desconto'] : 0;

        // Cálculo do preço com desconto
        $preco_original = $cd['preco'];
        $preco_desconto = $preco_original - ($preco_original * ($desconto / 100));
        $preco_desconto = number_format($preco_desconto, 2, ',', '.');
        $preco_original = number_format($preco_original, 2, ',', '.');

        echo "<tr class='informações'>
                <th class='info'>{$cd['id_cd']}</th>
                <th class='info'>{$cd['titulo']}</th>
                <th class='info'><img src='../../../../../img/{$cd['capa']}' alt='Capa do CD' class='capa'></th>
                <th class='info'>{$cd['disponibilidade']}</th>
                <th class='info'>R$ {$preco_original}</th>
                <th class='info'>";
        
        echo ($cd['destaque'] === 'Destaque')
            ? "<img src='../../../../../img/gerenciar/cd/icone_destaque.png' alt='Destaque' class='destaque'>"
            : "<img src='../../../../../img/gerenciar/cd/icone_destaque2.png' alt='Não Destaque' class='destaque'>";

        echo "</th>
              <th class='info'>{$cd['anoLancamento']}</th>
              <th class='info'>{$cd['genero']}</th>
              <th class='info'>{$cd['descricao_cd']}</th>
              <th class='info'>{$artistas_list}</th>
              <th class='info'>{$musicas_list}</th>
              <th class='info'>
                  <form action='promocao_cd.php' method='POST'>
                      <input type='hidden' name='id_cd' value='{$cd['id_cd']}'>
                      <input type='number' name='desconto' value='{$desconto}' min='0' max='100'>%
                      <button type='submit'>Aplicar Desconto</button>
                  </form>
              </th>
              <th class='info'>R$ {$preco_desconto}</th>
              <th class='info'>{$cd['numero_vendas']}</th>
              <th class='info'>
                  <a href='../editar/editar_cds.php?id_cd={$cd['id_cd']}' class='link_acao'>Editar</a><br>
                  <input type='checkbox' name='cd_selecionadas[]' value='{$cd['id_cd']}' class='input' id='cd_{$cd['id_cd']}'>
                  <label for='cd_{$cd['id_cd']}'></label>
              </th>
            </tr>";
    }
} else {
    echo "<tr><th colspan='15'>Nenhum CD encontrado.</th></tr>";
}
?>

            </tbody>
        </table>
    </section>
    </form>

</body>
</html>