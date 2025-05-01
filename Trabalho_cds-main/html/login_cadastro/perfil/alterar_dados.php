<?php
session_start();
include "../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];


// Busca os dados atuais do usuário
$stmt = $conn->prepare("SELECT * FROM Usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
// Lógica para buscar em dois diretórios
$foto = $usuario['foto_perfil'];
$foto_cliente = "../php_cliente/uploads/" . basename($foto);
$foto_admin = "php_cliente/uploads/" . basename($foto);

if (!empty($foto) && file_exists($foto_cliente)) {
    $foto_perfil = $foto_cliente;
} elseif (!empty($foto) && file_exists($foto_admin)) {
    $foto_perfil = $foto_admin;
} else {
    $foto_perfil = "../php_cliente/uploads/default.png";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Dados de Usuario</title>
    <link rel="shortcut icon" href="../../../img/favicon/favicon.ico" type="image/x-icon">
    
     <!-- Link para o arquivo de estilos CSS externo -->
    <link rel="stylesheet" href="../../../css/alterar/alterar.css">
    <link rel="stylesheet" href="../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <!-- Link para o arquivo de estilos JS externo -->
    <script src="../../../js/mascaras/mascara_cpf.js" defer></script>
    <script src="../../../js/mascaras/mascara_cep.js" defer></script>
    <script src="../../../js/mascaras/mascara_tel.js" defer></script>
    <script src="../../../js/mascaras/mascara_num.js" defer></script>
    
</head>
<body>
    <!-- Cabeçalho da página (com user logado) -->
    <header> 

        <div id="parte_de_cima_cab">

              <!-- Logo da página -->
              <a href="#" id="logo"><img src="../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Barra de pesquisa -->

            <div id="login_carrinho"> <!-- Conta e Carrinho -->
                    <a href="#"><img src="../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Imagem de perfil -->

                <a href="#"><img src="../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>
    </header>
    <!-- Elemento principal da página, onde o formulário de cadastro está contido -->
    <main>
        <!-- Seção de Alterar dados -->
        <section id="alterar">
        
            <!-- Título principal da página -->
            <h1 id="titulo">Alterar Dados</h1>
            <form action="admin_editar_usuario_salvar.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">

            
                <!-- Div que contém os campos de entrada do formulário -->
                <div id="inputs">
                    
                    <!-- Div para os dados de acesso -->
                    <div id="cima">
                        <div class="separacao">
                            <!-- Subtítulo para a seção de dados de acesso -->
                            <h1 class="subtitulo" id="acesso">Dados de acesso:</h1>
                            
                            <!-- Campos de entrada para usuário, e-mail, senha e confirmação de senha -->
                            <input type="text" placeholder="Usuario" class="input" name="login" value="<?php echo $usuario['login']; ?>" required>
                            <input type="email" placeholder="E-mail" class="input" name="email" value="<?php echo $usuario['email']; ?>" required>
                            <input type="text" placeholder="Senha" maxlength="20" class="input"  name="senha">
                            <input type="text" placeholder="Confirmar Senha" maxlength="20" class="input" name="confirmar_senha">
                        </div>
                        
                        <!-- Div para os dados pessoais -->
                        <div class="separacao" id="direita">
                            <h1 class="subtitulo" id="pessoais">Dados pessoais:</h1>
                            
                            <!-- Campos de entrada para nome completo, telefone e CPF -->
                            <input type="text" placeholder="Nome Completo" class="input"  name="nome_completo" value="<?php echo $usuario['nome_completo']; ?>" required>
                            <input type="text" id="telefone" placeholder="Telefone" maxlength="15" name="telefone" value="<?php echo $usuario['telefone']; ?>" required class="input">
                            <input type="text" id="cpf" placeholder="CPF" maxlength="14" class="input cpf" name="cpf" value="<?php echo $usuario['cpf']; ?>" required>

    <!-- Exibir a foto atual -->
    <?php if (!empty($usuario['foto_perfil'])): ?>
        <img src="<?php echo $usuario['foto_perfil']; ?>" alt="Foto Atual" width="120" style="border-radius: 10px;"><br><br>
    <?php endif; ?>                            <div>
                                
                                <label for="upload" class="input" id="add_perfil_img">Escolher foto de perfil</label>
                                <input type="file" id="upload" hidden name="foto_perfil">
                            </div>
                                <!-- Div para os dados de endereço -->
                            </div>
                            <div id="baixo">
                                <!-- Subtítulo para a seção de dados de endereço -->
                                <h1 class="subtitulo" id="endereco">Dados de endereço:</h1>
            
                                <div id="lado">
                                    <!-- Div com campos de entrada para endereço (CEP, estado, cidade, bairro) -->
                                    <div class="separacao2">
                                        <input type="text"  id="cep" placeholder="CEP" maxlength="9" class="input_baixo cep"  name="cep" value="<?php echo $usuario['cep']; ?>">
                                        <input type="text" placeholder="Estado" class="input_baixo" name="estado" value="<?php echo $usuario['estado']; ?>">
                                        <input type="text" placeholder="Cidade" class="input_baixo" name="cidade" value="<?php echo $usuario['cidade']; ?>">
                                        <input type="text" placeholder="Bairro" class="input_baixo "  name="bairro" value="<?php echo $usuario['bairro']; ?>">
                                    </div>
                                    
                                    <!-- Div com campos de entrada para o restante do endereço (logradouro, número, complemento) -->
                                    <div class="separacao2" id="direita_baixo">
                                        <input type="text" placeholder="Logradouro / Rua" class="input_baixo" name="logradouro" value="<?php echo $usuario['logradouro']; ?>">
                                        <input type="text" placeholder="Número" class="input_baixo num" id="numero" name="numero" value="<?php echo $usuario['numero']; ?>">
                                        <input type="text" placeholder="Complemento" class="input_baixo" name="complemento" value="<?php echo $usuario['complemento']; ?>">
                                    </div>
                                </div>
                            </div>
                        <!-- Botão de cadastro -->
                        <button id="button" type="submit">Cadastrar</button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <!-- Seção de imagens à direita -->
    <div id="imgs_direita">
        <!-- Imagens decorativas à direita -->
        <div class="cortar"><img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
        <div class="cortar"><img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e2"></div>
    </div>

    <!-- Seção de imagens à esquerda -->
    <div id="imgs_esquerda">
        <!-- Imagens decorativas à esquerda -->
        <img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
        <img src="../../../img/alterar_dados/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d2">
    </div>
</body>
</html>