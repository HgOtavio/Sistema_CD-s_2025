<?php
session_start();
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "cliente") {
    header("Location: ../php/login.php");
    exit();
}

include "../../../login_cadastro/conexao.php";

$id_usuario = $_SESSION['id_usuario'];
$res = $conn->query("SELECT * FROM Usuario WHERE id_usuario = $id_usuario");
$usuarioLogado = $res->fetch_assoc();

// Consultas de gênero, artista e música
$queryGenero = "SELECT DISTINCT genero FROM CD LIMIT 10";
$queryArtista = "SELECT nomeArtista FROM Artista LIMIT 10";
$queryMusica = "SELECT nomeMusica FROM Musica LIMIT 10";

$generos = $conn->query($queryGenero)->fetch_all(MYSQLI_ASSOC);
$artistas = $conn->query($queryArtista)->fetch_all(MYSQLI_ASSOC);
$musicas = $conn->query($queryMusica)->fetch_all(MYSQLI_ASSOC);

// Query para pegar os CDs favoritos do usuário e o desconto da promoção, se houver (JOINs desnecessários removidos)
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
    FROM Favoritos
    INNER JOIN CD ON Favoritos.id_cd = CD.id_cd
    LEFT JOIN Promocao P ON CD.id_cd = P.id_cd
    WHERE Favoritos.id_usuario = ?
";

// Processa a ação de favoritar (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['favoritar'])) {
    $cd_id = $_POST['cd_id'];
    $user_id = $_SESSION['id_usuario'];

    // Verificar se o CD já está favoritado
    $sql_verificar = "SELECT 1 FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("ii", $user_id, $cd_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // CD já está favoritado — remover
        $sql_remover = "DELETE FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
        $stmt_remover = $conn->prepare($sql_remover);
        $stmt_remover->bind_param("ii", $user_id, $cd_id);
        $stmt_remover->execute();
        $stmt_remover->close();
        $_SESSION['msg'] = "CD removido dos favoritos!";
    } else {
        // CD não está favoritado — adicionar
        $sql_adicionar = "INSERT INTO Favoritos (id_usuario, id_cd) VALUES (?, ?)";
        $stmt_adicionar = $conn->prepare($sql_adicionar);
        $stmt_adicionar->bind_param("ii", $user_id, $cd_id);
        $stmt_adicionar->execute();
        $stmt_adicionar->close();
        $_SESSION['msg'] = "CD adicionado aos favoritos!";
    }

    // Redirecionar para a página atual
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Executa a query principal
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Aqui você pode usar $result para exibir os CDs favoritos
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritos</title>
    <link rel="shortcut icon" href="../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../css/favoritos/favoritos.css">
    <link rel="stylesheet" href="../../../../css/cabeçalhos/cabeçalho_com_login.css"> <!-- Estilos do cabeçalho -->
    <link rel="stylesheet" href="../../../../css/rodape/rodape.css">

    <script src="../../../../js/favoritos/favoritos_ordenar.js" defer></script>
    <script src="../../../../js/cabeçalho/menu.js" defer></script>

</head>
<body>

    <!-- Cabeçalho da página (logado) -->
    <header> 

        <div id="parte_de_cima_cab">

            <!-- Logo da página -->
            <a href="#" id="logo"><img src="../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Barra de pesquisa -->
            <div id="barra_pesquisa">
                <input type="checkbox" id="check"> <!-- Controle de visibilidade -->
                <div id="complemento_pesquisa">

                    <!-- Campo de pesquisa -->
                    <input type="text" id="input_barra_pesquisa" placeholder="Buscar..." >

                    <!-- Botão de pesquisa -->
                    <label for="check" id="buttom_lupa">
                        <img src="../../../../img/cabeçario/icone_lupa.png" alt="Lupa" id="lupa">
                    </label>
                </div>
            </div>

            <div id="login_carrinho"> <!-- Login e Carrinho -->
                   <?php
