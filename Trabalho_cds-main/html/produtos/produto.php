<?php
session_start();
include "../login_cadastro/conexao.php";

// Verifica se o parâmetro 'id_cd' foi passado na URL
if (isset($_GET['id_cd'])) {
    $id_cd = $_GET['id_cd'];


    $id_usuario = $_SESSION['id_usuario'];
    $res = $conn->query("SELECT * FROM Usuario WHERE id_usuario = $id_usuario");
    $usuarioLogado = $res->fetch_assoc();

    // Consulta para contar o total de avaliações
  $sql_count = "SELECT COUNT(*) AS total_avaliacoes FROM avaliacao WHERE id_cd = ?";
  $stmt_count = $conn->prepare($sql_count);
  $stmt_count->bind_param("i", $id_cd);
  $stmt_count->execute();
  $result_count = $stmt_count->get_result();
  $row_count = $result_count->fetch_assoc();
  $total_avaliacoes = $row_count['total_avaliacoes'];

   // Consulta para calcular a média das avaliações
   $sql_media = "SELECT AVG(nota) AS media_avaliacoes FROM avaliacao WHERE id_cd = ?";
   $stmt_media = $conn->prepare($sql_media);
   $stmt_media->bind_param("i", $id_cd);
   $stmt_media->execute();
   $result_media = $stmt_media->get_result();
   $row_media = $result_media->fetch_assoc();
   $media_avaliacoes = round($row_media['media_avaliacoes'], 1); // arredondando para 1 casa decimal

    // Consulta para obter os detalhes do CD
$sql_cd = "SELECT id_cd, titulo, capa, preco, anoLancamento, genero, descricao, disponibilidade FROM CD WHERE id_cd = ?";
    $stmt_cd = $conn->prepare($sql_cd);
    $stmt_cd->bind_param('i', $id_cd);
    $stmt_cd->execute();
    $result_cd = $stmt_cd->get_result();

    if ($result_cd->num_rows > 0) {
        $cd = $result_cd->fetch_assoc();
    } else {
        echo "<p>CD não encontrado.</p>";
        exit();
    }

    // Consulta para obter o desconto da promoção
    $sql_desconto = "SELECT desconto FROM Promocao WHERE id_cd = ?";
    $stmt_desconto = $conn->prepare($sql_desconto);
    $stmt_desconto->bind_param('i', $id_cd);
    $stmt_desconto->execute();
    $result_desconto = $stmt_desconto->get_result();

    $desconto = 0;
    if ($result_desconto->num_rows > 0) {
        $promo = $result_desconto->fetch_assoc();
        $desconto = $promo['desconto'];
    }

    // Consulta para obter as músicas associadas ao CD
    $sql_musicas = "SELECT m.id_musica, m.nomeMusica, m.tempo, m.audio FROM Musica m
                    JOIN CD_Musica cm ON m.id_musica = cm.id_musica
                    WHERE cm.id_cd = ?";
    $stmt_musicas = $conn->prepare($sql_musicas);
    $stmt_musicas->bind_param('i', $id_cd);
    $stmt_musicas->execute();
    $result_musicas = $stmt_musicas->get_result();

    $musicas = [];
    while ($musica = $result_musicas->fetch_assoc()) {
        $musicas[] = $musica;
    }

    

    // Consulta para obter os artistas associados ao CD
    $sql_artistas = "SELECT a.id_artista, a.nomeArtista FROM Artista a
                     JOIN CD_Artista ca ON a.id_artista = ca.id_artista
                     WHERE ca.id_cd = ?";
    $stmt_artistas = $conn->prepare($sql_artistas);
    $stmt_artistas->bind_param('i', $id_cd);
    $stmt_artistas->execute();
    $result_artistas = $stmt_artistas->get_result();

    $artistas = [];
    while ($artista = $result_artistas->fetch_assoc()) {
        $artistas[] = $artista;
    }
} else {
    echo "<p>ID do CD não especificado.</p>";
    exit();
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar_carrinho'])) {
    $cd_id = $_POST['cd_id'];
    $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;

    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    if (isset($_SESSION['carrinho'][$cd_id])) {
        $_SESSION['carrinho'][$cd_id] += $quantidade;
    } else {
        $_SESSION['carrinho'][$cd_id] = $quantidade;
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
    <title>Detalhe do CD</title>
    <link rel="shortcut icon" href="../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../css/produto/produto.css">
    <link rel="stylesheet" href="../../css/produto/produto_part2.css">
    <link rel="stylesheet" href="../../css/produto/produto_part3.css">
    <link rel="stylesheet" href="../../css/produto/produto_responsividade.css">
    <link rel="stylesheet" href="../../css/cabeçalhos/cabeçalho_com_login.css">
    <link rel="stylesheet" href="../../css/rodape/rodape.css">

    <script src="../../js/quantidade/quantidade.js" defer></script>
    <script src="../../js/todos_produtos/favoritos.js" defer></script>
    <script src="../../js/produtos/butao.js" defer></script>
    <script src="../../js/avaliar/estrelas.js" defer></script>
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

                <!-- Campo de pesquisa -->
                <input type="text" id="input_barra_pesquisa" placeholder="Buscar..." >

                <!-- Botão de pesquisa -->
                <label for="check" id="buttom_lupa">
                    <img src="../../img/cabeçario/icone_lupa.png" alt="Lupa" id="lupa">
                </label>
            </div>
        </div>

        <div id="login_carrinho"> <!-- Login e Carrinho -->
            <a href="#"><img src="../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Foto de perfil -->

            <a href="#"><img src="../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
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
            <a href="#" id="home" class="link_caminho">
                <img src="../../img/todos_produtos/icone_home.png" alt="Home" id="img_home">
                <p>Home</p>
            </a>
            <div class="proxima_part">
                <img src="../../img/todos_produtos/icone_seta_direita.png" alt="Seta" class="seta">
                <a href="#" class="link_caminho">Produtos</a>
            </div>
            <div class="proxima_part">
                <img src="../../img/todos_produtos/icone_seta_direita.png" alt="Seta" class="seta">
                <a href="#" class="link_caminho">Cd</a>
            </div>
        </div>
    </section>

    <section id="section_pri">
    
        <section id="section_cima">
            <img src="../../img/<?php echo $cd['capa']; ?>" alt="Imagem do Cd" id="img">
            <div id="corpo">
                <div id="primeira">
                    <h2><?php foreach ($artistas as $artista): ?>
                    <li><a id="link_artista"href="detalhes_artista.php?id_artista=<?php echo $artista['id_artista']; ?>"><?php echo $artista['nomeArtista']; ?></a></li>
                <?php endforeach; ?></h2>
                            

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


                </div>
                <div>
                    <h1 id="titulo"><?php echo $cd['titulo']; ?></h1>
                    <p id="estrelas"> <?php 
                      // Exibindo as estrelas correspondentes à média
                        $estrelas_media = round($media_avaliacoes);
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $estrelas_media) {
                                echo "<img src='../../img/avaliar/estrela_amarela.png' alt='estrela cheia' class='estrela'>";
                            } else {
                                echo "<img src='../../img/avaliar/estrela_escura.png' alt='estrela vazia' class='estrela'>";
                            }
                        }
                        echo "</p>";
                ?>  avaliações média : <?php echo $media_avaliacoes   ?> </p>
                </div>
            
                <div>
                      <?php if ($desconto > 0): ?>
            <?php $preco_final = $cd['preco'] - ($cd['preco'] * ($desconto / 100)); ?>
            <p  id="preco"> R$ <?php echo number_format($cd['preco'], 2, ',', '.'); ?></p>
            <p  id="promo"> R$ <?php echo number_format($preco_final, 2, ',', '.'); ?> (<?php echo $desconto; ?>% OFF)</p>
        <?php else: ?>
            <p id="preco">R$ <?php echo number_format($cd['preco'], 2, ',', '.'); ?></p>
        <?php endif; ?>

       <form method="POST" action="">
            <input type="hidden" name="cd_id" value="<?= $cd['id_cd'] ?>">
            <input type="hidden" name="disponibilidade" value="<?= $cd['disponibilidade'] ?>">

            <div class="input-wrapper">
                <button type="button" onclick="decrementar(this)">-</button>
                <input type="number" name="quantidade" class="quantidade-input" value="1" min="1" max="<?= $cd['disponibilidade'] ?>" required>
                <button type="button" onclick="incrementar(this)">+</button>
            </div>

                <button type="submit" name="adicionar_carrinho" class="btn" id="but_car">
                    <img src="../../img/cabeçario/icone_carrinho.png" alt="carrinho">Adicionar ao Carrinho
                </button>
        </form>

