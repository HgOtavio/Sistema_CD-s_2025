<?php
include "../../../../login_cadastro/conexao.php";
session_start();

if (!isset($_POST['id_musica'])) {
    die("ID da música não fornecido.");
}

$id_musica = $_POST['id_musica'];
$nomeMusica = $_POST['nomeMusica'];
$tempo = $_POST['tempo'];

$audioPath = null;

// Se o usuário enviar um novo áudio
if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['audio']['tmp_name'];
    $extensao = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION);

    // Força extensão .mp3
    $nomeArquivo = $id_musica . ".mp3";

    $pastaDestino = "../../../../../audio/";
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0777, true);
    }

    $caminhoCompleto = $pastaDestino . $nomeArquivo;
    $audioPath = $caminhoCompleto;

    if (!move_uploaded_file($tmp, $caminhoCompleto)) {
        die("Erro ao salvar o novo arquivo de áudio.");
    }
}

// Atualiza os dados da música
if ($audioPath) {
    $stmt = $conn->prepare("UPDATE Musica SET nomeMusica=?, tempo=?, audio=? WHERE id_musica=?");
    $stmt->bind_param("sssi", $nomeMusica, $tempo, $audioPath, $id_musica);
} else {
    $stmt = $conn->prepare("UPDATE Musica SET nomeMusica=?, tempo=? WHERE id_musica=?");
    $stmt->bind_param("ssi", $nomeMusica, $tempo, $id_musica);
}

if (!$stmt->execute()) {
    die("Erro ao atualizar música: " . $stmt->error);
}

// Atualiza os CDs associados
if (isset($_POST['cdsSelecionados']) && is_array($_POST['cdsSelecionados'])) {
    $conn->query("DELETE FROM CD_Musica WHERE id_musica = $id_musica");

    foreach ($_POST['cdsSelecionados'] as $id_cd) {
        $conn->query("INSERT INTO CD_Musica (id_cd, id_musica) VALUES ($id_cd, $id_musica)");
    }
}

echo "<script>alert('Música atualizada com sucesso!'); window.location.href='lista_musicas.php';</script>";
$conn->close();
?>
