<?php
include "../../../../login_cadastro/conexao.php";
session_start();

if (!isset($_POST['id_artista'])) {
    die("ID do artista não fornecido.");
}

$id_artista = $_POST['id_artista'];
$nomeArtista = trim($_POST['nomeArtista']);
$dataNascimento = date('Y-m-d', strtotime($_POST['dataNascimento']));
$descricao = $_POST['descricao'];
$caminhoImagem = null;

// Upload da nova foto (se enviada)
if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['fotoPerfil']['tmp_name'];
    $extensao = strtolower(pathinfo($_FILES['fotoPerfil']['name'], PATHINFO_EXTENSION));

    // Formata o nome do artista (sem espaços ou acentos)
    $nomeFormatado = preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nomeArtista));

    // Caminho a ser salvo no banco
    $caminhoImagem = 'Artista/' . $nomeFormatado . '.' . $extensao;

    // Caminho real para salvar o arquivo
    $caminhoPasta = '../../../../../img/Artista/';
    if (!is_dir($caminhoPasta)) {
        mkdir($caminhoPasta, 0777, true);
    }

    // Move o arquivo
    $destino = $caminhoPasta . $nomeFormatado . '.' . $extensao;
    if (!move_uploaded_file($tmp, $destino)) {
        die("Erro ao salvar a imagem.");
    }
}

// Atualiza os dados do artista
if ($caminhoImagem) {
    $sql = "UPDATE Artista SET nomeArtista=?, dataNascimento=?, descricao=?, fotoPerfil=? WHERE id_artista=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nomeArtista, $dataNascimento, $descricao, $caminhoImagem, $id_artista);
} else {
    $sql = "UPDATE Artista SET nomeArtista=?, dataNascimento=?, descricao=? WHERE id_artista=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nomeArtista, $dataNascimento, $descricao, $id_artista);
}

if (!$stmt->execute()) {
    die("Erro ao atualizar artista: " . $stmt->error);
}

// Atualiza CDs associados
if (isset($_POST['cds']) && is_array($_POST['cds'])) {
    $conn->query("DELETE FROM CD_Artista WHERE id_artista = $id_artista");
    foreach ($_POST['cds'] as $id_cd) {
        $conn->query("INSERT INTO CD_Artista (id_cd, id_artista) VALUES ($id_cd, $id_artista)");
    }
}

echo "<script>alert('Artista atualizado com sucesso!'); window.location.href='listar_artistas.php';</script>";
$conn->close();
?>
