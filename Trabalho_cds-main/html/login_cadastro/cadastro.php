<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="shortcut icon" href="../../img/favicon/favicon.ico" type="image/x-icon">
    
     <!-- Link para o arquivo de estilos CSS externo -->
    <link rel="stylesheet" href="../../css/cadastrar/cadastro.css">
    <link rel="stylesheet" href="../../css/cabeçalhos/cabeçalho_sem_login.css">

    <!-- Link para o arquivo de estilos JS externo -->
    <script src="../../js/mascaras/mascara_cpf.js" defer></script>
    <script src="../../js/mascaras/mascara_cep.js" defer></script>
    <script src="../../js/mascaras/mascara_tel.js" defer></script>
    <script src="../../js/mascaras/mascara_num.js" defer></script>
    
</head>
<body>
    <!-- Cabeçalho da página (sem o usuário estar logado) -->   <header> 
        <div id="parte_de_cima_cab">

            <!-- Logo da página -->
            <a href="#" id="logo"><img src="../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
                <div id="login_cadastro"> <!-- Login e cadastro -->
                    <a href="#"><img src="../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Ícone de perfil -->
                    <p id="p_cadastro_login">
                        <a href="#" class="a_cadastro_login">Cadastro</a> ou <br>
                        <a href="#" class="a_cadastro_login">Login</a>
                    </p>
                </div>
        </div>
    </header>
    <!-- Elemento principal da página, onde o formulário de cadastro está contido -->
    <main>
        <!-- Seção de cadastro -->
        <section id="cadastro">
        
            <!-- Título principal da página -->
            <h1 id="titulo">Cadastrar</h1>
            <form action="confirmar_cadastro.php" method="POST">

            
            <!-- Texto com link para redirecionar o usuário para a tela de login caso já tenha uma conta -->
            
                <p class="p_titulo">Preencha os campos com seus dados ou voltar para <a href="#" class="link_login">tela inicial.</a></p>
                <p class="p_titulo">Já tem uma conta? <a href="#" class="link_login">Logar</a></p>
          
            
            <!-- Div que contém os campos de entrada do formulário -->
            <div id="inputs">
                
                <!-- Div para os dados de acesso -->
                <div id="cima">
                    <div class="separacao">
                        <!-- Subtítulo para a seção de dados de acesso -->
                        <h1 class="subtitulo" id="acesso">Dados de acesso:</h1>
                        
                        <!-- Campos de entrada para usuário, e-mail, senha e confirmação de senha -->
                          
                        <input type="text" placeholder="Usuario" class="input" name="login" required>
                        <input type="email" placeholder="E-mail" class="input"  name="email" required>
                        <input type="password" placeholder="Senha" maxlength="20" class="input" name="senha" required>
                        <input type="password" placeholder="Confirmar Senha" maxlength="20" class="input"  name="confirma_senha" required>
                    </div>
                    
                    <!-- Div para os dados pessoais -->
                    <div class="separacao" id="direita">
                        <h1 class="subtitulo" id="pessoais">Dados pessoais:</h1>
                        <form action="confirmar_cadastro.php" method="POST">
      
                            <!-- Campos de entrada para nome completo, telefone e CPF -->
                            <input type="text" placeholder="Nome Completo" class="input"  name="nome_completo" required>
                            <input type="text" id="telefone" placeholder="Telefone" maxlength="15" class="input"  name="telefone" required>
                            <input type="text" id="cpf" placeholder="CPF" maxlength="14" class="input" name="cpf" required>
                            <div>
                                <label for="upload" class="input" id="add_perfil_img">Escolher foto de perfil</label>
                                <input type="file" id="upload "  name="foto_perfil" hidden>
                            </div>
                                <!-- Div para os dados de endereço -->
                            </div>
                            <div id="baixo">
                                <!-- Subtítulo para a seção de dados de endereço -->
                                <h1 class="subtitulo" id="endereco">Dados de endereço:</h1>
            
                                <div id="lado">
                                    <!-- Div com campos de entrada para endereço (CEP, estado, cidade, bairro) -->
                                    <div class="separacao2">
                                        <input type="text"  id="cep" placeholder="CEP" maxlength="9" class="input_baixo" name="cep" required>
                                        <input type="text" placeholder="Estado" class="input_baixo" name="estado" required>
                                        <input type="text" placeholder="Cidade" class="input_baixo" name="cidade" required>
                                        <input type="text" placeholder="Bairro" class="input_baixo" name="bairro" required>
                                    </div>
                                    
                                    <!-- Div com campos de entrada para o restante do endereço (logradouro, número, complemento) -->
                                    <div class="separacao2" id="direita_baixo">
                                        <input type="text" placeholder="Logradouro / Rua" class="input_baixo" name="logradouro" required>
                                        <input type="text" placeholder="Número" class="input_baixo num" id="numero" name="numero" required >
                                        <input type="text" placeholder="Complemento" class="input_baixo" name="complemento">
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
        <div class="cortar"><img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
    </div>

    <!-- Seção de imagens à esquerda -->
    <div id="imgs_esquerda">
        <!-- Imagens decorativas à esquerda -->
        <img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
    </div>
</body>
</html>