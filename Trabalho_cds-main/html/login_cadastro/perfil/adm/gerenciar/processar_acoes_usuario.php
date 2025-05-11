<?php
session_start();
include "../../../../login_cadastro/conexao.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

$id_usuario = $_GET['id'] ?? null; // Garantir que id_usuario é inicializado
$acao = $_POST['acao'] ?? null;

if (isset($_POST['acao']) && $_POST['acao'] == 'remover_itens_selecionados') {
    // Cancelar ou reativar compras selecionadas
    if (isset($_POST['compras_cancelar'])) {
        foreach ($_POST['compras_cancelar'] as $id_compra) {
            // Verificar o status e atualizar diretamente
            $sql_update = "UPDATE Compra SET status = CASE WHEN status = 'ativa' THEN 'Cancelada' ELSE 'Ativa' END WHERE id_compra = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $id_compra);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }

    // Remover itens do carrinho selecionados
    if (isset($_POST['cds_carrinho'])) {
        foreach ($_POST['cds_carrinho'] as $id_cd) {
            $sql = "DELETE FROM Carrinho WHERE id_cd = ? AND id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_cd, $id_usuario);
            if (!$stmt->execute()) {
                // Adicionar verificação de erro
                echo "Erro ao remover item do carrinho.";
            }
            $stmt->close();
        }
    }

    // Remover favoritos selecionados
    if (isset($_POST['cds_favoritos'])) {
        foreach ($_POST['cds_favoritos'] as $id_cd) {
            $sql = "DELETE FROM Favoritos WHERE id_cd = ? AND id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_cd, $id_usuario);
            if (!$stmt->execute()) {
                // Adicionar verificação de erro
                echo "Erro ao remover item dos favoritos.";
            }
            $stmt->close();
        }
    }

    // Redirecionar ou mostrar mensagem de sucesso
    header("Location: gerenciar_atividades.php?id=". $id_usuario);
    exit();
}