// Verifica se o usuário tem uma foto de perfil
if (!empty($usuarioLogado['foto_perfil'])):
    // Define a URL de destino com base no tipo de usuário
    if ($usuarioLogado['tipo'] === 'admin') {
        $linkPerfil = "../admin.php";
    } else {
        $linkPerfil = "../user.php";
    }
?>
    <a href="<?php echo $linkPerfil; ?>">
        <img src="../../../../img/php_cliente//<?php echo htmlspecialchars($usuarioLogado['foto_perfil']); ?>" id="Perfil" alt="Perfil">
    </a>
<?php else:
    // Se não tiver foto, mesma lógica para o link com imagem padrão
    if ($usuarioLogado['tipo'] === 'admin') {
        $linkPerfil = "../admin.php";
    } else {
        $linkPerfil = "../user.php";
    }
?>
    <a href="<?php echo $linkPerfil; ?>">
        <img src="../../img/uploads/perfil_padrao.jpg" alt="Perfil padrão" id="Perfil">
    </a>
<?php endif; ?>

                <a href="#"><img src="../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>

        <hr style="color: #7b7a7a;"><!-- Linha separadora -->

        <!-- Menu de navegação -->
        <nav class="menu-underline">
            <input type="checkbox" id="menu_toggle" class="menu_toggle"><!-- Menu responsivo -->
            <label for="menu_toggle" class="menu_icon">&#9776;</label> 

            <ul id="menu">

               <li class="p_menu"><a href="../../../pagina_inicial/index_logado.php" class="a_menu">Inicio</a></li>
                <li class="p_menu"><a href="../../../produtos/todos_os_produtos.php?" class="a_menu">Produtos</a></li>
                
                  <!-- Menu suspenso de gêneros musicais -->
                <li class="p_menu" id="menu_genero">

                    <button onclick="aparecer_g('sumir_g')" class="b_menu">
                        Gênero
                    </button>

                    <!-- Submenu de Gêneros -->
                    <ul class="subclasse_menu" id="sumir_g">
                        
                        <ul class="sub_subclasse_menu">
                        <?php foreach ($generos as $genero): ?>
                                 <li><a href="../../../produtos/todos_os_produtos.php?genero=<?= htmlspecialchars($genero['genero']) ?>" class="sub_a"><?= htmlspecialchars($genero['genero']) ?></a></li>
                        <?php endforeach; ?>
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
                        <?php foreach ($artistas as $artista): ?>
                           <li><a href="../../../produtos/todos_os_produtos.php??busca_geral=<?= htmlspecialchars($artista['nomeArtista']) ?>"  class="sub_a"><?= htmlspecialchars($artista['nomeArtista']) ?></a></li>
                        <?php endforeach; ?>
                        </ul>
                        
                       
                    </ul>
                </li>
            </ul>
        </nav>
    </header>
<main>
    <section>
        <div id="caminho">
            <a href="#" id="home" class="link_caminho">
                <img src="../../../../img/favoritos/icone_home.png" alt="Home" id="img_home">
                <p>Home</p>
            </a>
            <div class="proxima_part">
                <img src="../../../../img/favoritos/icone_seta_direita.png" alt="Seta" class="seta">
                <a href="#" class="link_caminho">Favoritos</a>
            </div>
        </div>
    </section>
    
  <h1 id="titulo">Favoritos</h1>

<div id="main">

    <!-- Exibe a quantidade total de produtos favoritos encontrados -->
    <p id="quantidade"><?php echo $result->num_rows; ?> Produtos</p>

<?php if ($result->num_rows === 0): ?>
    <!-- Caso não tenha nenhum favorito -->
    <p id="nada">Não há favoritos</p>
