<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");

// Verifica conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Dados vindos do formulário (via POST)
$nome_completo = $_POST['nome_completo'];
$email = $_POST['email'];
$cpf = $_POST['cpf'];
$telefone = $_POST['telefone'];
$login = $_POST['login'];
$senha = $_POST['senha'];
$tipo = $_POST['tipo'];
$cep = $_POST['cep'];
$estado = $_POST['estado'];
$cidade = $_POST['cidade'];
$bairro = $_POST['bairro'];
$logradouro = $_POST['logradouro'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];

// Gerar o ID do usuário (isso normalmente seria feito no banco de dados com auto-incremento)
$id_usuario = uniqid(); // Substitua com a lógica real para obter o id do usuário

// Processar a foto de perfil
$foto_perfil = null;
if (!empty($_FILES['foto_perfil']['name'])) {
    $foto_nome = $_FILES['foto_perfil']['name'];
    $foto_tmp = $_FILES['foto_perfil']['tmp_name'];
    $foto_ext = pathinfo($foto_nome, PATHINFO_EXTENSION);

    // Define o novo nome da foto
    $novo_nome_foto = "foto_" . $id_usuario . "." . $foto_ext;

    // Diretório para salvar a foto
    $diretorio = "../../../../../img/php_cliente/uploads/";

    // Movendo o arquivo
    if (move_uploaded_file($foto_tmp, $diretorio . $novo_nome_foto)) {
        // Atualizar caminho salvo no banco
        $foto_perfil = "../uploads/" . $novo_nome_foto;
    } else {
        echo "Erro ao fazer upload da foto!";
        exit();
    }
}

// Prepara a query para inserir o novo usuário
$stmt = $conn->prepare("INSERT INTO Usuario (nome_completo, email, cpf, telefone, login, senha, tipo, cep, estado, cidade, bairro, logradouro, numero, complemento, foto_perfil) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Verifica se a preparação da query deu certo
if (!$stmt) {
    die("Erro ao preparar: " . $conn->error);
}

// Faz o bind dos parâmetros
$stmt->bind_param("sssssssssssssss", $nome_completo, $email, $cpf, $telefone, $login, $senha, $tipo, $cep, $estado, $cidade, $bairro, $logradouro, $numero, $complemento, $foto_perfil);

// Executa a query
if ($stmt->execute()) {
    echo "Usuário cadastrado com sucesso!";
} else {
    echo "Erro ao cadastrar: " . $stmt->error;
}

// Fecha a conexão
$stmt->close();
$conn->close();
?>
