<?php
session_start();
include "../login_cadastro/conexao.php";

if (!isset($_SESSION['id_usuario'])) {
    die("Acesso negado.");
}

$id_usuario = $_SESSION["id_usuario"];
$nota = $_POST["nota"] ?? null;
$comentario = $_POST["comentario"] ?? '';
$id_cd = isset($_POST["id_cd"]) && $_POST["id_cd"] !== '' ? $_POST["id_cd"] : null;

if (!$nota) {
    die("Nota obrigatória.");
}

if ($id_cd !== null) {
    // Avaliação de CD
    $sql = "INSERT INTO avaliacao (nota, comentario, id_usuario, id_cd)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $nota, $comentario, $id_usuario, $id_cd);
} else {
    // Avaliação do sistema (sem CD)
    $sql = "INSERT INTO avaliacao (nota, comentario, id_usuario, id_cd)
            VALUES (?, ?, ?, NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $nota, $comentario, $id_usuario);
}

$stmt->execute();

// Redirecionamento
if ($id_cd) {
    header("Location: todos_os_produtos.php?id_cd=$id_cd");
} else {
    header("Location: ../pagina_inicial/index_logado.php");
}
exit();
?>
