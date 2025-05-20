<?php
session_start();
include "../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    echo "Você precisa estar logado para ver o histórico de compras.";
    exit;
}

date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário para o Brasil
$dataAtual = date('d/m/Y'); // Formato brasileiro: dia/mês/ano
$id_usuario = $_SESSION['id_usuario'];
$res = $conn->query("SELECT * FROM Usuario WHERE id_usuario = $id_usuario");
$usuarioLogado = $res->fetch_assoc();



$sql = "SELECT c.id_compra, c.id_cd, c.quantidade, c.forma_pagamento, c.tipo_pagamento, c.tipo_envio, 
               c.enderecoEntrega, c.valorTotal, c.cep_entrega, c.estimativa_entrega, c.taxa_entrega, 
               cd.titulo AS cd_titulo, cd.capa
        FROM Compra c
        JOIN CD cd ON c.id_cd = cd.id_cd
        WHERE c.id_usuario = ? AND c.data_compra >= DATE_SUB(NOW(), INTERVAL 13 DAY)
        ORDER BY c.data_compra DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historico de compras</title>
    <link rel="shortcut icon" href="../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../css/historico/historico.css">
    <link rel="stylesheet" href="../../../../css/cabeçalhos/cabeçalho_com_login.css"> <!-- Estilos do cabeçalho -->
    <link rel="stylesheet" href="../../../../css/rodape/rodape.css">

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
    <section id="section">
    <h1 id="titulo">Historico de compras</h1>
    <button class="button" id="voltar"><a href="#" class="link">Voltar</a></button>
    <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div id='produtos'>";
        echo "<div class='produto'>";

        echo "<div class='duas primeiro'>";
        echo "<h1 class='nome'>{$row['cd_titulo']}</h1>";
        echo "<img src='../../../../img/{$row['capa']}' alt='Capa' class='img'>";
        echo "</div>";

        echo "<div class='duas part1'>";
        echo "<p class='sub_titulo'>Quantidade: <span class='info_sub'>{$row['quantidade']}</span></p>";
        echo "<p class='sub_titulo'>Forma de pagamento: <span class='info_sub'>{$row['forma_pagamento']}</span></p>";
        echo "<p class='sub_titulo'>Tipo de envio: <span class='info_sub'>{$row['tipo_envio']}</span></p>";
        echo "</div>";

        echo "<div class='duas'>";
        echo "<p class='sub_titulo'>Endereço: <span class='info_sub'>{$row['enderecoEntrega']}</span></p>";
        echo "<p class='sub_titulo'>Total: <span class='info_sub'>R$ " . number_format($row['valorTotal'], 2, ',', '.') . "</span></p>";
        echo "<p class='sub_titulo'>Taxa de entrega: <span class='info_sub'>R$ " . number_format($row['taxa_entrega'], 2, ',', '.') . "</span></p>";
        echo "<p class='sub_titulo'>Previsão de entrega: <span class='info_sub'>" . date('d/m/Y', strtotime($row['estimativa_entrega'])) . "</span></p>";
        echo "</div>";

        echo "</div>"; // Fecha produto
        echo "</div>"; // Fecha produtos
    }
} else {
    echo "<p style='text-align:center;'>Você ainda não realizou nenhuma compra.</p>";
}

$stmt->close();
?>

    </section>

    <div id="div_but_prod" ><button class="button" id="but_prod"><a href="#" class="link" id="link_prod">Tela de Produtos</a></button></div>

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
                    <li class="lista_rodape"><a href="sugestao.html" class="link_rodape">Recomendar Produtos</a></li>
                </ul>
            </div>

            <!-- Seção: Atendimento -->
            <div class="topicos_rodape">
                <h1 class="titulo">Atendimento</h1>
                <ul>
                    <li class="lista_rodape">
                        <a href="mailto:codedisc@gmail.com" class="link_rodape">
                            <img src="../../../../../../img/rodape/contato/icone_email.png" alt="Ícone email" class="img_rodape_contatos"> Email
                        </a>
                    </li>
                    <li class="lista_rodape">
                        <a href="tel:+5585900000000" class="link_rodape">
                            <img src="../../../../../../img/rodape/contato/icone_telefone.png" alt="Ícone telefone" class="img_rodape_contatos"> Telefone
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
            <img src="../../../../../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../../../img/rodape/cartoes/icone_cartao1.png" alt="Cartão" class="img_cartao_rodape">
            <img src="../../../../../../img/rodape/cartoes/icone_cartao2.png" alt="Cartão" class="img_cartao_rodape">
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