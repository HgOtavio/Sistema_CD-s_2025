<?php
session_start();
include "../login_cadastro/conexao.php"; // Inclua a conexão com o banco de dados

if (!isset($_POST['id_cd']) || !isset($_POST['id_usuario'])) {
    echo json_encode(["error" => "ID do CD ou usuário não informado."]);
    exit;
}

$id_usuario = $_POST['id_usuario'];
$id_cd = $_POST['id_cd'];

// Verificar se o CD já está favoritado
$sql_verificar = "SELECT 1 FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
$stmt = $conn->prepare($sql_verificar);
$stmt->bind_param("ii", $id_usuario, $id_cd);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // CD já favoritado, então vamos remover
    $sql_delete = "DELETE FROM Favoritos WHERE id_usuario = ? AND id_cd = ?";
    $stmt_del = $conn->prepare($sql_delete);
    $stmt_del->bind_param("ii", $id_usuario, $id_cd);
    
    if ($stmt_del->execute()) {
        // Remoção bem-sucedida
        $favorito = false;
    } else {
        // Se houve erro ao remover
        echo json_encode(["error" => "Erro ao remover favorito."]);
        exit;
    }
    $stmt_del->close();
} else {
    // CD não favoritado, então vamos adicionar
    $sql_insert = "INSERT INTO Favoritos (id_usuario, id_cd) VALUES (?, ?)";
    $stmt_ins = $conn->prepare($sql_insert);
    $stmt_ins->bind_param("ii", $id_usuario, $id_cd);
    
    if ($stmt_ins->execute()) {
        // Adição bem-sucedida
        $favorito = true;
    } else {
        // Se houve erro ao adicionar
        echo json_encode(["error" => "Erro ao adicionar favorito."]);
        exit;
    }
    $stmt_ins->close();
}

$conn->close();

// Retorna a resposta em formato JSON
echo json_encode(["favoritado" => $favorito]);
?>
