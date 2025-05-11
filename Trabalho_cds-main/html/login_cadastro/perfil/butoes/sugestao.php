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
                <a href="#"><img src="../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a>
                <a href="#"><img src="../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a>
            </div>
        </div>
    </header>
    <button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button>
    <main>
        <section id="sugestao">
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