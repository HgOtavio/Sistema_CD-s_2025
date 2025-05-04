<?php
$conn = new mysqli('localhost', 'root', '', 'LojaCDs');
if ($conn->connect_error) {
    die('Erro de conexão: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['desconto'])) {
        // Aplica o desconto
        $id_cd = intval($_POST['id_cd']);
        $desconto = intval($_POST['desconto']);

        if ($desconto < 0 || $desconto > 100) {
            die('Desconto inválido.');
        }

        // Verifica se já existe um desconto para o CD
        $sql_verifica = 'SELECT * FROM Promocao WHERE id_cd = ' . $id_cd;
        $result_verifica = $conn->query($sql_verifica);

        if ($result_verifica->num_rows > 0) {
            // Atualiza o desconto existente
            $sql_update = 'UPDATE Promocao SET desconto = ' . $desconto . ' WHERE id_cd = ' . $id_cd;
            $conn->query($sql_update);
        } else {
            // Insere um novo desconto
            $sql_insert = 'INSERT INTO Promocao (id_cd, desconto) VALUES (' . $id_cd . ', ' . $desconto . ')';
            $conn->query($sql_insert);
        }
    }

    if (isset($_POST['data_expiracao'])) {
        // Aplica a data de expiração
        $id_cd = intval($_POST['id_cd']);
        $data_expiracao = $_POST['data_expiracao'];

        // Verifica se já existe um desconto para o CD
        $sql_verifica = 'SELECT * FROM Promocao WHERE id_cd = ' . $id_cd;
        $result_verifica = $conn->query($sql_verifica);

        if ($result_verifica->num_rows > 0) {
            // Atualiza a data de expiração
            $sql_update = 'UPDATE Promocao SET data_expiracao = \'' . $data_expiracao . '\' WHERE id_cd = ' . $id_cd;
            $conn->query($sql_update);
        } else {
            // Insere a data de expiração se não houver desconto
            $sql_insert = 'INSERT INTO Promocao (id_cd, data_expiracao) VALUES (' . $id_cd . ', \'' . $data_expiracao . '\')';
            $conn->query($sql_insert);
        }
    }

    header('Location: gerenciar_cds.php');
    exit();
}

$conn->close();
?>
