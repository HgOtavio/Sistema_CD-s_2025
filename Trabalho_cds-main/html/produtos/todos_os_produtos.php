<?php
session_start();
// Função para garantir que o parâmetro seja tratado como array

include "../login_cadastro/conexao.php";

function garantirArray($param) {
    if (is_array($param)) {
        return $param;
    } elseif (!empty($param)) {
        return [$param];
    }
    return [];
}

// Inicializa os filtros
$preco_min = isset($_GET['preco_min']) ? $_GET['preco_min'] : 0;
$preco_max = isset($_GET['preco_max']) ? $_GET['preco_max'] : 1000;
$desconto = isset($_GET['desconto']) ? $_GET['desconto'] : 'todos';
$disponibilidade = isset($_GET['disponibilidade']) ? $_GET['disponibilidade'] : 0;
$artista_nome = isset($_GET['artista_nome']) ? garantirArray($_GET['artista_nome']) : [];
$titulo_cd = isset($_GET['titulo_cd']) ? $_GET['titulo_cd'] : '';
$genero = isset($_GET['genero']) ? garantirArray($_GET['genero']) : [];
$musica_nome = isset($_GET['musica_nome']) ? garantirArray($_GET['musica_nome']) : [];
$ordem_alfabetica = isset($_GET['ordem_alfabetica']) ? $_GET['ordem_alfabetica'] : 'desc';
$busca_geral = isset($_GET['busca_geral']) ? $_GET['busca_geral'] : '';
$mais_vendidos = isset($_GET['mais_vendidos']) ? $_GET['mais_vendidos'] : 'nao';
$ordem_personalizada = isset($_GET['ordem_personalizada']) ? $_GET['ordem_personalizada'] : '';
$anosSelecionados = isset($_GET['anos']) ? $_GET['anos'] : [];
$mostrarDestaques = isset($_GET['destaque']) ? in_array('Destaque', $_GET['destaque']) : false;
$mostrarNaoDestaques = isset($_GET['destaque']) ? in_array('Não Destaque', $_GET['destaque']) : false;

// Consulta para checkboxes
$artistas_result = $conn->query("SELECT * FROM Artista");
$musicas_result = $conn->query("SELECT * FROM Musica");
$generos_result = $conn->query("SELECT DISTINCT genero FROM CD");

// Buscar anos com destaque
$anosResult = $conn->query("SELECT DISTINCT anoLancamento FROM CD WHERE destaque = 'Destaque' ORDER BY anoLancamento DESC");

$sql = "
    SELECT 
        CD.id_cd, 
        CD.titulo, 
        CD.capa, 
        CD.preco, 
        CD.descricao, 
        CD.disponibilidade, 
        COALESCE(P.desconto, 0) AS desconto,
        CD.genero
    FROM CD
    LEFT JOIN CD_Artista ON CD.id_cd = CD_Artista.id_cd
    LEFT JOIN Artista ON CD_Artista.id_artista = Artista.id_artista
    LEFT JOIN Promocao P ON CD.id_cd = P.id_cd
    LEFT JOIN CD_Musica ON CD.id_cd = CD_Musica.id_cd
    LEFT JOIN Musica ON CD_Musica.id_musica = Musica.id_musica
    WHERE CD.preco BETWEEN $preco_min AND $preco_max
    AND (P.desconto >= 0 OR P.desconto IS NULL)
    AND (CD.disponibilidade >= $disponibilidade)
";

// Filtros adicionais
if (!empty($busca_geral)) {
    $sql .= " AND (
        Artista.nomeArtista LIKE '%$busca_geral%' OR 
        CD.titulo LIKE '%$busca_geral%' OR 
        Musica.nomeMusica LIKE '%$busca_geral%'
    )";
}

if (!empty($anosSelecionados)) {
    $anosFiltrados = array_map('intval', $anosSelecionados);
    $anosString = implode(",", $anosFiltrados);
    $sql .= " AND CD.anoLancamento IN ($anosString)";
}

