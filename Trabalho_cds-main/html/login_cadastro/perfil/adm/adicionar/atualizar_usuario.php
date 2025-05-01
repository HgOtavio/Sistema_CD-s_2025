<?php
// Conexão com o banco
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

// Prepara a query sem o campo 'pais'
$stmt = $conn->prepare("INSERT INTO Usuario (nome_completo, email, cpf, telefone, login, senha, tipo, cep, estado, cidade, bairro, logradouro, numero, complemento) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Verifica se a preparação da query deu certo
if (!$stmt) {
    die("Erro ao preparar: " . $conn->error);
}

// Faz o bind dos parâmetros
$stmt->bind_param("ssssssssssssss", $nome_completo, $email, $cpf, $telefone, $login, $senha, $tipo, $cep, $estado, $cidade, $bairro, $logradouro, $numero, $complemento);

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
