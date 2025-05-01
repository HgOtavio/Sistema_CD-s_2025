<?php
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeArtista = $_POST["nomeArtista"];
    $descricao = $_POST["descricao"];
    $dataNascimento = $_POST["dataNascimento"];
    $cdsSelecionados = explode(",", $_POST["cdsSelecionados"]);
    $fotoPerfil = $_FILES["fotoPerfil"];

    // Verifica se o artista já existe
    $stmt = $conn->prepare("SELECT id_artista FROM Artista WHERE nomeArtista = ?");
    $stmt->bind_param("s", $nomeArtista);
    $stmt->execute();
    $result = $stmt->get_result();
    $id_artista = null;

    if ($result->num_rows > 0) {
        $id_artista = $result->fetch_assoc()["id_artista"];
    } else {
        $stmt = $conn->prepare("INSERT INTO Artista (nomeArtista, descricao, dataNascimento) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nomeArtista, $descricao, $dataNascimento);
        $stmt->execute();
        $id_artista = $stmt->insert_id;
    }

    // Upload da imagem
    if ($fotoPerfil['error'] == 0) {
        $ext = strtolower(pathinfo($fotoPerfil['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $nomeFoto = uniqid('artista_') . "." . $ext;
            $destino = "../Artista/" . $nomeFoto;
            move_uploaded_file($fotoPerfil['tmp_name'], $destino);

            $stmt = $conn->prepare("UPDATE Artista SET fotoPerfil = ? WHERE id_artista = ?");
            $stmt->bind_param("si", $nomeFoto, $id_artista);
            $stmt->execute();
        }
    }

    // Associar CDs
    if (!empty($cdsSelecionados)) {
        $stmt = $conn->prepare("INSERT INTO CD_Artista (id_cd, id_artista) VALUES (?, ?)");
        foreach ($cdsSelecionados as $id_cd) {
            if (is_numeric($id_cd)) {
                $stmt->bind_param("ii", $id_cd, $id_artista);
                $stmt->execute();
            }
        }
    }

    echo "<script>alert('Artista cadastrado com sucesso!'); window.location.href='add_artista.php.php';</script>";
}

$conn->close();
?>
