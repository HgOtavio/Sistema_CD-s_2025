<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Buscar CDs existentes
$sql_cd = "SELECT id_cd, titulo FROM CD";
$result_cd = $conn->query($sql_cd);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Musicas</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css">
    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit_musicas.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">
 <!-- Importando o Select2 para melhor seleção -->
 <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                    <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Imagem de perfil -->

                <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>
    </header>

    <main>
        <!-- Seção de Adicionar Dados do Musica -->
        <section id="adicionar">
        
            <!-- Título principal da página -->
            <h1 id="titulo">Adicionar Música</h1>
            
            <!-- Div que contém os campos de entrada do formulário -->
            <div id="inputs">
                
                <!-- Div para os dados do Musica-->
                <div id="cima">
                    <div class="separacao">
                            <!-- Subtítulo para a seção de dados do Musica -->
                            <h1 class="subtitulo" id="acesso">Dados da Musica:</h1>
                            <form action="processar_musica.php" method="post" enctype="multipart/form-data">

                            <!-- Campos de entrada -->
                            <input type="text" placeholder="Nome" class="input" name="nomeMusica" required>
                            <input type="text" placeholder="Duração" class="input tempo" name="tempo" step="0.01" min="0" required>
                            
                        </div>
                        
                        <!-- Div para os dados do artista -->
                        <div class="separacao" id="direita">
                            
                            <!-- Campos de entrada -->
                            <div>
                                <label for="upload" id="audio">Adicionar audio da Musica</label>
                                    <input class="input" type="file" class="input add_perfil_img" id="upload" hidden  name="audio" accept="audio/*">
                                </div> 
                                <label for="id_cd[]">Associar a CD(s):</label>
        <select name="id_cd[]" multiple="multiple" id="select-cd" required style="width: 100%;">
            <?php
            while ($cd = $result_cd->fetch_assoc()) {
                echo "<option value='{$cd['id_cd']}'>{$cd['titulo']}</option>";
            }
            ?>
        </select>

                            
                        </div>
                        <!-- Botão de Adicionar -->
                        <button id="button" type="submit">Adicionar</button>
                        </form>

                </div>
            </div>
        </section>
    </main>

    <!-- Seção de imagens à direita -->
    <div id="imgs_direita">
        <!-- Imagens decorativas à direita -->
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
        <div class="cortar"><img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e2"></div>
    </div>

    <!-- Seção de imagens à esquerda -->
    <div id="imgs_esquerda">
        <!-- Imagens decorativas à esquerda -->
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
        <img src="../../../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d2">
    </div>
    <script>
    $(document).ready(function() {
        $('#select-cd').select2({
            placeholder: "Selecione um ou mais CDs",
            allowClear: true
        });
    });
    </script>
</body>
</html>