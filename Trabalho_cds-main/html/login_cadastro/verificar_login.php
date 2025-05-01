<?php
include "conexao.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST["login"];
    $senha_inserida = $_POST["senha"];

    // Consulta para buscar o usuário e a senha criptografada
    $sql = "SELECT * FROM Usuario WHERE login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $senha_armazenada = $usuario["senha"];
        $tipo_usuario = $usuario["tipo"];

        $senha_correta = false;

        // Tenta usar password_verify para verificar a senha, se falhar, tenta com MD5
        if ($tipo_usuario == "admin") {
            // Se for admin, tenta comparar com MD5
            if (md5($senha_inserida) == $senha_armazenada) {
                $senha_correta = true;
            }
        } elseif ($tipo_usuario == "cliente") {
            // Se for cliente, tenta comparar com password_verify
            if (password_verify($senha_inserida, $senha_armazenada)) {
                $senha_correta = true;
            } elseif (md5($senha_inserida) == $senha_armazenada) {
                // Caso falhe o password_verify, tenta comparar com MD5
                $senha_correta = true;
            }
        }

        if ($senha_correta) {
            $_SESSION["id_usuario"] = $usuario["id_usuario"];
            $_SESSION["tipo"] = $tipo_usuario;

            // Redireciona conforme o tipo de usuário
            if ($tipo_usuario == "admin") {
                header("Location: ../pagina_inicial/index_logado.php");
            } else {
                header("Location: ../pagina_inicial/index_logado.php");
            }
            exit();
        } else {
            // Redireciona para login.php se a senha ou login estiverem incorretos
            header("Location: login.php?erro=1");
            exit();
        }
    } else {
        // Redireciona para login.php se o usuário não for encontrado
        header("Location: login.php?erro=1");
        exit();
    }
}
?>