<?php else: ?>
    <!-- Container que segura todos os produtos, para organizar um ao lado do outro -->
    <div class="fileira_produtos">
    <?php while ($cd = $result->fetch_assoc()) { ?>
        <!-- Início de cada produto -->
        <div class="produto">
            <!-- Exibe a imagem da capa do CD -->
            <img src="../../../../img/<?php echo $cd['capa']; ?>" alt="<?php echo $cd['titulo']; ?>" class="img_capa_cd">

            <div>
                <div>
                    <!-- Exibe o título do CD -->
                    <h1 class="nome_cd"><?php echo $cd['titulo']; ?></h1>

                    <!-- Exibe um pedaço da descrição (máx 60 caracteres) -->
                    <p class="descricao">
                        <a href="" class="artista">
                            <?php echo substr($cd['descricao'], 0, 60); ?>
                        </a>
                    </p>
                </div>

                <!-- Formulário para adicionar o item no carrinho -->
                <form method="POST" action="">
                    <input type="hidden" name="cd_id" value="<?php echo $cd['id_cd']; ?>">
                    <button type="submit" name="adicionar_carrinho" class="carrinho">Adicionar ao Carrinho</button>
                </form>              

                <div class="baixo_part">
                    <div>
                        <!-- Exibe o preço original -->
                        <h1 class="valor">R$<?php echo number_format($cd['preco'], 2, ',', '.'); ?></h1>

                        <!-- Exibe o preço com desconto (se houver) -->
                        <?php if ($cd['desconto'] > 0) { ?>
                            <p class="promo">
                                R$ <?php echo number_format($cd['preco'] * (1 - $cd['desconto'] / 100), 2, ',', '.'); ?>
                            </p>
                        <?php } ?>
                    </div>

                    <!-- Formulário de favoritos -->
                    <form method="post" action="../../../produtos/favoritar.php" id="favoritar-form">
                        <input type="hidden" name="id_cd" value="<?= $cd['id_cd'] ?>">
                        <input type="hidden" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>">

                        <?php
                        // Verifica se o usuário está logado
                        if (isset($_SESSION['id_usuario'])) {
                            $id_usuario = $_SESSION['id_usuario'];
                        } else {
                            echo "Você precisa estar logado para favoritar CDs.";
                            exit;
                        }

                        // Verifica se o produto já está favoritado
                        $sql_verificar = "SELECT 1 FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
                        $stmt = $conn->prepare($sql_verificar);
                        $stmt->bind_param("ii", $id_usuario, $cd['id_cd']);
                        $stmt->execute();
                        $stmt->store_result();

                        // Define a imagem correta conforme o status de favorito
                        if ($stmt->num_rows > 0) {
                            $img_favorito = '../../../../img/todos_produtos/icone_favoritos_selecionado.png'; 
                        } else {
                            $img_favorito = '../../../../img/todos_produtos/icone_favoritos.png'; 
                        }
                        $stmt->close();
                        ?>

                        <!-- Botão de favoritar -->
                        <button type="button" class="btn-favorito" id="favorito-button">
                            <img src="<?= $img_favorito ?>" alt="Favoritar" class="img_favorito" id="favorito-img">
                        </button>
                    </form>

                    <!-- Código JS para enviar a requisição de favoritar/desfavoritar via AJAX -->
                    <script>
                        document.querySelectorAll('.btn-favorito').forEach((favoritoButton, index) => {
                            const form = favoritoButton.closest('form');
                            const favoritoImg = favoritoButton.querySelector('.img_favorito');

                            favoritoButton.addEventListener("click", function () {
                                const formData = new FormData(form);

                                fetch('../../../produtos/favoritar.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    // Atualiza a imagem conforme o retorno da requisição
                                    favoritoImg.src = data.favoritado
                                        ? '../../../../img/todos_produtos/icone_favoritos_selecionado.png'
                                        : '../../../../img/todos_produtos/icone_favoritos.png';

                                    // Verifica se todos ainda estão favoritados
                                    let todosFavoritados = true;
                                    document.querySelectorAll('.img_favorito').forEach(img => {
                                        if (!img.src.includes('icone_favoritos_selecionado.png')) {
                                            todosFavoritados = false;
                                        }
                                    });

                                    // Se algum deixou de ser favoritado, recarrega a página
                                    if (!todosFavoritados) {
                                        location.reload();
                                    }
                                })
                                .catch(error => {
                                    console.error('Erro ao favoritar:', error);
                                });
                            });
                        });
                    </script>

                </div> <!-- fim baixo_part -->
            </div> <!-- fim bloco principal -->
        </div> <!-- fim produto -->

        <!-- Botão Ver Mais -->
        <a href="../../../produtos/produto.php?id_cd=<?php echo $cd['id_cd']; ?>" class="link_produto2">
            <div class="butao">Ver Mais</div>
        </a>

    <?php } ?>
    </div> <!-- fim fileira_produtos -->