<script>
function incrementar(btn) {
    const input = btn.parentElement.querySelector(".quantidade-input");
    const max = parseInt(input.getAttribute("max"));
    let atual = parseInt(input.value);
    if (atual < max) input.value = atual + 1;
}

function decrementar(btn) {
    const input = btn.parentElement.querySelector(".quantidade-input");
    const min = parseInt(input.getAttribute("min"));
    let atual = parseInt(input.value);
    if (atual > min) input.value = atual - 1;
}
</script>


                    </div>
                    <button id="comprar">Comprar</button>
                </div>
            </div>
        </section>

        <hr class="linha" id="linha_1">

        <section id="butoes">
          <div>
            <button id="botao1" class="but_baixo ativo" >Descrição</button>
            <button id="botao2" class="but_baixo">Músicas</button>
            <button id="botao3" class="but_baixo">Avaliar</button>
          </div>
        
          <div id="areaConteudo">
            <div id="conteudo1" class="dentro ativo">
              <div class="separar_tip_cont">
                <p class="tipo">Título</p>
                <p class="cont"> <?php echo $cd['titulo']; ?></p>
              </div>
              <hr class="linha_separcao">
              <div class="separar_tip_cont">
                <p class="tipo">Disponibilidade</p>
                <p class="cont"><?= $cd['disponibilidade'] ?></p>
              </div>
              <hr class="linha_separcao">
              <div class="separar_tip_cont">
                <p class="tipo">Artista</p>
                <?php foreach ($artistas as $artista): ?>
                <p class="cont"><a id="link_artista"href="detalhes_artista.php?id_artista=<?php echo $artista['id_artista']; ?>"><?php echo $artista['nomeArtista']; ?></a></p>
                <?php endforeach; ?>
              </div>
              <hr class="linha_separcao">
              <div class="separar_tip_cont">
                <p class="tipo">Ano de Lançamento</p>
                <p class="cont"><?php echo $cd['anoLancamento']; ?></p>
              </div>
              <hr class="linha_separcao">
              <div class="separar_tip_cont">
                <p class="tipo">Gênero</p>
                <p class="cont"><?php echo $cd['genero']; ?></p>
              </div>
              <hr class="linha_separcao">
              <h1 id="decri">Descrição</h1>
              <p id="p_des"><?php echo $cd['descricao']; ?></p>
            </div>
            <div id="conteudo2" class="dentro">
              <div class="separar_tip_cont_m">
                <p class="tipo">Nome</p>
                <p class="tipo">Duração</p>
                <p class="tipo">Áudio</p>
              </div>

              <?php foreach ($musicas as $musica): ?>
              <hr class="linha_separcao m">
              <div class="separar_tip_cont_m">
                <p class="cont">                            <?php echo $musica['nomeMusica']; ?>
                </p>
                <p class="cont"><?php echo $musica['tempo']; ?> minutos</p>
                <p class="cont"><?php
    $audio_path = "../../audio/" . $musica['id_musica'] . ".mp3";

    if (file_exists($audio_path)) {
        $audio_id = "audio_" . $musica['id_musica'];
        echo "
        <audio controls id='$audio_id' class='audio'>
            <source src='$audio_path#t=0,50' type='audio/mpeg'>
            Seu navegador não suporta o elemento de áudio.
        </audio>

        <div id='alerta_previa' style='
            display: none;
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 8px;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        '>Prévia limitada a 20 segundos.</div>

        <script>
            const audioElem = document.getElementById('$audio_id');
            const alerta = document.getElementById('alerta_previa');

            audioElem.addEventListener('play', () => {
                setTimeout(() => {
                    if (!audioElem.paused) {
                        audioElem.pause();
                        audioElem.currentTime = 0;
                        alerta.style.display = 'block';
                        setTimeout(() => {
                            alerta.style.display = 'none';
                        }, 3000); // Esconde após 3 segundos
                    }
                }, 21000); // 21 segundos
            });
        </script>
        ";
    }
