<?php
session_start();
include "../../../../login_cadastro/conexao.php";

// Verifica se o usuário está logado e é do tipo administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../php/login.php");
    exit();
}

// Verificar se foi passado um ID
if (!isset($_POST['id_artista']) || !is_numeric($_POST['id_artista'])) {
    die("ID do artista não informado ou inválido.");
}

$id_artista = intval($_POST['id_artista']);

// Se enviou o formulário (alteração)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeArtista = $_POST['nomeArtista'];
    $dataNascimento = DateTime::createFromFormat('m/d/Y', $_POST['dataNascimento'])->format('Y-m-d');
    $descricao = $_POST['descricao'];
    $cdsSelecionados = isset($_POST['cds']) ? $_POST['cds'] : [];

    // Atualizar dados do artista
    $sql_update = "UPDATE Artista SET nomeArtista = ?, dataNascimento = ?, descricao = ? WHERE id_artista = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssi", $nomeArtista, $dataNascimento, $descricao, $id_artista);

    if ($stmt_update->execute()) {
        // Se houver foto, processar o upload
        if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] == 0) {
            $fotoNome = $_FILES['fotoPerfil']['name'];
            $fotoTemp = $_FILES['fotoPerfil']['tmp_name'];
            $fotoCaminho = "../Artista/" . $fotoNome;

            // Move a foto para o diretório
            if (move_uploaded_file($fotoTemp, $fotoCaminho)) {
                // Atualizar o nome da foto no banco
                $sql_foto_update = "UPDATE Artista SET fotoPerfil = ? WHERE id_artista = ?";
                $stmt_foto_update = $conn->prepare($sql_foto_update);
                $stmt_foto_update->bind_param("si", $fotoNome, $id_artista);
                $stmt_foto_update->execute();
            }
        }

        // Deletar as relações anteriores de CDs associados
        $conn->query("DELETE FROM CD_Artista WHERE id_artista = $id_artista");

        // Inserir novos CDs associados
        foreach ($cdsSelecionados as $id_cd) {
            $stmt_insert_cd = $conn->prepare("INSERT INTO CD_Artista (id_artista, id_cd) VALUES (?, ?)");
            $stmt_insert_cd->bind_param("ii", $id_artista, $id_cd);
            $stmt_insert_cd->execute();
        }

        // Redireciona com sucesso
        header("Location: editar_artistas.php?id_artista=$id_artista&success=1");
        exit();
    } else {
        // Se houver falha na execução do update
        die("Erro ao atualizar os dados do artista.");
    }
}
?>