<?php endif; ?>

</main>
     <!-- Rodapé -->
<footer>

    <!-- Seção com a logo no rodapé, contendo duas linhas laterais -->
    <section id="logo_rodape">
        <div class="linha_logo_rodape"></div> <!-- Linha à esquerda -->
        <!-- Logo central -->
        <img src="../../../../img/cabeçario/logo.png" alt="Logo" id="img_rodape_logo">
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
                    <li class="lista_rodape"><a href="../../../../html/rodape/politica_comercial.html#trocas_devolucoes" class="link_rodape">Trocas e Devoluções</a></li>
                    <li class="lista_rodape"><a href="../../../../html/rodape/politica_comercial.html#termos_uso" class="link_rodape">Termos de uso</a></li>
                    <li class="lista_rodape"><a href="../../../../html/rodape/politica_comercial.html#politica_privacidade" class="link_rodape">Políticas de privacidade</a></li>
                    <li class="lista_rodape"><a href="../../../../html/rodape/politica_comercial.html#direito_arrependimento" class="link_rodape">Direito de arrependimento</a></li>
                </ul>
            </div>

            <!-- Seção: Suporte -->
            <div class="topicos_rodape">
                <h1 class="titulo">Suporte</h1>
                <ul>
                    <li class="lista_rodape"><a href="../../../rodape/perguntas_frequentes.html" class="link_rodape">Perguntas Frequentes</a></li>
                    <li class="lista_rodape"><a href="../../../rodape/avaliar.html" class="link_rodape">Avaliar</a></li>
                    <li class="lista_rodape"><a href="#" class="link_rodape">Recomendar Produtos</a></li>
                </ul>
            </div>

            <!-- Seção: Atendimento -->
            <div class="topicos_rodape">
                <h1 class="titulo">Atendimento</h1>
                <ul>
                    <li class="lista_rodape">
                        <a href="mailto:codedisc@gmail.com" class="link_rodape">
                            <img src="../../../../img/rodape/contato/icone_email.png" alt="Ícone email" class="img_rodape_contatos"> Email
                        </a>
                    </li>
                    <li class="lista_rodape">
                        <a href="tel:+5585900000000" class="link_rodape">
                            <img src="../../../../img/rodape/contato/icone_telefone.png" alt="Ícone telefone" class="img_rodape_contatos"> Telefone
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
            <img src="../../../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
        </div>

        <!-- Seção de redes sociais -->
        <div id="redes_sociais_rodape">
            <h1 class="titulo">Redes Sociais</h1>
            <!-- Ícones representando redes sociais -->
            <a href="https://www.instagram.com/code_disc_oficial"><img src="../../../../img/rodape/redes_sociais/icone_instagram.png" alt="Instagram" class="img_rodape_social"></a>
            <a href="https://www.facebook.com/code_disc_oficial"><img src="../../../../img/rodape/redes_sociais/icone_facebook.png" alt="Facebook" class="img_rodape_social"></a>
            <a href="https://twitter.com/code_disc_oficial"><img src="../../../../img/rodape/redes_sociais/icone_x.png" alt="X (Twitter)" class="img_rodape_social"></a>
            <a href="https://www.tiktok.com/code_disc_oficial"><img src="../../../../img/rodape/redes_sociais/icone_tiktok.png" alt="TikTok" class="img_rodape_social"></a>
            <a href="https://open.spotify.com/user/code_disc_oficial"><img src="../../../../img/rodape/redes_sociais/icone_spotify.png" alt="Spotify" class="img_rodape_social"></a>
            <a href="https://www.youtube.com/code_disc_oficial"><img src="../../../../img/rodape/redes_sociais/icone_youtube.png" alt="YouTube" class="img_rodape_social"></a>
        </div>
    </section>
</footer>

</body>
</html>