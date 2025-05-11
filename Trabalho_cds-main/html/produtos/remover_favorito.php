<?php
session_start();
include "../php/conexao.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

if (isset($_POST['cds_favoritos']) && is_array($_POST['cds_favoritos'])) {
    $ids = $_POST['cds_favoritos'];
    $id_usuario = $_GET['id'] ?? null;

    if ($id_usuario) {
        $sql = "DELETE FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
        $stmt = $conn->prepare($sql);

        foreach ($ids as $id_cd) {
            $stmt->bind_param("ii", $id_usuario, $id_cd);
            $stmt->execute();
        }

        $stmt->close();
    }
}

header("Location: {$_SERVER['HTTP_REFERER']}");
exit();
