<?php
session_start();

// Verificação de login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login_cadastro/login.php");
    exit();
}

// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro ao conectar ao banco de dados: " . $conn->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];
$res = $conn->query("SELECT * FROM Usuario WHERE id_usuario = $id_usuario");
$usuarioLogado = $res->fetch_assoc();

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



$stmtAv = $conn->prepare($sqlAv);
$stmtAv->execute();
$resultAv = $stmtAv->get_result();


?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugestões</title>
    <link rel="shortcut icon" href="../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../css/sugestao/sugestao.css">
    <link rel="stylesheet" href="../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../js/sugestao/sugestao.js" defer></script>
    <script src="../../../../js/mascaras/mascara_ano.js" defer></script>
    <script src="../../../../js/mascaras/mascara_temp.js" defer></script>
    <script src="../../../../js/mascaras/mascara_data.js" defer></script>
</head>
<body>
    <header> 
        <div id="parte_de_cima_cab">

            <!-- Logo da página -->
            <a href="#" id="logo"><img src="../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Barra de pesquisa -->

            <!-- Ícones de perfil e carrinho de compras -->
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
                <a href="#"><img src="../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a>
            </div>
        </div>
    </header>
    <main>
        <section id="sugestao">
            <div id="but_v"><button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button></div>
            <h1 id="titulo">Sugestões</h1>
            <p id="subtitulo">Tipo de sugestão</p>
            <form action="salvar_sugestao.php" method="POST" enctype="multipart/form-data">

                  <select id="tipo_sugestao" name="tipo">
                              <option value="">Selecione...</option>
                              <option value="cd">Cds</option>
                              <option value="musica">Músicas</option>
                              <option value="artista">Artistas</option>
                  </select>
              
            <div id="cd" class="itens_input">
                <div class="lado">
                    <input type="text" name="titulo_cd" placeholder="Titulo do CD" class="input">
                    <div>
                        <label for="upload" class="input add_perfil_img" id="add_perfil_img">Adicionar foto da Capa</label>
                        <input type="file" name="capa_cd" id="upload" hidden>
                    </div>
                    <input type="text" placeholder="Ano de Lançamento"  name="ano_cd"  class="input ano">
                </div>
                <div class="lado">
                    <input type="text" placeholder="Descrição" name="descricao" class="input">
                    <input type="text" placeholder="Gênero" name="genero" class="input">
                </div>
            </div>
            <div id="musica" class="itens_input">
                <div class="lado">
                    <input type="text" placeholder="Nome da Música"  name="nome_musica"class="input">
                    <input type="text" placeholder="Duração (minutos)" name="tempo_musica" class="input tempo">
                    <input type="text" placeholder="Gênero"  name="genero" class="input">
                </div>
                <lado>
                    <div>
                        <label for="upload" id="audio">Adicionar audio da Musica</label>
                        <input type="file" name="tempo_musica" class="input add_perfil_img" id="upload" hidden>
                    </div>
                    <input type="text" placeholder="Descrição"  name="descricao"class="input">
                </lado>
            </div>
            
            <div id="artista" class="itens_input">
                <div class="lado">
                    <input type="text" placeholder="Nome do Artista" name="nome_artista" class="input">
                    <div>
                        <label for="upload" class="input add_perfil_img" id="add_perfil_img">Adicionar foto do Artista</label>
                        <input type="file" id="upload" name="foto_artista" hidden>
                    </div>
                    <input type="text" placeholder="Data de Nascimento"  name="data_nascimento"  class="input data">
                </div>
                <div class="lado">
                    <input type="text" placeholder="Descrição"  name="descricao" class="input">
                    <input type="text" placeholder="Gênero" name="genero" class="input">
                </div>
            </div>
            <button id="button" type="submit" >Enviar Sugestão</button>
            </form>
        </section>
    </main>

     <!-- Seção de imagens decorativas à direita -->
     <div id="imgs_direita">
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita sumir"></div>
    </div>

    <!-- Seção de imagens decorativas à esquerda -->
    <div id="imgs_esquerda">
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda sumir">
    </div>

</body>
</html>