?>

</p>
              </div>
              <?php endforeach; ?>
    
            </div>
            <div id="conteudo3" class="dentro">
              <section id="conteudo">
                <!-- Título principal da página -->
                <h1 id="titulo_avaliar">Avaliar <?php echo $id_cd ? "este CD" : "o sistema"; ?></h1>

                <form action="salvar_avaliacao.php" method="POST">
    <input type="hidden" name="id_cd" value="<?= htmlspecialchars($id_cd) ?>">
    
    <label>Nota:
        <select name="nota" required>
            <option value="">Escolha</option>
            <option value="1">1 ★</option>
            <option value="2">2 ★</option>
            <option value="3">3 ★</option>
            <option value="4">4 ★</option>
            <option value="5">5 ★</option>
        </select>
    </label><br><br>
    <div id="text_avalicao"></div>
    <p id="text_avalicao_titulo">Comentário:<br>
        <textarea name="comentario" rows="3" cols="40" id="input_text" ></textarea>
    </p>
    
    <button type="submit">Enviar Avaliação</button>
    </div><br><br>

</form>

            </section>
            </div>
          </div>
        </section>

        <hr class="linha">
       
        <section id="avaliacoes">
  <h1 id="titulo_aval">Avaliações</h1>

  <?php
  include "../login_cadastro/conexao.php";
  $id_cd = $_GET['id_cd'] ?? null;

  // Consulta para contar o total de avaliações
  $sql_count = "SELECT COUNT(*) AS total_avaliacoes FROM avaliacao WHERE id_cd = ?";
  $stmt_count = $conn->prepare($sql_count);
  $stmt_count->bind_param("i", $id_cd);
  $stmt_count->execute();
  $result_count = $stmt_count->get_result();
  $row_count = $result_count->fetch_assoc();
  $total_avaliacoes = $row_count['total_avaliacoes'];

  // Consulta para calcular a média das avaliações
  $sql_media = "SELECT AVG(nota) AS media_avaliacoes FROM avaliacao WHERE id_cd = ?";
  $stmt_media = $conn->prepare($sql_media);
  $stmt_media->bind_param("i", $id_cd);
  $stmt_media->execute();
  $result_media = $stmt_media->get_result();
  $row_media = $result_media->fetch_assoc();
  $media_avaliacoes = round($row_media['media_avaliacoes'], 1); // arredondando para 1 casa decimal

  // Exibe o total de avaliações e a média
  echo "<p><strong>Total de Avaliações:</strong> " . $total_avaliacoes . " avaliações</p>";
  echo "<p><strong>Média das Avaliações:</strong> " . $media_avaliacoes . " ★</p>";

  // Exibindo as estrelas correspondentes à média
  $estrelas_media = round($media_avaliacoes);
  echo "<p><strong>Estrelas médias:</strong> ";
  for ($i = 1; $i <= 5; $i++) {
      if ($i <= $estrelas_media) {
          echo "<img src='../../img/avaliar/estrela_amarela.png' alt='estrela cheia' class='estrela'>";
      } else {
          echo "<img src='../../img/avaliar/estrela_escura.png' alt='estrela vazia' class='estrela'>";
      }
  }
  echo "</p>";

  // Consulta para pegar as 5 avaliações mais recentes
  $sql = "
      SELECT a.nota, a.comentario, a.data_avaliacao, u.nome_completo, u.foto_perfil
      FROM avaliacao a
      JOIN Usuario u ON a.id_usuario = u.id_usuario
      WHERE a.id_cd = ?
      ORDER BY a.data_avaliacao DESC
      LIMIT 5
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id_cd);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
      echo "<p style='margin-left: 15px;'>Nenhuma avaliação encontrada para este CD.</p>";
  } else {
      while ($row = $result->fetch_assoc()) {
          // Caminho da imagem de perfil
          $pasta_fotos = '../../img/php_cliente/uploads/';
            $caminho_foto = $pasta_fotos . $row['foto_perfil'];

        $foto = (!empty($row['foto_perfil']) && file_exists($caminho_foto))
    ? htmlspecialchars($caminho_foto)
    : $pasta_fotos . 'default.png'; // ou alguma imagem padrão


          // Estrelas para a avaliação individual
          $estrelas = intval($row['nota']);
          ?>
          <div class="aval">
            <div class="direita_aval">
              <!-- Foto de perfil do usuário -->
              <div>
              <?php if (!empty($usuarioLogado['foto_perfil'])): ?>
            <img src=" ../../img/<?php echo htmlspecialchars($usuarioLogado['foto_perfil']); ?>"  id="Perfil" alt="Perfil">
        <?php else: ?>
            <img src="../../img/uploads/perfil_padrao.jpg" alt="Perfil padrão"  id="Perfil" >
        <?php endif; ?><!-- Foto de perfil -->

                <h1 class="tipo"><?= htmlspecialchars($row['nome_completo']) ?></h1>
                <p class="cont"><?= date("d/m/Y", strtotime($row['data_avaliacao'])) ?></p>
              </div>
              <div class="estrelas">
                <?php 
                // Exibe as estrelas baseadas na nota
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $estrelas) {
                        echo "<img src='../../img/avaliar/estrela_amarela.png' alt='estrela cheia' class='estrela'>";
                    } else {
                        echo "<img src='../../img/avaliar/estrela_escura.png' alt='estrela vazia' class='estrela'>";
                    }
                }
                ?>
              </div>
            </div>
            <div>
              <p class="tipo">Avaliação do CD</p>
              <p id="p_aval"><?= nl2br(htmlspecialchars($row['comentario'])) ?></p>
            </div>
          </div>
          <?php
      }
  }
  ?>
</section>


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
                      <li class="lista_rodape"><a href="../login_cadastro/perfil/butoes/sugestao.html" class="link_rodape">Recomendar Produtos</a></li>
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
</body>
</html>