<?php
session_start();
include "../login_cadastro/conexao.php";

if (!isset($_SESSION['id_usuario'])) {
    die("Acesso negado.");
}

$id_usuario = $_SESSION["id_usuario"];
$id_cd = !empty($_POST["id_cd"]) ? $_POST["id_cd"] : null;
$nota = $_POST["nota"];
$comentario = $_POST["comentario"];

$sql = "INSERT INTO avaliacao (nota, comentario, id_usuario, id_cd)
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isii", $nota, $comentario, $id_usuario, $id_cd);
$stmt->execute();

if ($id_cd) {
    header("Location: produto.php?id_cd=$id_cd");
} else {
    header("Location: index.php");
}
exit();
