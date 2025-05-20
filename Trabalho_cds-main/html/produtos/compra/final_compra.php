<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Finalizada</title>
    <link rel="shortcut icon" href="../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../css/compra/final_compra.css">
    <link rel="stylesheet" href="../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">
</head>
<body>
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

    <main>
        <section id="compra">
        
          <?php
session_start();

// Verificar se as informações da compra estão na sessão
if (!isset($_SESSION['compra'])) {
    echo "<h2> Erro: Informações da compra não encontradas.</h2>";
    exit;
}

$compra = $_SESSION['compra'];

// Exibindo os detalhes da compra
echo "<div>
        <h2 id='titulo'>Confirmação de Compra</h2>
        <p class='info'><strong>Valor total da compra:</strong> R$ " . number_format($compra['valor_total'], 2, ',', '.') . "</p>
        <p class='info'><strong>CEP de entrega:</strong> " . $compra['cep'] . "</p>
        <p class='info'><strong>Endereço de entrega:</strong> " . $compra['endereco_entrega'] . "</p>
        <p> class='info'<strong>Taxa de entrega:</strong> R$ " . number_format($compra['taxa_entrega'], 2, ',', '.') . "</p>
        <p class='info'><strong>Tempo estimado de entrega:</strong> " . $compra['estimativa_entrega'] . "</p>";

// Verificar se há código PIX e exibi-lo
if (isset($compra['codigo_pix'])) {
    echo "<p class='info'><strong>Código PIX para pagamento:</strong> " . $compra['codigo_pix'] . "</p>";
}

echo "<p class='info'>Você será redirecionado para a página de produtos em 10 segundos...</p>
      </div>";

// Estilo adicional para a página
echo "<style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        div { background: white; max-width: 500px; margin: 50px auto; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #4CAF50; }
        p { font-size: 16px; margin: 10px 0; }
      </style>";

// Link manual de redirecionamento
echo "<p class='info'><a href='todos_os_produtos.php'>Clique aqui se não for redirecionado automaticamente.</a></p>";

// Opcional: Limpar os dados da compra da sessão após exibir
unset($_SESSION['compra']);

// Usando JavaScript para aguardar 10 segundos e redirecionar
echo "<script>
        setTimeout(function() {
            window.location.href = 'todos_os_produtos.php';
        }, 10000); // 10 segundos
      </script>";
?>

        </section>
    </main>

     <!-- Seção de imagens à direita -->
     <div id="imgs_direita">
        <!-- Imagens decorativas à direita -->
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
    </div>

    <!-- Seção de imagens à esquerda -->
    <div id="imgs_esquerda">
        <!-- Imagens decorativas à esquerda -->
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
    </div>
</body>
</html>