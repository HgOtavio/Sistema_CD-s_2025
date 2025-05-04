<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é do tipo administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Obtém o id do usuário logado
$id_usuario = $_SESSION['id_usuario'];

// Filtros
$filtro_nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$filtro_email = isset($_GET['email']) ? $_GET['email'] : '';
$filtro_cpf = isset($_GET['cpf']) ? $_GET['cpf'] : '';
$filtro_login = isset($_GET['login']) ? $_GET['login'] : '';
$filtro_cep = isset($_GET['cep']) ? $_GET['cep'] : '';
$filtro_telefone = isset($_GET['telefone']) ? $_GET['telefone'] : '';

$sql = "SELECT * FROM Usuario WHERE (tipo = 'cliente' OR tipo = 'admin')";

if (!empty($filtro_nome)) {
    $sql .= " AND nome_completo LIKE '%" . $conn->real_escape_string($filtro_nome) . "%'";
}
if (!empty($filtro_email)) {
    $sql .= " AND email LIKE '%" . $conn->real_escape_string($filtro_email) . "%'";
}
if (!empty($filtro_cpf)) {
    $sql .= " AND cpf LIKE '%" . $conn->real_escape_string($filtro_cpf) . "%'";
}
if (!empty($filtro_login)) {
    $sql .= " AND login LIKE '%" . $conn->real_escape_string($filtro_login) . "%'";
}
if (!empty($filtro_cep)) {
    $sql .= " AND cep LIKE '%" . $conn->real_escape_string($filtro_cep) . "%'";
}
if (!empty($filtro_telefone)) {
    $sql .= " AND telefone LIKE '%" . $conn->real_escape_string($filtro_telefone) . "%'";
}

// Busca dados do usuário logado
$sql_usuario_logado = "SELECT login, foto_perfil FROM Usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql_usuario_logado);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_usuario_logado = $stmt->get_result();
$usuario_logado = $result_usuario_logado->fetch_assoc();

// Se não encontrar usuário, redireciona (por segurança)
if (!$usuario_logado) {
    header("Location: ../php/login.php");
    exit();
}

// Guarda login e foto
$login_usuario_logado = $usuario_logado['login'];
$foto_perfil_usuario = $usuario_logado['foto_perfil'];

// Define caminho correto da foto
$caminho_foto = "../php_php/uploads/" . basename($foto_perfil_usuario);
if (!empty($foto_perfil_usuario) && file_exists($caminho_foto)) {
    $foto_exibir = $caminho_foto;
} else {
    $foto_exibir = "../php_cliente/uploads/default.png"; // Foto padrão
}


$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuarios</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar.css">
    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar_user.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../../../js/adm/gerenciar/gerenciar_filtro.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_cpf.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_tel.js" defer></script>
    <script src="../../../../../js/mascaras/mascara_cep.js" defer></script>