if ($mostrarDestaques && !$mostrarNaoDestaques) {
    $sql .= " AND CD.destaque = 'Sim'";
} elseif (!$mostrarDestaques && $mostrarNaoDestaques) {
    $sql .= " AND (CD.destaque IS NULL OR CD.destaque != 'Sim')";
}

if (!empty($genero)) {
    $generoFiltrado = array_map([$conn, 'real_escape_string'], $genero);
    $sql .= " AND CD.genero IN ('" . implode("','", $generoFiltrado) . "')";
}

if (!empty($artista_nome)) {
    $artistaFiltrado = array_map([$conn, 'real_escape_string'], $artista_nome);
    $sql .= " AND Artista.nomeArtista IN ('" . implode("','", $artistaFiltrado) . "')";
}

if (!empty($musica_nome)) {
    $musicaFiltrado = array_map([$conn, 'real_escape_string'], $musica_nome);
    $sql .= " AND Musica.nomeMusica IN ('" . implode("','", $musicaFiltrado) . "')";
}

if ($desconto == 'com') {
    $sql .= " AND P.desconto > 0";
} elseif ($desconto == 'sem') {
    $sql .= " AND (P.desconto = 0 OR P.desconto IS NULL)";
}

// Agrupamento para evitar duplicidade
$sql .= " GROUP BY CD.id_cd";

// Ordenação
$ordem_sql = '';
switch ($ordem_personalizada) {
    case 'avaliacao':
        $ordem_sql .= "CD.avaliacao_media DESC";
        break;
    case 'recente':
        $ordem_sql .= "CD.anoLancamento DESC";
        break;
    case 'antigo':
        $ordem_sql .= "CD.anoLancamento ASC";
        break;
    case 'preco_min':
        $ordem_sql .= "CD.preco ASC";
        break;
    case 'preco_max':
        $ordem_sql .= "CD.preco DESC";
        break;
    case 'mais_vendidos':
    case 'relevancia':
        $ordem_sql .= "CD.disponibilidade DESC";
        break;
}

if (!empty($ordem_sql)) {
    $sql .= " ORDER BY $ordem_sql";
}


// Executa
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();


// Exibe a mensagem de sucesso, caso exista
if (isset($_SESSION['msg'])) {
    echo "<div style='text-align: center; color: green; font-size: 18px; margin: 20px 0;'>" . $_SESSION['msg'] . "</div>";
    unset($_SESSION['msg']); // Limpa a mensagem da sessão após exibição
}

