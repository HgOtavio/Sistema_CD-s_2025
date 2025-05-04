<?php
session_start();
include "../login_cadastro/conexao.php";

if (!isset($_POST['id_cd']) || empty($_POST['id_cd'])) {
    $_SESSION['mensagem'] = "ID do CD inválido.";
    header("Location: listar_cds.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_cd = $_POST['id_cd'];

// Verifica se já está favoritado
$sql_verificar = "SELECT 1 FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
$stmt = $conn->prepare($sql_verificar);
$stmt->bind_param("ii", $id_usuario, $id_cd);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Já favoritado, remover
    $stmt->close();
    $sql_delete = "DELETE FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
    $stmt_del = $conn->prepare($sql_delete);
    $stmt_del->bind_param("ii", $id_usuario, $id_cd);
    $stmt_del->execute();
    $stmt_del->close();
    $_SESSION['mensagem'] = "CD removido dos favoritos.";
    $favorito = false; // Variável para indicar que não é favoritado
} else {
    // Não favoritado, adicionar
    $stmt->close();
    $sql_insert = "INSERT INTO Favoritos (id_usuario, id_cd) VALUES (?, ?)";
    $stmt_ins = $conn->prepare($sql_insert);
    $stmt_ins->bind_param("ii", $id_usuario, $id_cd);
    $stmt_ins->execute();
    $stmt_ins->close();
    $_SESSION['mensagem'] = "CD adicionado aos favoritos.";
    $favorito = true; // Variável para indicar que é favoritado
}

$conn->close();
header("Location: " . $_SERVER['HTTP_REFERER']); // Retorna à página anterior
exit();
?>
