<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_musica = intval($_POST['id_musica']);
    $nomeMusica = $_POST['nomeMusica'];
    $tempo = $_POST['tempo'];
    $cdsSelecionados = isset($_POST['cdsSelecionados']) ? $_POST['cdsSelecionados'] : [];

    // Atualizar dados da música
    $sql_update = "UPDATE Musica SET nomeMusica = ?, tempo = ? WHERE id_musica = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssi", $nomeMusica, $tempo, $id_musica);
    $stmt->execute();

    // Upload de novo áudio (se enviado)
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] == 0) {
        $arquivo = $_FILES['audio'];
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . "." . $extensao;
        $caminho = "uploads/" . $nomeArquivo;

        // Validar tipos permitidos
        $tipos_permitidos = ['mp3', 'wav'];
        if (in_array(strtolower($extensao), $tipos_permitidos)) {
            move_uploaded_file($arquivo['tmp_name'], $caminho);

            // Atualizar campo de áudio
            $sql_audio = "UPDATE Musica SET audio = ? WHERE id_musica = ?";
            $stmt = $conn->prepare($sql_audio);
            $stmt->bind_param("si", $nomeArquivo, $id_musica);
            $stmt->execute();
        } else {
            echo "Tipo de arquivo inválido! Permitido: MP3 ou WAV.";
            exit;
        }
    }

    // Atualizar CDs associados:
    // Remove associações antigas
    $sql_delete_cds = "DELETE FROM CD_Musica WHERE id_musica = ?";
    $stmt = $conn->prepare($sql_delete_cds);
    $stmt->bind_param("i", $id_musica);
    $stmt->execute();

    // Inserir novas associações
    foreach ($cdsSelecionados as $id_cd) {
        $sql_insert_cd = "INSERT INTO CD_Musica (id_musica, id_cd) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_insert_cd);
        $stmt->bind_param("ii", $id_musica, $id_cd);
        $stmt->execute();
    }

    // Redirecionar após atualização
    header("Location: editar_musicas.php?atualizado=1");
    exit();
} else {
    echo "Requisição inválida.";
}

$conn->close();
?>
