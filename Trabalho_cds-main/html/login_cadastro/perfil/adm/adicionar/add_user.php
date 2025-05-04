<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é do tipo administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Obtém os filtros da URL (caso existam)
$filtro_titulo = isset($_GET['titulo']) ? $_GET['titulo'] : '';
$filtro_genero = isset($_GET['genero']) ? $_GET['genero'] : '';
$filtro_ano = isset($_GET['ano']) ? $_GET['ano'] : '';
$filtro_disponibilidade = isset($_GET['disponibilidade']) ? $_GET['disponibilidade'] : '';
$filtro_destaque = isset($_GET['destaque']) ? $_GET['destaque'] : '';
$filtro_preco_min = isset($_GET['preco_min']) ? $_GET['preco_min'] : '';
$filtro_preco_max = isset($_GET['preco_max']) ? $_GET['preco_max'] : '';

// Consulta CDs com base nos filtros
$sql_cd = "SELECT id_cd, titulo, capa, disponibilidade, preco, destaque, anoLancamento, genero, descricao, numero_vendas FROM CD WHERE 1=1";

if (!empty($filtro_titulo)) {
    $sql_cd .= " AND titulo LIKE '%$filtro_titulo%'";
}

if (!empty($filtro_genero)) {
    $sql_cd .= " AND genero LIKE '%$filtro_genero%'";
}

if (!empty($filtro_ano)) {
    $sql_cd .= " AND anoLancamento = $filtro_ano";
}

if (!empty($filtro_disponibilidade)) {
    $sql_cd .= " AND disponibilidade LIKE '%$filtro_disponibilidade%'";
}
// Filtragem por destaque
if (!empty($filtro_destaque)) {
    if ($filtro_destaque == 'Destaque') {
        // Filtro para destacar CDs com destaque
        $sql_cd .= " AND destaque = 'Destaque'";
    } elseif ($filtro_destaque == 'Não Destaque') {
        // Filtro para CDs sem destaque
        $sql_cd .= " AND (destaque IS NULL OR destaque != 'Destaque')";
    }
}



if (!empty($filtro_preco_min)) {
    $sql_cd .= " AND preco >= $filtro_preco_min";
}

if (!empty($filtro_preco_max)) {
    $sql_cd .= " AND preco <= $filtro_preco_max";
}

$result_cd = $conn->query($sql_cd);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuarios</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/add_edit/add_edit.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../../js/mascaras/mascara_cpf.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_tel.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_cep.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_num.js" defer></script>
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
        <!-- Seção de Adicionar Usuario -->
        <section id="adicionar">
        
            <!-- Título principal da página -->
            <h1 id="titulo">Adicionar Novo Usuario</h1>
            
             <!-- Div que contém os campos de entrada do formulário -->
             <div id="inputs">
                
                <!-- Div para os dados de acesso -->
                <div id="cima">
                    <div class="separacao">
                        <!-- Subtítulo para a seção de dados de acesso -->
                        <h1 class="subtitulo" id="acesso">Dados de acesso:</h1>
                        <form action="atualizar_usuario.php" method="POST" enctype="multipart/form-data">
                     
                                <!-- Campos de entrada para usuário, e-mail, senha e confirmação de senha -->
                                <input type="text" placeholder="Usuario" class="input" name="login" required>
                                <input type="email" placeholder="E-mail" class="input"  name="email" required>
                                <input type="text" placeholder="Senha" maxlength="20" class="input"name="senha" required>
                                <input type="text" placeholder="Confirmar Senha" maxlength="20" class="input" name="confirmar_senha" required>
                            </div>
                            
                            <!-- Div para os dados pessoais -->
                            <div class="separacao" id="direita">
                                <h1 class="subtitulo" id="pessoais">Dados pessoais:</h1>
                                
                                <!-- Campos de entrada para nome completo, telefone e CPF -->
                                <input type="text" placeholder="Nome Completo" class="input" name="nome_completo" required> 
                                <input type="text" id="telefone" placeholder="Telefone" maxlength="15" class="input"  name="telefone" required>
                                <input type="text" id="cpf" placeholder="CPF" maxlength="14" class="input"  name="cpf" required>
                                <div>
                                    <label for="upload" class="input add_perfil_img">Escolher foto de perfil</label>
                                    <input type="file" id="upload"  name="foto_perfil" hidden>
                                </div>
                                    <!-- Div para os dados de endereço -->
                                </div>
                                <div id="baixo">
                                    <!-- Subtítulo para a seção de dados de endereço -->
                                    <h1 class="subtitulo" id="endereco">Dados de endereço:</h1>
                
                                    <div id="lado">
                                        <!-- Div com campos de entrada para endereço (CEP, estado, cidade, bairro) -->
                                        <div class="separacao2">
                                            <input type="text"  id="cep" placeholder="CEP" maxlength="9" class="input_baixo" name="cep">
                                            <input type="text" placeholder="Estado" class="input_baixo" name="estado">
                                            <input type="text" placeholder="Cidade" class="input_baixo" name="cidade">
                                            <input type="text" placeholder="Bairro" class="input_baixo" name="bairro">
                                        </div>
                                        
                                        <!-- Div com campos de entrada para o restante do endereço (logradouro, número, complemento) -->
                                        <div class="separacao2" id="direita_baixo">
                                     
                                           <label>Tipo de Usuário:</label><br>
    <select  class="input_baixo" name="tipo" required>
        <option value="cliente">Cliente</option>
        <option value="admin">Administrador</option>
    </select><br><br>
                                            <input type="text" placeholder="Logradouro / Rua" class="input_baixo" name="logradouro">
                                            <input type="text" placeholder="Número" class="input_baixo num" id="numero" name="numero" >
                                            <input type="text" placeholder="Complemento" class="input_baixo" name="complemento"> 
                                        </div>
                                    </div>
                                </div>
                            <!-- Botão de cadastro -->
                            <button id="button">Adicionar</button>
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
</body>
</html>