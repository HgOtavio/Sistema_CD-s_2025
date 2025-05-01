<?php
session_start();
include "../../login_cadastro/conexao.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_POST["id_usuario"];
    $nome_completo = $_POST["nome_completo"];
    $email = $_POST["email"];
    $cpf = $_POST["cpf"];
    $telefone = $_POST["telefone"];
    $login = $_POST["login"];
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];
    $cep = $_POST["cep"];
    $estado = $_POST["estado"];
    $cidade = $_POST["cidade"];
    $bairro = $_POST["bairro"];
    $logradouro = $_POST["logradouro"];
    $numero = $_POST["numero"];
    $complemento = $_POST["complemento"];

    // Validação de senha
    if (!empty($senha)) {
        if ($senha !== $confirmar_senha) {
            echo "As senhas não coincidem.";
            exit();
        }
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    }

    // Verifica se foi enviada uma nova foto
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $foto_nome = $_FILES['foto_perfil']['name'];
        $foto_tmp = $_FILES['foto_perfil']['tmp_name'];

        $caminho_destino = "uploads/" . uniqid() . "_" . basename($foto_nome);
        move_uploaded_file($foto_tmp, $caminho_destino);
    } else {
        // Se não houver nova foto, mantemos a antiga
        $stmt = $conn->prepare("SELECT foto_perfil FROM Usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $caminho_destino = $usuario['foto_perfil'];
        $stmt->close();
    }

    // Atualização no banco
    if (!empty($senha)) {
        $stmt = $conn->prepare("UPDATE Usuario SET nome_completo=?, email=?, cpf=?, telefone=?, login=?, senha=?, cep=?, estado=?, cidade=?, bairro=?, logradouro=?, numero=?, complemento=?, foto_perfil=? WHERE id_usuario=?");
        $stmt->bind_param("sssssssssssssssi", $nome_completo, $email, $cpf, $telefone, $login, $senha_hash, $cep, $estado, $cidade, $bairro, $logradouro, $numero, $complemento, $caminho_destino, $id_usuario);
    } else {
        $stmt = $conn->prepare("UPDATE Usuario SET nome_completo=?, email=?, cpf=?, telefone=?, login=?, cep=?, estado=?, cidade=?, bairro=?, logradouro=?, numero=?, complemento=?, foto_perfil=? WHERE id_usuario=?");
        $stmt->bind_param("sssssssssssssi", $nome_completo, $email, $cpf, $telefone, $login, $cep, $estado, $cidade, $bairro, $logradouro, $numero, $complemento, $caminho_destino, $id_usuario);
    }

    if ($stmt->execute()) {
        header("Location: alterar_dados.php?msg=sucesso");
    } else {
        echo "Erro ao atualizar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
