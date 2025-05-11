<?php
session_start();
include "../login_cadastro/conexao.php";

// Verifica se está logado
if (!isset($_SESSION['id_usuario'])) {
    echo "Você precisa estar logado para avaliar.";
    exit;
}

// Verifica se é avaliação de CD ou do sistema
$id_cd = $_GET['id_cd'] ?? null;
$avaliando_cd = $id_cd !== null;

// Título dinâmico
$titulo = $avaliando_cd ? "Avaliar este CD" : "Avaliar o sistema";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Site</title>
    <link rel="shortcut icon" href="../../img/favicon/favicon.ico" type="image/x-icon">

    <!-- Importação dos arquivos CSS -->
    <link rel="stylesheet" href="../../css/avaliar_site/avaliar_site.css">
    <link rel="stylesheet" href="../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <!-- Importação do script JavaScript responsável pela avaliação com estrelas -->
    <script src="../../js/avaliar/estrelas.js" defer></script>

</head>
<body>

    <!-- Cabeçalho da página (com usuário logado) -->
    <header> 
        <div id="parte_de_cima_cab">

            <!-- Logo da página -->
            <a href="#" id="logo"><img src="../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Barra de pesquisa -->

            <!-- Ícones de perfil e carrinho de compras -->
            <div id="login_carrinho">
                <a href="#"><img src="../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a>
                <a href="#"><img src="../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a>
            </div>
        </div>
    </header>
    <button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button>
    <main>
        <section id="conteudo">
            <!-- Título principal da página -->
            <h1 id="titulo"><?= $titulo ?></h1>
            <form action="salvar_avaliacao.php" method="POST">


            <!-- Seção para avaliação com estrelas -->
            <div id="avaliar_estrelas">
                <p id="avaliar_estrelas_titulo">Sua Avaliação</p>
                <div id="linha_de_estrelas">
                <select name="nota" required>
                        <option value="">Escolha</option>
                        <option value="1">1 ★</option>
                        <option value="2">2 ★</option>
                        <option value="3">3 ★</option>
                        <option value="4">4 ★</option>
                        <option value="5">5 ★</option>
              </select>
            </div>

            <!-- Campo para o usuário escrever um comentário sobre o site -->
            <div id="text_avalicao">
                <p id="text_avalicao_titulo">Sua avaliação sobre o site</p>
                <textarea rows="4" cols="50" placeholder="Digite seu texto aqui..." id="input_text" name="comentario"  ></textarea>
            </div>

            <!-- Botão de envio da avaliação -->
            <button id="enviar" type="submit">Enviar</button>
            </form>
 
        </section>
    </main>

    <!-- Seção de imagens decorativas à direita -->
    <div id="imgs_direita">
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita sumir"></div>
    </div>

    <!-- Seção de imagens decorativas à esquerda -->
    <div id="imgs_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda sumir">
    </div>
    
</body>
</html>
