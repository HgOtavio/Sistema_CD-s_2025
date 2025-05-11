<?php
include "../../login_cadastro/conexao.php";

// 1. Recebe os dados do formulário
$id_usuario = $_POST['id_usuario'];
$login = $_POST['login'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$confirmar_senha = $_POST['confirmar_senha'];
$nome_completo = $_POST['nome_completo'];
$telefone = $_POST['telefone'];
$cpf = $_POST['cpf'];
$cep = $_POST['cep'];
$estado = $_POST['estado'];
$cidade = $_POST['cidade'];
$bairro = $_POST['bairro'];
$logradouro = $_POST['logradouro'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];

// 2. Verifica se senha e confirmação foram preenchidas e se são iguais
$senha_sql = "";
if (!empty($senha) && $senha === $confirmar_senha) {
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $senha_sql = ", senha = '$senha_hash'";
} elseif (!empty($senha) && $senha !== $confirmar_senha) {
    echo "As senhas não coincidem.";
    exit;
}

// 3. Processamento da imagem, se enviada
$foto_perfil_sql = "";
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
    $nome_arquivo = $login . '.' . $extensao;

    // Caminho físico onde a imagem será salva
    $pasta_destino = "../../../img/php_cliente/uploads/";
    $caminho_completo = $pasta_destino . $nome_arquivo;

    // Caminho que será salvo no banco
    $caminho_banco = "../uploads/" . $nome_arquivo;

    // Move o arquivo
    move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_completo);

    $foto_perfil_sql = ", foto_perfil = '$caminho_banco'";
}

// 4. Atualiza os dados no banco
$sql = "UPDATE Usuario SET 
            login = ?, 
            email = ?, 
            nome_completo = ?, 
            telefone = ?, 
            cpf = ?, 
            cep = ?, 
            estado = ?, 
            cidade = ?, 
            bairro = ?, 
            logradouro = ?, 
            numero = ?, 
            complemento = ?
            $senha_sql
            $foto_perfil_sql
        WHERE id_usuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssssi", 
    $login, $email, $nome_completo, $telefone, $cpf, 
    $cep, $estado, $cidade, $bairro, $logradouro, 
    $numero, $complemento, $id_usuario);

if ($stmt->execute()) {
    header("Location: adm.php?editado=1");
    exit;
} else {
    echo "Erro ao atualizar: " . $conn->error;
}
?>