</head>
<body>

    <!-- Cabeçalho da página (com user logado) -->
    <header> 

        <div id="parte_de_cima_cab">

              <!-- Logo da página -->
              <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Barra de pesquisa -->

            <div id="login_carrinho">
                    <div id="perfil_usuario_logado">
                        <img src="<?php echo $foto_exibir; ?>" alt="Perfil" id="Perfil" width="40" height="40" style="border-radius: 50%;">
                        <span id="login_usuario" style="margin-left: 10px;"><?php echo htmlspecialchars($login_usuario_logado); ?></span>
                    </div>
             <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a>
        </div>

        </div>
    </header>
    
    <button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button>

    <h1 id="titulo">Gerenciar Usuarios</h1>
    <form method="get" action="">
        <button id="btn_filtro">Filtros</button>
        <section id="filtro">
            <div id="separar">
                <div class="lado">
                    <p class="p_filtro">Usuario:<input type="text" class="input_filtro" name="login" value="<?php echo $filtro_login; ?>"></p>
                    <p class="p_filtro">E-mail:<input type="email" class="input_filtro" name="email" value="<?php echo $filtro_email; ?>"></p>
                </div>
                <div class="lado">
                    <p class="p_filtro">Nome:<input type="text" name="nome" class="input_filtro" value="<?php echo $filtro_nome; ?>" ></p>
                    <p class="p_filtro">CPF:<input type="text" class="input_filtro" id="cpf" maxlength="14" name="cpf" value="<?php echo $filtro_cpf; ?>"></p>
                </div>
                <div class="lado">
                    <p class="p_filtro">CEP:<input type="text" name="cpf" class="input_filtro" id="cep" maxlength="9"  name="cep" value="<?php echo $filtro_cep; ?>"></p>
                    <p class="p_filtro">Telefone:<input type="text" name="telefone" class="input_filtro" id="telefone" maxlength="15" value="<?php echo $filtro_telefone; ?>"></p>
                </div>
            </div>
            <div>
                <button id="button_filtro" type="submit">Procurar</button>
                <button type="button" id="button_filtro" onclick="window.location.href='gerenciar_user.php';">Todos</button>

            </div>
        </section>
        </form>
    <button class="button_voltar"><a href="../adicionar/add_user.php" class="link_voltar">Adicionar Usuarios</a></button>



    <form method="post" action="excluir_selecionados.php">
    <button id="button_excluir" type="submit">Excluir</button>
        <section id="tabelao">
            <table id="tabela">
                <thead>
               
                    <tr id="itens_cabeca">
                        <th class="titulo_item id">ID</th>
                        <th class="titulo_item">Foto</th>
                        <th class="titulo_item">Usuario</th>
                        <th class="titulo_item">E-mail</th>
                        <th class="titulo_item">Nome</th>
                        <th class="titulo_item">Telefone</th>
                        <th class="titulo_item">CPF</th>
                        <th class="titulo_item">CEP</th>
                        <th class="titulo_item">Ações</th>
                    </tr>
                </thead>
 <!-- #region -->   <?php while ($usuario = $result->fetch_assoc()): ?>
                        <tbody>
                            <tr class="informações">
                                <th class="info id"><?php echo $usuario['id_usuario']; ?></th>
                                <th class="info">  <?php
                        $foto = $usuario['foto_perfil'];
                        $foto_cliente = "../../../../../img/php_cliente/uploads/" . basename($foto);

                        if (!empty($foto) && file_exists($foto_cliente)) {
                            echo "<img src='$foto_cliente' width='50' height='50'>";
                        } elseif (!empty($foto) && file_exists($foto_cliente)) {
                            echo "<img src='$foto_cliente' width='50' height='50'>";
                        } else {
                            echo "<img src='../php_cliente/uploads/default.png' width='50' height='50'>";
                        }
                        ?>
                        </th>
                                <th class="info"><?php echo $usuario['login']; ?></th>
                                <th class="info"><?php echo $usuario['email']; ?></th>
                                <th class="info"><?php echo $usuario['nome_completo']; ?></th>
                                <th class="info"><?php echo $usuario['telefone']; ?></th>
                                <th class="info"><?php echo $usuario['cep']; ?></th>
                                <th class="info"><?php echo $usuario['cpf']; ?></th>
                                <th class="info">
                                    <div class="separar">
                                        <a href="../editar/editar_user.php?id=<?php echo $usuario['id_usuario']; ?>" class="link_acao">Editar</a>
                                        <a href="atividades_usuario.php?id=<?= $usuario['id_usuario'] ?>" class="link_acao">Atividades</a>
                                    </div> 
                                    <input type="checkbox" name="excluir[]" value="<?= $usuario['id_usuario']; ?>" id="checkbox_<?= $usuario['id_usuario']; ?>" class="input">
                                <label for="checkbox_<?= $usuario['id_usuario']; ?>"></label>



                                </th>
                            </tr>
                     </tbody>
                    <?php endwhile; ?>
            </table>
        </section>
    </form>
    <script>
    const checkboxes = document.querySelectorAll('.excluir_checkbox');
    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', (e) => {
            const id = e.target.value;
            const img = document.getElementById('img_' + id);
            const label = document.getElementById('label_' + id);

            if (e.target.checked) {
                img.src = 'check_checked.png';  // Imagem quando marcado
            } else {
                img.src = 'check_unchecked.png';  // Imagem quando desmarcado
            }
        });
    });
</script>

</body>
</html>