<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verificar se o usuário está logado e se é administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Verificar se o ID do usuário foi passado
if (!isset($_GET['id'])) {
    echo "Usuário não especificado.";
    exit();
}

$id_usuario = $_GET['id'];

// Buscar o login do usuário (alterado de nome_completo para login)
$login = "";
$busca_login = $conn->prepare("SELECT login FROM Usuario WHERE id_usuario = ?");
$busca_login->bind_param("i", $id_usuario);
$busca_login->execute();
$busca_login->bind_result($login);
$busca_login->fetch();
$busca_login->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Atividdades do User</title>
    <link rel="shortcut icon" href="../../../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar.css">
    <link rel="stylesheet" href="../../../../../css/adm/gerenciar/gerenciar_at.css">
    <link rel="stylesheet" href="../../../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">
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
    <button class="button_voltar"><a href="#" class="link_voltar">Voltar</a></button>
    
    <h1 class="titulo">Gerenciar Atividade do  <?= htmlspecialchars($login) ?></h1>
    <form method="post" action="processar_acoes_usuario.php?id=<?=$id_usuario?>">


    <button id="button_excluir"type="submit"  name="acao" value="remover_itens_selecionados">Excluir</button>
    <button class="button_voltar link_voltar">Editar</button>

    <h1 class="subtitulo">Compras Realizadas</h1>
    <section id="tabelao" >
        <table id="tabela">
            <thead>
                <tr id="itens_cabeca">
                    <th class="titulo_item ">Excluir</th>
                    <th class="titulo_item">ID</th>
                    <th class="titulo_item">Titulo</th>
                    <th class="titulo_item">Quantidade</th>
                    <th class="titulo_item">Status</th>
                    <th class="titulo_item">Forma de Pagamento</th>
                    <th class="titulo_item">Tipo de Pagamento</th>
                    <th class="titulo_item">Envio</th>
                    <th class="titulo_item">Data</th>
                </tr>
            </thead>
            <tbody>
<?php
$sql = "SELECT c.id_compra, cd.titulo, c.quantidade, c.status, c.forma_pagamento, c.tipo_pagamento, c.tipo_envio, c.data_compra
        FROM Compra c
        INNER JOIN CD cd ON c.id_cd = cd.id_cd
        WHERE c.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<tr><th colspan='9' class='info'>Nenhuma compra realizada encontrada.</th></tr>";
} else {
    while ($row = $res->fetch_assoc()) {
        echo "<tr class='informações'>
                <th class='info'><input type='checkbox' class='input' id='compra_{$row['id_compra']}' name='compras_cancelar[]' value='{$row['id_compra']}'><label for='compra_{$row['id_compra']}'></label></th>
                <th class='info'>{$row['id_compra']}</th>
                <th class='info'>{$row['titulo']}</th>
                <th class='info'>{$row['quantidade']}</th>
                <th class='info'>{$row['status']}</th>
                <th class='info'>{$row['forma_pagamento']}</th>
                <th class='info'>{$row['tipo_pagamento']}</th>
                <th class='info'>{$row['tipo_envio']}</th>
                <th class='info'>" . (new DateTime($row['data_compra']))->format('d/m/Y') . "</th>
              </tr>";
    }
}
?>
</tbody>

        </table>
    </section>

    <div id="lado">
        <div class="div">
            <h1 class="subtitulo">Itens no Carrinho</h1>
            <section id="tab_carrinho">
                <table id="carrinho">
                    <thead>
                        <tr id="itens_cabeca">
                            <th class="titulo_item">CD</th>
                            <th class="titulo_item">Quantidade</th>
                            <th class="titulo_item">Excluir</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
$sql = "SELECT cd.titulo, ca.quantidade, ca.id_cd
        FROM Carrinho ca
        INNER JOIN CD cd ON ca.id_cd = cd.id_cd
        WHERE ca.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<tr><th colspan='3' class='info'>Nenhum item no carrinho.</th></tr>";
} else {
    while ($row = $res->fetch_assoc()) {
        echo "<tr class='informações'>
                <th class='info'><input type='checkbox' id='carrinho_{$row['id_cd']}' class='input' name='cds_carrinho[]' value='{$row['id_cd']}'><label for='carrinho_{$row['id_cd']}'></label></th>
                <th class='info'>{$row['titulo']}</th>
                <th class='info'>{$row['quantidade']}</th>
              </tr>";
    }
}
?>
</tbody>

                    </tbody>
                </table>
            </section>
        </div>
        <div class="div">
            <h1 class="subtitulo">Favoritos</h1>
            <section id="tab_favorito">
                <table id="favorito">
                    <thead>
                        <tr id="itens_cabeca">
                            <th class="titulo_item">CD</th>
                            <th class="titulo_item">Excluir</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
$sql = "SELECT cd.titulo, cd.id_cd
        FROM Favoritos f
        INNER JOIN CD cd ON f.id_cd = cd.id_cd
        WHERE f.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<tr><th colspan='2' class='info'>Nenhum CD favoritado.</td></tr>";
} else {
    while ($row = $res->fetch_assoc()) {
        echo "<tr class='informações'>
                <th class='info'><input type='checkbox' id='favorito_{$row['id_cd']}' name='cds_favoritos[]' value='{$row['id_cd']}'><label for='favorito_{$row['id_cd']}'></label></th>
                <th class='info'>{$row['titulo']}</th>
              </tr>";
    }
}
?>
</tbody>

                </table>
            </section>
        </div>
    </div>
 </form>

</body>
</html>