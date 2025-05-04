<?php
// Conexão com o banco de dados
include "../../../../login_cadastro/conexao.php";

$id_usuario = $_GET['id']; // Pegando o ID do usuário da URL

// Receber os dados do formulário
$nome_completo = $_POST['nome_completo'];
$email = $_POST['email'];
$cpf = $_POST['cpf'];
$telefone = $_POST['telefone'];
$login = $_POST['login'];
$cep = $_POST['cep'];
$estado = $_POST['estado'];
$cidade = $_POST['cidade'];
$bairro = $_POST['bairro'];
$logradouro = $_POST['logradouro'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];
$pais = $_POST['pais'];
$senha_antiga = $_POST['senha_antiga'];
$senha_nova = $_POST['senha_nova'];
$confirmar_senha = $_POST['confirmar_senha'];

// Consultando os dados atuais do usuário (AGORA PUXANDO A FOTO TAMBÉM)
$query = "SELECT senha, foto_perfil FROM Usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Verificar se a senha antiga está correta
if (!empty($senha_antiga) && !empty($senha_nova) && !empty($confirmar_senha)) {
    if (password_verify($senha_antiga, $usuario['senha'])) {
        if ($senha_nova === $confirmar_senha) {
            $senha_nova_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
        } else {
            echo "As novas senhas não coincidem!";
            exit();
        }
    } else {
        echo "A senha antiga está incorreta!";
        exit();
    }
} else {
    $senha_nova_hash = $usuario['senha']; // Mantém a senha atual
}

// Processar a foto de perfil
$foto_perfil = $usuario['foto_perfil']; // Foto atual

if (!empty($_FILES['foto_perfil']['name'])) {
    // Se uma nova foto foi enviada
    $foto_nome = $_FILES['foto_perfil']['name'];
    $foto_tmp = $_FILES['foto_perfil']['tmp_name'];
    $foto_ext = pathinfo($foto_nome, PATHINFO_EXTENSION);

    // Define o novo nome da foto como o login do usuário
    $novo_nome_foto = $login . "." . $foto_ext;

    // Diretório para salvar a foto
    $diretorio = "../../../../../img/php_cliente/uploads/";

    // Movendo o arquivo
    move_uploaded_file($foto_tmp, $diretorio . $novo_nome_foto);

    // Atualizar caminho salvo no banco
    $foto_perfil = "../uploads/" . $novo_nome_foto;
}

// Atualizar os dados do usuário no banco
$query = "UPDATE Usuario SET 
    nome_completo = ?, 
    email = ?, 
    cpf = ?, 
    telefone = ?, 
    login = ?, 
    senha = ?, 
    foto_perfil = ?, 
    cep = ?, 
    estado = ?, 
    cidade = ?, 
    bairro = ?, 
    logradouro = ?, 
    numero = ?, 
    complemento = ?, 
    pais = ? 
WHERE id_usuario = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("sssssssssssssssi", 
    $nome_completo, 
    $email, 
    $cpf, 
    $telefone, 
    $login, 
    $senha_nova_hash, 
    $foto_perfil, 
    $cep, 
    $estado, 
    $cidade, 
    $bairro, 
    $logradouro, 
    $numero, 
    $complemento, 
    $pais, 
    $id_usuario
);

// Executar atualização
$stmt->execute();

// Redirecionar
header("Location: editar_user.php?id=$id_usuario");
exit();
?>
