<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LojaCDs";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Coleta os dados do formulário
$nome = $_POST['nome_completo'];
$email = $_POST['email'];
$cpf = $_POST['cpf'];
$telefone = $_POST['telefone'];
$login = $_POST['login'];
$senha = $_POST['senha'];
$confirma_senha = $_POST['confirma_senha'];
$tipo = $_POST['tipo'];
$cep = $_POST['cep'];
$estado = $_POST['estado'];
$cidade = $_POST['cidade'];
$bairro = $_POST['bairro'];
$logradouro = $_POST['logradouro'];
$numero = $_POST['numero'];
$complemento = $_POST['complemento'];

// Verifica se as senhas coincidem
if ($senha !== $confirma_senha) {
    echo "As senhas não coincidem. <a href='cadastro.php'>Tente novamente</a>.";
    exit();
}

// Verifica se login ou email ou cpf já existem
$sql_verifica = "SELECT * FROM Usuario WHERE login = ? OR email = ? OR cpf = ?";
$stmt_verifica = $conn->prepare($sql_verifica);
$stmt_verifica->bind_param("sss", $login, $email, $cpf);
$stmt_verifica->execute();
$result = $stmt_verifica->get_result();

if ($result->num_rows > 0) {
    echo "Login, e-mail ou CPF já estão em uso. <a href='cadastro.php'>Tente novamente</a>.";
    exit();
}

// Criptografa a senha antes de salvar
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere todos os dados diretamente na tabela Usuario
$sql = "INSERT INTO Usuario (
    nome_completo, email, cpf, telefone, login, senha, tipo,
    cep, estado, cidade, bairro, logradouro, numero, complemento
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssssss",
    $nome,
    $email,
    $cpf,
    $telefone,
    $login,
    $senha_hash,
    $tipo,
    $cep,
    $estado,
    $cidade,
    $bairro,
    $logradouro,
    $numero,
    $complemento
);

if ($stmt->execute()) {
    echo "Cadastro realizado com sucesso! <a href='login.php'>Ir para login</a>.";
} else {
    echo "Erro ao cadastrar: " . $stmt->error;
}

$conn->close();
?>
