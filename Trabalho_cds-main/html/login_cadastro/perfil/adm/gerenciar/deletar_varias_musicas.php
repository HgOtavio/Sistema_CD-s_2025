<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário é admin
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../../login.php");
    exit();
}

// Verifica se músicas foram selecionadas
if (isset($_POST['musicas_selecionadas']) && is_array($_POST['musicas_selecionadas'])) {
    $musicas = $_POST['musicas_selecionadas'];

    // Prepara a exclusão para cada música
    foreach ($musicas as $id_musica) {
        // Deleta da tabela associativa CD_Musica primeiro (evita erro de chave estrangeira)
        $stmt1 = $conn->prepare("DELETE FROM CD_Musica WHERE id_musica = ?");
        $stmt1->bind_param("i", $id_musica);
        $stmt1->execute();
        $stmt1->close();

        // Deleta da tabela Musica
        $stmt2 = $conn->prepare("DELETE FROM Musica WHERE id_musica = ?");
        $stmt2->bind_param("i", $id_musica);
        $stmt2->execute();
        $stmt2->close();

        // Deleta o arquivo de áudio (caso exista)
        $audio_path = "../audio/" . $id_musica . ".mp3";
        if (file_exists($audio_path)) {
            unlink($audio_path);
        }
    }

    // Redireciona de volta com sucesso
    header("Location: gerenciar_musicas.php?msg=sucesso");
    exit();
} else {
    // Nenhuma música selecionada
    header("Location: gerenciar_musicas.php?msg=nenhuma_selecionada");
    exit();
}
?>
