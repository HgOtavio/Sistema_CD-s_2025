<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// 1. Receber dados do formulário
$id_musica = isset($_POST['id_musica']) ? intval($_POST['id_musica']) : 0;
$nomeMusica = $_POST['nomeMusica'] ?? '';
$tempo = $_POST['tempo'] ?? '';
$cdsSelecionados = $_POST['cdsSelecionados'] ?? [];

// 2. Atualizar dados da música
$sql_update = "UPDATE Musica SET nomeMusica = ?, tempo = ? WHERE id_musica = ?";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("ssi", $nomeMusica, $tempo, $id_musica);
$stmt->execute();

// 3. Atualizar CDs associados à música
// Remove todos os registros antigos
$conn->query("DELETE FROM CD_Musica WHERE id_musica = $id_musica");

// Adiciona os novos CDs selecionados
if (!empty($cdsSelecionados)) {
    $stmt_cd = $conn->prepare("INSERT INTO CD_Musica (id_cd, id_musica) VALUES (?, ?)");
    foreach ($cdsSelecionados as $id_cd) {
        $id_cd = intval($id_cd);
        $stmt_cd->bind_param("ii", $id_cd, $id_musica);
        $stmt_cd->execute();
    }
}

// 4. Verifica se foi enviado um novo arquivo de áudio
if (isset($_FILES['audio']) && $_FILES['audio']['error'] === 0) {
    $pasta_destino = "../../../../../audio/";
    $nome_arquivo = $id_musica . ".mp3";
    $caminho_completo = $pasta_destino . $nome_arquivo;

    // Move o novo arquivo para a pasta
    move_uploaded_file($_FILES['audio']['tmp_name'], $caminho_completo);
}

// 5. Redireciona ou exibe mensagem
header("Location: ../musicas.php?sucesso=1");
exit;
?>
