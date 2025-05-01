<?php
$host = "localhost";
$usuario = "root";  // Altere se necessário
$senha = "";        // Altere se necessário
$banco = "LojaCDs";

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>