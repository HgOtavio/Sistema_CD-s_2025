<?php
include "../../../../login_cadastro/conexao.php";
session_start();

if (!isset($_POST['id_cd'])) {
    die("CD inválido.");
}

$id_cd = $_POST['id_cd'];
$titulo = $_POST['titulo'];
$disponibilidade = $_POST['disponibilidade'];
$preco = $_POST['preco'];
$destaque = $_POST['destaque'];
$anoLancamento = $_POST['anoLancamento'];
$genero = $_POST['genero'];
$descricao = $_POST['descricao'];

$caminhoBanco = null;

// Se uma nova capa foi enviada
if (isset($_FILES['nova_capa']) && $_FILES['nova_capa']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['nova_capa']['tmp_name'];
    $extensao = pathinfo($_FILES['nova_capa']['name'], PATHINFO_EXTENSION);
    $nomeArquivo = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", $genero)) . "_$id_cd." . $extensao;

    $caminhoFinal = "../../../../../img/imagens/" . $nomeArquivo;
    $caminhoBanco = "imagens/" . $nomeArquivo;

    if (!is_dir('img/imagens')) {
        mkdir('img/imagens', 0777, true);
    }

    if (!move_uploaded_file($tmp, $caminhoFinal)) {
        die("Erro ao salvar nova imagem.");
    }
}

// Atualizar dados do CD
if ($caminhoBanco) {
    $sql = "UPDATE CD SET titulo=?, capa=?, disponibilidade=?, preco=?, destaque=?, anoLancamento=?, genero=?, descricao=? WHERE id_cd=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdsissi", $titulo, $caminhoBanco, $disponibilidade, $preco, $destaque, $anoLancamento, $genero, $descricao, $id_cd);
} else {
    $sql = "UPDATE CD SET titulo=?, disponibilidade=?, preco=?, destaque=?, anoLancamento=?, genero=?, descricao=? WHERE id_cd=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsissi", $titulo, $disponibilidade, $preco, $destaque, $anoLancamento, $genero, $descricao, $id_cd);
}

if ($stmt->execute()) {
    // Atualizar artistas
    if (isset($_POST['artistas']) && is_array($_POST['artistas'])) {
        $conn->query("DELETE FROM CD_Artista WHERE id_cd = $id_cd");
        foreach ($_POST['artistas'] as $id_artista) {
            $conn->query("INSERT INTO CD_Artista (id_cd, id_artista) VALUES ($id_cd, $id_artista)");
        }
    }

    // Atualizar músicas
    if (isset($_POST['musicas']) && is_array($_POST['musicas'])) {
        $conn->query("DELETE FROM CD_Musica WHERE id_cd = $id_cd");
        foreach ($_POST['musicas'] as $id_musica) {
            $conn->query("INSERT INTO CD_Musica (id_cd, id_musica) VALUES ($id_cd, $id_musica)");
        }
    }

    echo "<script>alert('CD atualizado com sucesso!'); window.location.href='editar_cds.php?id_cd=$id_cd';</script>";
} else {
    echo "Erro ao atualizar CD: " . $stmt->error;
}

$conn->close();
?>
