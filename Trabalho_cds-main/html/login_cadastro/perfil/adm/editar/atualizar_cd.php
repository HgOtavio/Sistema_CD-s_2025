<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtendo os dados enviados pelo formulário
    $id_cd = $_POST['id_cd'];
    $titulo = $_POST['titulo'];
    $disponibilidade = $_POST['disponibilidade'];
    $preco = $_POST['preco'];
    $destaque = $_POST['destaque'];
    $anoLancamento = $_POST['anoLancamento'];
    $genero = $_POST['genero'];
    $descricao = $_POST['descricao'];
    $artistasSelecionados = $_POST['artistasSelecionados']; // Array com os artistas selecionados
    $musicasSelecionadas = $_POST['musicasSelecionadas']; // Array com as músicas selecionadas

    // Atualizando os dados do CD
    $sql_atualizar_cd = "UPDATE CD SET titulo = ?, disponibilidade = ?, preco = ?, destaque = ?, anoLancamento = ?, genero = ?, descricao = ? WHERE id_cd = ?";
    $stmt_atualizar_cd = $conn->prepare($sql_atualizar_cd);
    $stmt_atualizar_cd->bind_param("ssdsdssi", $titulo, $disponibilidade, $preco, $destaque, $anoLancamento, $genero, $descricao, $id_cd);
    $stmt_atualizar_cd->execute();

    // Atualizando os artistas associados
    $sql_deletar_artistas = "DELETE FROM CD_Artista WHERE id_cd = ?";
    $stmt_deletar_artistas = $conn->prepare($sql_deletar_artistas);
    $stmt_deletar_artistas->bind_param("i", $id_cd);
    $stmt_deletar_artistas->execute();

    foreach ($artistasSelecionados as $id_artista) {
        $sql_associar_artista = "INSERT INTO CD_Artista (id_cd, id_artista) VALUES (?, ?)";
        $stmt_associar_artista = $conn->prepare($sql_associar_artista);
        $stmt_associar_artista->bind_param("ii", $id_cd, $id_artista);
        $stmt_associar_artista->execute();
    }

    // Atualizando as músicas associadas
    $sql_deletar_musicas = "DELETE FROM CD_Musica WHERE id_cd = ?";
    $stmt_deletar_musicas = $conn->prepare($sql_deletar_musicas);
    $stmt_deletar_musicas->bind_param("i", $id_cd);
    $stmt_deletar_musicas->execute();

    foreach ($musicasSelecionadas as $id_musica) {
        $sql_associar_musica = "INSERT INTO CD_Musica (id_cd, id_musica) VALUES (?, ?)";
        $stmt_associar_musica = $conn->prepare($sql_associar_musica);
        $stmt_associar_musica->bind_param("ii", $id_cd, $id_musica);
        $stmt_associar_musica->execute();
    }

    // Redirecionar após o sucesso
    header("Location:../editar/editar_cds.php?id_cd=" . $id_cd);
    exit();
}
?>