// Processa a ação de favoritar (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['favoritar'])) {
    // Recebe o ID do CD e o ID do usuário (supondo que o ID do usuário está na sessão)
    $cd_id = $_POST['cd_id'];
    $user_id = $_SESSION['id_usuario'];

    // Verificar se o CD já está favoritado
    $sql_verificar = "SELECT 1 FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("ii", $user_id, $cd_id);
    $stmt->execute();
    $stmt->store_result();

    // Se o CD já estiver favoritado, vamos removê-lo
    if ($stmt->num_rows > 0) {
        // CD favoritado, vamos removê-lo
        $sql_remover = "DELETE FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
        $stmt_remover = $conn->prepare($sql_remover);
        $stmt_remover->bind_param("ii", $user_id, $cd_id);
        $stmt_remover->execute();
        $stmt_remover->close();

        // Mensagem de sucesso
        $_SESSION['msg'] = "CD removido dos favoritos!";
    } else {
        // CD não favoritado, vamos adicionar
        $sql_adicionar = "INSERT INTO Favoritos (id_usuario, id_cd) VALUES (?, ?)";
        $stmt_adicionar = $conn->prepare($sql_adicionar);
        $stmt_adicionar->bind_param("ii", $user_id, $cd_id);
        $stmt_adicionar->execute();
        $stmt_adicionar->close();

        // Mensagem de sucesso
        $_SESSION['msg'] = "CD adicionado aos favoritos!";
    }
    
    // Redirecionar para a página atual após o processamento
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Processa a ação de adicionar ao carrinho (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar_carrinho'])) {
    $cd_id = $_POST['cd_id'];
    $quantidade = 1; // A quantidade pode ser ajustada conforme necessário

    // Verifica se o carrinho já foi inicializado na sessão
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    // Adiciona o CD ao carrinho
    if (isset($_SESSION['carrinho'][$cd_id])) {
        $_SESSION['carrinho'][$cd_id] += $quantidade; // Se o CD já está no carrinho, aumenta a quantidade
    } else {
        $_SESSION['carrinho'][$cd_id] = $quantidade; // Adiciona o CD com a quantidade inicial
    }

    $_SESSION['msg'] = "CD adicionado ao carrinho!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
    <link rel="shortcut icon" href="../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../css/todos_produtos/todos_produtos_filtro.css">
    <link rel="stylesheet" href="../../css/todos_produtos/todos_produtos_orderna.css">
    <link rel="stylesheet" href="../../css/todos_produtos/todos_produtos_produto.css">
    <link rel="stylesheet" href="../../css/todos_produtos/todos_produtos_responsividade.css">
    <link rel="stylesheet" href="../../css/cabeçalhos/cabeçalho_com_login.css">
    <link rel="stylesheet" href="../../css/rodape/rodape.css">
    

    <script src="../../js/todos_produtos/filtro_part1.js" defer></script>
    <script src="../../js/todos_produtos/filtro_part2.js" defer></script>
    <script src="../../js/todos_produtos/ordernar.js" defer></script>
    <script src="../../js/todos_produtos/favoritos.js" defer></script>
    <script src="../../js/cabeçalho/menu.js" defer></script> <!-- Script do menu interativo -->
</head>
<body>

    <!-- Cabeçalho da página (logado) -->
    <header> 

        <div id="parte_de_cima_cab">

            <!-- Logo da página -->
            <a href="#" id="logo"><img src="../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a>  
            
            <!-- Barra de pesquisa -->
            <div id="barra_pesquisa">
                <input type="checkbox" id="check"> <!-- Controle de visibilidade -->
                <div id="complemento_pesquisa">


                <form method="GET" action="">

                    <!-- Campo de pesquisa -->
                    <input  name="busca_geral" type="text" id="input_barra_pesquisa" placeholder="Buscar..." value="<?php echo isset($_GET['busca_geral']) ? htmlspecialchars($_GET['busca_geral']) : ''; ?>" autocomplete="off">

                    <!-- Botão de pesquisa -->
                    <label for="check" id="buttom_lupa">
                        <img src="../../img/cabeçario/icone_lupa.png" alt="Lupa" id="lupa">
                    </label>
                </form>

                </div>
            </div>

            <div id="login_carrinho"> <!-- Login e Carrinho -->
                <a href=""><img src="../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Foto de perfil -->

                <a href="../login_cadastro/perfil/butoes/carrinho.php"><img src="../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>

        <hr style="color: #7b7a7a;"><!-- Linha separadora -->

        <!-- Menu de navegação -->
        <nav class="menu-underline">
            <input type="checkbox" id="menu_toggle" class="menu_toggle"><!-- Menu responsivo -->
            <label for="menu_toggle" class="menu_icon">&#9776;</label> 

            <ul id="menu">

                <li class="p_menu"><a href="#" class="a_menu">Inicio</a></li>
                <li class="p_menu"><a href="#" class="a_menu">Produtos</a></li>
                
                 <!-- Menu suspenso de gêneros musicais -->
                <li class="p_menu" id="menu_genero">

                    <button onclick="aparecer_g('sumir_g')" class="b_menu">
                        Gênero
                    </button>

                    <!-- Submenu de Gêneros -->
                    <ul class="subclasse_menu" id="sumir_g">
                        
                        <ul class="sub_subclasse_menu">
                            <li><a href="#" class="sub_a">Clássica</a></li>
                            <li><a href="#" class="sub_a">Eletrônica</a></li>
                            <li><a href="#" class="sub_a">Forro</a></li>
                            <li><a href="#" class="sub_a">Hip Hop</a></li>
                            <li><a href="#" class="sub_a">MPB</a></li>
                        </ul>
                        
                        <ul class="sub_subclasse_menu">
                            <li><a href="#" class="sub_a">Pagode</a></li>
                            <li><a href="#" class="sub_a">Pop</a></li>
                            <li><a href="#" class="sub_a">Reggae</a></li>
                            <li><a href="#" class="sub_a">Rock</a></li>
                            <li><a href="#" class="sub_a">Sertanejo</a></li>
                        </ul>
                    </ul>
                </li>

                <!-- Menu suspenso para Artistas -->
                <li class="p_menu" id="arredondar_b">

                    <button onclick="aparecer_a('sumir_a')" class="b_menu">
                        Artistas
                    </button>

                    <!-- Submenu de Artistas -->
                    <ul class="subclasse_menu_a" id="sumir_a">
                        
                        <ul class="sub_subclasse_menu">
                            <li><a href="#" class="sub_a">Ludwing Beethowen</a></li>
                            <li><a href="#" class="sub_a">Marshmello</a></li>
                            <li><a href="#" class="sub_a">Luiz Gonzaga</a></li>
                            <li><a href="#" class="sub_a">Snoop Dogg</a></li>
                            <li><a href="#" class="sub_a">Maria Bethânia</a></li>
                        </ul>
                        
                        <ul class="sub_subclasse_menu">
                            <li><a href="#" class="sub_a">Péricles</a></li>
                            <li><a href="#" class="sub_a">Michael Jackson</a></li>
                            <li><a href="#" class="sub_a">Bob Marley</a></li>
                            <li><a href="#" class="sub_a">Elvis Presley</a></li>
                            <li><a href="#" class="sub_a">Luan Santana</a></li>
                        </ul>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>
    <section>
        <div id="caminho">
            <a href="../pagina_inicial/index_logado.php" id="home" class="link_caminho">
                <img src="../../img/todos_produtos/icone_home.png" alt="Home" id="img_home">
                <p>Home</p>
            </a>
        </div>
    </section>
    <form method="get" action="">
    <section id="meio">
            
 
                <div id="filtro">
                    <div id="cabeca">
                        <h1 id="titulo_filtro">Filtro</h1>
                        <button id="butao_filtro">Filtrar</button>
                    </div>
                    <hr id="linha">

                    <div>
                        <div class="part_cima">
                            <h1 class="titulo">Gênero</h1>
                            <img src="../../img/todos_produtos/icone_seta_direita.png" alt="Seta para abrir seleção" id="button_filtro_generos" class="setas_filtro">
                        </div>
                        <div id="generos" class="contener">
                        <?php while ($genero_item = $generos_result->fetch_assoc()) { ?>
                        <label class="itens">
                            <input  class="isput" type="checkbox" name="genero[]" value="<?php echo $genero_item['genero']; ?>" <?php echo (in_array($genero_item['genero'], $genero)) ? 'checked' : ''; ?>>
                            <?php echo ucfirst($genero_item['genero']); ?>
                        </label><br>
                    <?php } ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="part_cima">
                            <h1 class="titulo">Artistas</h1>
                            <img src="../../img/todos_produtos/icone_seta_direita.png" alt="Seta para abrir seleção" id="button_filtro_artistas" class="setas_filtro">
                        </div>
                        <div id="artistas" class="contener">
                        <?php while ($artista = $artistas_result->fetch_assoc()) { ?>
                        <label class="itens">
                            <input class="inpt" type="checkbox" name="artista_nome[]" value="<?php echo $artista['nomeArtista']; ?>" <?php echo (in_array($artista['nomeArtista'], $artista_nome)) ? 'checked' : ''; ?>>
                            <?php echo $artista['nomeArtista']; ?>
                                </label><br>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div>
                        <div class="part_cima">
                            <h1 class="titulo">Musicas</h1>
                            <img src="../../img/todos_produtos/icone_seta_direita.png" alt="Seta para abrir seleção" id="button_filtro_musicas" class="setas_filtro">
                        </div>
                        <div id="musicas" class="contener">
                        <?php while ($musica = $musicas_result->fetch_assoc()) { ?>
                                <label class="itens">
                                    <input class="inpu" type="checkbox" name="musica_nome[]" value="<?php echo $musica['nomeMusica']; ?>" <?php echo (in_array($musica['nomeMusica'], $musica_nome)) ? 'checked' : ''; ?>>
                                    <?php echo $musica['nomeMusica']; ?>
                                </label><br>
                                <?php } ?>
                        </div>
                    </div>
                    <div>
                        <div class="part_cima">
                            <h1 class="titulo">Destaques Do Ano</h1>
                            <img src="../../img/todos_produtos/icone_seta_direita.png" alt="Seta para abrir seleção" id="button_filtro_destaque" class="setas_filtro">
                        </div>
                        <div id="destaque" class="contener">
                        <?php while ($ano = $anosResult->fetch_assoc()): ?>
                            <label class="itens" >
                                <input  class="itens" id="checkbox31" class="input"  type="checkbox" name="anos[]" value="<?php echo $ano['anoLancamento']; ?>"
                                    <?php if (in_array($ano['anoLancamento'], $anosSelecionados)) echo 'checked'; ?>>
                                <?php echo $ano['anoLancamento']; ?>
                            </label>
                        <?php endwhile; ?>
                        </div>
                    </div>
            
                    <div>
                        <h1 class="titulo" id="preço">Preço: <p id="valor">R$<span id="valor_preco"><?php echo $preco_max; ?></span></p></h1> 
                        <lable id="valor_ponta">R$5<input  class="input" type="range" name="preco_max" min="0" max="1000" step="5"
                                            value="<?php echo $preco_max; ?>"
                                            oninput="document.getElementById('valor_preco').textContent = this.value;">
                            <input type="hidden" name="preco_min" class="input" value="0" >R$1000</lable>
                    </div>
                </div>

                <div id="main">
                    <div id="part_cima_produtos">
                        <p id="quantidade"><?php echo $result->num_rows; ?> Produtos</p>
                        <div>
                            <div id="ordenar_produtos">
                                <p id="ordenar">Ordenar Por</p>
                                <img src="../../img/todos_produtos/icone_seta_direita.png" alt="Seta" id="seta_ordenar">
                            </div>

                            <div id="formas_de_ordenar">
                                <div id="forma_ordenar">

                                <button type="submit" id="button_ordenar" name="ordem_personalizada" value="avaliacao" <?php if ($ordem_personalizada == 'avaliacao')  ?>>Mais bem avaliados</button>
                        <button type="submit" id="button_ordenar"href="todos_os_produtos.php">  TODOS</button>
                                            <button type="submit" id="button_ordenar" name="ordem_personalizada" value="recente" <?php if ($ordem_personalizada == 'recente')  ?>>Mais recentes</button>
                                            <button type="submit" id="button_ordenar" name="ordem_personalizada" value="antigo" <?php if ($ordem_personalizada == 'antigo')  ?>>Mais antigos</button>
                                            <button type="submit" id="button_ordenar" name="ordem_personalizada" value="preco_baixo" <?php if ($ordem_personalizada == 'preco_baixo'); ?>>Preço: menor para maior</button>
                                            <button type="submit" id="button_ordenar" name="ordem_personalizada" value="preco_alto" <?php if ($ordem_personalizada == 'preco_alto'); ?>>Preço: maior para menor</button>
                                            <button type="submit" id="button_ordenar" name="ordem_personalizada" value="mais_vendidos" <?php if ($ordem_personalizada == 'mais_vendidos') ; ?>>Mais vendidos</button>
                                            <button type="submit" id="button_ordenar" name="ordem_personalizada" value="relevancia" <?php if ($ordem_personalizada == 'relevancia') ; ?>>Mais relevantes</button>
                                </div>
                            </div>
                        </div>
            </div>
            </form>


            <div class="fileira_produtos">

            <?php while ($cd = $result->fetch_assoc()) { ?>

                <div>
                    <div class="produto">
                           <img src="../../img/<?php echo $cd['capa']; ?>" alt="<?php echo $cd['titulo']; ?>" class="img_capa_cd">
                           <div>
                               <div>
                                   <h1 class="nome_cd"><?php echo $cd['titulo']; ?></h1>
                                   <p class="descricao"><a href="#" class="artista"><?php echo substr($cd['descricao'], 0, 60); ?></a></p>
                               </div>
                               <form  method="POST"  action="">
                <input type="hidden" name="cd_id" value="<?php echo $cd['id_cd']; ?>">
                <button type="submit" name="adicionar_carrinho" class="carrinho">Adicionar ao Carrinho</button>
            </form>                             
              <div class="baixo_part">
                                   <div>
                                       <h1 class="valor">R$<?php echo number_format($cd['preco'], 2, ',', '.'); ?></h1>
                                       <?php if ($cd['desconto'] > 0) { ?>
                <p class="promo">R$ <?php echo number_format($cd['preco'] * (1 - $cd['desconto'] / 100), 2, ',', '.'); ?></p>
            <?php } ?>
                                   </div>
                    <form method="post" action="favoritar.php" id="favoritar-form">
    <input type="hidden" name="id_cd" value="<?= $cd['id_cd'] ?>">
    <input type="hidden" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>">

    <?php
    // Verificar se o usuário está logado
    if (isset($_SESSION['id_usuario'])) {
        $id_usuario = $_SESSION['id_usuario'];
    } else {
        echo "Você precisa estar logado para favoritar CDs.";
        exit;
    }

    // Verificar se o CD já está favoritado
    $sql_verificar = "SELECT 1 FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("ii", $id_usuario, $cd['id_cd']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // CD já favoritado, mostrar imagem de favorito ativo
        $img_favorito = '../../img/todos_produtos/icone_favoritos_selecionado.png'; 
    } else {
        // CD não favoritado, mostrar imagem de favorito inativo
        $img_favorito = '../../img/todos_produtos/icone_favoritos.png'; 
    }
    $stmt->close();
    ?>

    <button type="button" class="btn-favorito" id="favorito-button">
        <img src="<?= $img_favorito ?>" alt="Favoritar" class="img_favorito" id="favorito-img">
    </button>
</form>

<script>
    // Seleciona o botão e a imagem
    const favoritoButton = document.getElementById("favorito-button");
    const favoritoImg = document.getElementById("favorito-img");
    const form = document.getElementById("favoritar-form");

    // Adiciona o evento de clique
    favoritoButton.addEventListener("click", function() {
        // Envia os dados do formulário com AJAX
        const formData = new FormData(form);
        
        fetch('favoritar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Verifica se o favorito foi adicionado ou removido
            if (data.favoritado) {
                // CD favoritado, troca a imagem
                favoritoImg.src = '../../img/todos_produtos/icone_favoritos_selecionado.png';
            } else {
                // CD não favoritado, troca a imagem
                favoritoImg.src = '../../img/todos_produtos/icone_favoritos.png';
            }
        })
        .catch(error => {
            console.error('Erro ao favoritar:', error);
        });
    });
</script>


                     </div>
           </div>
                    </div>
                    <a href="produto.php?id_cd=<?php echo $cd['id_cd']; ?>" class="link_produto2"> <div class="butao">Ver Mais</div></a>
                </div>
            <?php } ?>



             
            </div>
        
        </div>
    </section>

