<?php
session_start();
include "../../../login_cadastro/conexao.php";

// Verificação de login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login_cadastro/login.php");
    exit();
}

// Inicializa o carrinho
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Consulta de avaliações (declarado fora do POST)
$sqlAv = "
    SELECT a.nota, a.comentario, a.data_avaliacao, u.login, u.foto_perfil, u.id_usuario
    FROM avaliacao a
    INNER JOIN Usuario u ON a.id_usuario = u.id_usuario
    WHERE a.id_cd IS NULL
      AND a.data_avaliacao = (
          SELECT MAX(a2.data_avaliacao)
          FROM avaliacao a2
          WHERE a2.id_usuario = a.id_usuario AND a2.id_cd IS NULL
      )
    ORDER BY a.data_avaliacao DESC
";

// Atualiza quantidade ou remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cd'])) {
    $id_cd = (int)$_POST['id_cd'];

    if (isset($_POST['remover'])) {
        unset($_SESSION['carrinho'][$id_cd]);
    } elseif (isset($_POST['atualizar'])) {
        $nova_quantidade = max(1, (int)$_POST['quantidade']);

        // Verifica disponibilidade no estoque
        $sql = "SELECT disponibilidade FROM CD WHERE id_cd = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cd);
        $stmt->execute();
        $stmt->bind_result($disponibilidade);
        $stmt->fetch();
        $stmt->close();

        if ($nova_quantidade > $disponibilidade) {
            $nova_quantidade = $disponibilidade;
        }

        $_SESSION['carrinho'][$id_cd] = $nova_quantidade;
    }
}

// Buscar dados do usuário logado
$id_usuario = $_SESSION['id_usuario'];
$res = $conn->query("SELECT * FROM Usuario WHERE id_usuario = $id_usuario");
$usuarioLogado = $res->fetch_assoc();

// Executar consulta de avaliações
$stmtAv = $conn->prepare($sqlAv);
$stmtAv->execute();
$resultAv = $stmtAv->get_result();

// Consultas iniciais de Gênero, Artista e Música
$queryGenero = "SELECT DISTINCT genero FROM CD LIMIT 10";
$queryArtista = "SELECT nomeArtista FROM Artista LIMIT 10";
$queryMusica = "SELECT nomeMusica FROM Musica LIMIT 10";

$stmtGenero = $conn->query($queryGenero);
$stmtArtista = $conn->query($queryArtista);
$stmtMusica = $conn->query($queryMusica);

$generos = $stmtGenero->fetch_all(MYSQLI_ASSOC);
$artistas = $stmtArtista->fetch_all(MYSQLI_ASSOC);
$musicas = $stmtMusica->fetch_all(MYSQLI_ASSOC);

// Buscar CDs do carrinho
$itens = [];
$total = 0;

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(",", array_map('intval', array_keys($_SESSION['carrinho'])));
    $sql = "
        SELECT CD.id_cd, CD.titulo, CD.capa, CD.preco, CD.disponibilidade, 
               IFNULL(Promocao.desconto, 0) AS desconto
        FROM CD
        LEFT JOIN Promocao ON CD.id_cd = Promocao.id_cd
        WHERE CD.id_cd IN ($ids)
    ";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $row['quantidade'] = $_SESSION['carrinho'][$row['id_cd']];
        $subtotal = $row['preco'] * $row['quantidade'];
        if ($row['desconto'] > 0) {
            $subtotal -= ($subtotal * ($row['desconto'] / 100));
        }
        $row['subtotal'] = $subtotal;
        $total += $subtotal;
        $itens[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho</title>
    <link rel="shortcut icon" href="../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../css/carrinho/carrinho.css">
    <link rel="stylesheet" href="../../../../css/cabeçalhos/cabeçalho_com_login.css"> <!-- Estilos do cabeçalho -->

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

            <div id="login_carrinho"> 
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
                  <a href="carrinho.php"><img src="../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>

        <hr style="color: #7b7a7a;"><!-- Linha separadora -->

        <!-- Menu de navegação -->
        <nav class="menu-underline">
            <input type="checkbox" id="menu_toggle" class="menu_toggle"><!-- Menu responsivo -->
            <label for="menu_toggle" class="menu_icon">&#9776;</label> 

            <ul id="menu">

                <li class="p_menu"><a href="../../../pagina_inicial/index_logado.php" class="a_menu">Inicio</a></li>
                <li class="p_menu"><a href="../../../produtos/todos_os_produtos.php" class="a_menu">Produtos</a></li>
                
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
                           <li><a href="../../../produtos/todos_os_produtos.php?busca_geral=<?= htmlspecialchars($artista['nomeArtista']) ?>"  class="sub_a"><?= htmlspecialchars($artista['nomeArtista']) ?></a></li>
                        <?php endforeach; ?>
                        </ul>
                        
                      
                    </ul>
                </li>
            </ul>
        </nav>
    </header>
    <section id="section">
        <h1 id="titulo">Seu Carrinho</h1>
        <?php if (empty($itens)): ?>
                <div class="produto"><p class="sub_titulo">Seu carrinho está vazio.</p></div>
            <?php else: ?>
                <?php foreach ($itens as $item): ?>
                    
            <div id="produtos">
                <div class="produto">
                    <div class="esquerda">
                    <img src="../../../../img/<?= $item['capa'] ?>" alt="Imagem do cd" class="img">
                        <div class="lado">
                            <h1 class="nome"><?= htmlspecialchars($item['titulo']) ?></h1>
                                        <p class="sub_titulo">Preço: <span class="info_sub">R$<?= number_format($item['preco'], 2, ',', '.') ?></span></p>
                                        <p class="sub_titulo">Desconto: <span class="info_sub" ><?= $item['desconto'] ?>%</span></p> 
                                <form method="post" >
                                <input type="hidden" name="id_cd" value="<?= $item['id_cd'] ?>">
                                        <p class="sub_titulo">Quantidade:
                                                    <div class="input-wrapper">
                                                        <button type="button" onclick="decrementar(this)">-</button>
                                                        <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" />
                                                        <button type="button" onclick="incrementar(this)">+</button>
                                                    </div>
                                                </p>
                                        <p class="sub_titulo">Subtotal: <span class="info_sub">R$<?= number_format($item['subtotal'], 2, ',', '.') ?></span></p>
                                    </div>
                                </div>
                                <div class="buttons">
                                    <button class="butao" name="atualizar">Atualizar</button>
                                    <button class="butao"  name="remover">Deletar</button>
                                </div>
                            </form>

                </div>
                <?php endforeach; ?>
            <?php endif; ?>
          
        </div>
        <div id="part_baixo">
          <?php if (!empty($itens)): ?>
    <button onclick="window.location.href='../../../produtos/compra/comprar.php'" class="butao">Comprar</button>
    <p class="sub_titulo">Total do Carrinho: <span class="info_sub">R$<?= number_format($total, 2, ',', '.') ?></span></p>
<?php endif; ?>


        </div>
    </section>

    <!-- Seção de imagens à direita -->
    <div id="imgs_direita">
        <!-- Imagens decorativas à direita -->
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
    </div>

    <!-- Seção de imagens à esquerda -->
    <div id="imgs_esquerda">
        <!-- Imagens decorativas à esquerda -->
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
    </div>

    <script>
        function incrementar(botao) {
            const input = botao.parentElement.querySelector('input[type="number"]');
            input.value = parseInt(input.value) + 1;
        }

        function decrementar(botao) {
            const input = botao.parentElement.querySelector('input[type="number"]');
            let valor = parseInt(input.value);
            if (valor > 1) input.value = valor - 1;
        }
    </script>
      
</body>
</html>