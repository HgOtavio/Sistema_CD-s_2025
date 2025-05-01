<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pega os dados do formulário
    $nomeMusica = $conn->real_escape_string($_POST['nomeMusica']);
    $tempo = (float) $_POST['tempo'];
    $cdsSelecionados = $_POST['id_cd']; // Vai ser um array com os CDs

    // Upload do arquivo de áudio (se existir)
    $caminhoAudio = null;
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] == UPLOAD_ERR_OK) {
        $pastaDestino = "uploads_audio/";
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }
        $nomeArquivo = uniqid() . "_" . basename($_FILES['audio']['name']);
        $caminhoCompleto = $pastaDestino . $nomeArquivo;

        if (move_uploaded_file($_FILES['audio']['tmp_name'], $caminhoCompleto)) {
            $caminhoAudio = $caminhoCompleto;
        } else {
            die("Erro ao enviar o arquivo de áudio.");
        }
    }

    // 1. Inserir a música na tabela Musica
    $sql_musica = "INSERT INTO Musica (nomeMusica, tempo, audio) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_musica);
    $stmt->bind_param("sds", $nomeMusica, $tempo, $caminhoAudio);
    if ($stmt->execute()) {
        // Pegamos o ID da música recém inserida
        $id_musica = $conn->insert_id;

        // 2. Inserir relações na tabela CD_Musica
        foreach ($cdsSelecionados as $id_cd) {
            $sql_relacao = "INSERT INTO CD_Musica (id_cd, id_musica) VALUES (?, ?)";
            $stmt_relacao = $conn->prepare($sql_relacao);
            $stmt_relacao->bind_param("ii", $id_cd, $id_musica);
            $stmt_relacao->execute();
            $stmt_relacao->close();
        }

        echo "Música adicionada com sucesso e associada aos CDs!";
    } else {
        echo "Erro ao adicionar música: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Formulário não enviado corretamente.";
}

$conn->close();
?>
