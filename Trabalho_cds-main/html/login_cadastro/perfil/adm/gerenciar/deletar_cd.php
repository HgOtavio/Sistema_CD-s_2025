<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Verifica se os IDs dos CDs foram enviados
if (isset($_POST['cd_selecionadas']) && is_array($_POST['cd_selecionadas'])) {
    $ids = $_POST['cd_selecionadas'];

    foreach ($ids as $id_cd) {
        $id_cd = intval($id_cd); // segurança

        // Exclui os relacionamentos nas tabelas intermediárias
        $conn->query("DELETE FROM CD_Artista WHERE id_cd = $id_cd");
        $conn->query("DELETE FROM CD_Musica WHERE id_cd = $id_cd");
        $conn->query("DELETE FROM Promocao WHERE id_cd = $id_cd");

        // Exclui o próprio CD
        $conn->query("DELETE FROM CD WHERE id_cd = $id_cd");
    }

    header("Location: gerenciar_cds.php?msg=excluido");
    exit();
} else {
    header("Location: gerenciar_cds.php?msg=nenhum_selecionado");
    exit();
}
