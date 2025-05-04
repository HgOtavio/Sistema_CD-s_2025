<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário é admin
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Verifica se recebeu os IDs para exclusão
if (isset($_POST['excluir']) && is_array($_POST['excluir'])) {
    $ids_para_excluir = $_POST['excluir'];

    // Prepara a SQL para exclusão em lote
    $placeholders = implode(',', array_fill(0, count($ids_para_excluir), '?'));
    $stmt = $conn->prepare("DELETE FROM Usuario WHERE id_usuario IN ($placeholders)");

    // Adiciona os tipos dinamicamente (todos inteiros)
    $types = str_repeat('i', count($ids_para_excluir));
    $stmt->bind_param($types, ...$ids_para_excluir);

    if ($stmt->execute()) {
        // Redireciona de volta após a exclusão
        header("Location: gerenciar_user.php?sucesso=1");
        exit();
    } else {
        echo "Erro ao excluir usuários: " . $conn->error;
    }
} else {
    echo "Nenhum usuário selecionado para exclusão.";
}
?>