<footer>

    <!-- Seção com a logo no rodapé, contendo duas linhas laterais -->
    <section id="logo_rodape">
        <div class="linha_logo_rodape"></div> <!-- Linha à esquerda -->
        <!-- Logo central -->
        <img src="../../img/cabeçario/logo.png" alt="Logo" id="img_rodape_logo">
        <div class="linha_logo_rodape"></div> <!-- Linha à direita -->
    </section>

    <!-- Corpo principal do rodapé -->
    <section id="corpo_rodape">
        <div id="parte_de_cima_rodape">
            <h1 class="titulo">Sobre nós</h1>
            <!-- Texto de descrição sobre a empresa ou site -->
            <p id="sobre_texto_rodape">
                Somos uma loja online apaixonada por música, dedicada a quem valoriza a experiência de ouvir um bom CD. Trabalhamos com títulos novos e selecionados, dos clássicos aos lançamentos, sempre com qualidade e cuidado.Nossa missão é manter viva a cultura do CD, oferecendo um atendimento atencioso, envios rápidos e uma experiência de compra segura. Se você ama música de verdade, está no lugar certo. 
            </p>
        </div>

        <div id="parte_de_baixo_rodape">
            <!-- Seção: Política Comercial -->
            <div class="topicos_rodape">
                <h1 class="titulo">Política comercial</h1>
                <ul>
                    <li class="lista_rodape"><a href="../rodape/politica_comercial.html#trocas_devolucoes" class="link_rodape">Trocas e Devoluções</a></li>
                    <li class="lista_rodape"><a href="../rodape/politica_comercial.html#termos_uso" class="link_rodape">Termos de uso</a></li>
                    <li class="lista_rodape"><a href="../rodape/politica_comercial.html#politica_privacidade" class="link_rodape">Políticas de privacidade</a></li>
                    <li class="lista_rodape"><a href="../rodape/politica_comercial.html#direito_arrependimento" class="link_rodape">Direito de arrependimento</a></li>
                </ul>
            </div>

            <!-- Seção: Suporte -->
            <div class="topicos_rodape">
                <h1 class="titulo">Suporte</h1>
                <ul>
                    <li class="lista_rodape"><a href="../rodape/perguntas_frequentes.html" class="link_rodape">Perguntas Frequentes</a></li>
                    <li class="lista_rodape"><a href="../rodape/avaliar.html" class="link_rodape">Avaliar</a></li>
                    <li class="lista_rodape"><a href="#" class="link_rodape">Recomendar Produtos</a></li>
                </ul>
            </div>

            <!-- Seção: Atendimento -->
            <div class="topicos_rodape">
                <h1 class="titulo">Atendimento</h1>
                <ul>
                    <li class="lista_rodape">
                        <a href="mailto:codedisc@gmail.com" class="link_rodape">
                            <img src="../../img/rodape/contato/icone_email.png" alt="Ícone email" class="img_rodape_contatos"> Email
                        </a>
                    </li>
                    <li class="lista_rodape">
                        <a href="tel:+5585900000000" class="link_rodape">
                            <img src="../../img/rodape/contato/icone_telefone.png" alt="Ícone telefone" class="img_rodape_contatos"> Telefone
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Linha separadora do rodapé -->
    <hr id="hr_rodape">

    <!-- Seção final do rodapé -->
    <section id="final_rodape">
        <div>
            <h1 class="titulo">Formas de pagamento</h1>
            <!-- Ícones representando formas de pagamento -->
            <img src="../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
        </div>

        <!-- Seção de redes sociais -->
        <div id="redes_sociais_rodape">
            <h1 class="titulo">Redes Sociais</h1>
           <!-- Ícones representando redes sociais -->
           <a href="https://www.instagram.com/code_disc_oficial"><img src="../../img/rodape/redes_sociais/icone_instagram.png" alt="Instagram" class="img_rodape_social"></a>
           <a href="https://www.facebook.com/code_disc_oficial"><img src="../../img/rodape/redes_sociais/icone_facebook.png" alt="Facebook" class="img_rodape_social"></a>
           <a href="https://twitter.com/code_disc_oficial"><img src="../../img/rodape/redes_sociais/icone_x.png" alt="X (Twitter)" class="img_rodape_social"></a>
           <a href="https://www.tiktok.com/code_disc_oficial"><img src="../../img/rodape/redes_sociais/icone_tiktok.png" alt="TikTok" class="img_rodape_social"></a>
           <a href="https://open.spotify.com/user/code_disc_oficial"><img src="../../img/rodape/redes_sociais/icone_spotify.png" alt="Spotify" class="img_rodape_social"></a>
           <a href="https://www.youtube.com/code_disc_oficial"><img src="../../img/rodape/redes_sociais/icone_youtube.png" alt="YouTube" class="img_rodape_social"></a>
        </div>
    </section>
</footer>

<script src="../../js/todos_produtos/produto/nome_cd.js" defer></script>
</body>
</html>
