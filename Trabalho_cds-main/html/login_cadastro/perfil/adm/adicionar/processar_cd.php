<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Verifica se todos os campos foram enviados
if (
    isset($_POST['titulo']) && isset($_POST['disponibilidade']) && isset($_POST['preco']) &&
    isset($_POST['destaque']) && isset($_POST['anoLancamento']) && isset($_POST['genero']) &&
    isset($_POST['descricao']) && isset($_POST['artistas']) && isset($_POST['musicas'])
) {
    // Pega os dados
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $disponibilidade = $conn->real_escape_string($_POST['disponibilidade']);
    
    // Ajusta o preço
    $preco_br = $_POST['preco']; // Preço enviado pelo formulário
    // Remove 'R$' e substitui vírgula por ponto
    $preco = floatval(str_replace(',', '.', str_replace('R$', '', $preco_br)));

    $destaque = $conn->real_escape_string($_POST['destaque']);
    $anoLancamento = intval($_POST['anoLancamento']);
    $genero = $conn->real_escape_string($_POST['genero']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $artistas = $_POST['artistas'];
    $musicas = $_POST['musicas'];

    // Trata a imagem (opcional)
    $capa_nome = NULL;
    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $capa_nome = $_FILES['capa']['name'];
        $capa_tmp = $_FILES['capa']['tmp_name'];

        $diretorio_upload = '../../../../../img/imagens'; // Pasta para armazenar as imagens
        if (!is_dir($diretorio_upload)) {
            mkdir($diretorio_upload, 0777, true); // Cria a pasta se não existir
        }
        $caminho_final = $diretorio_upload . '/' . basename($capa_nome);

        if (!move_uploaded_file($capa_tmp, $caminho_final)) {
            echo "Aviso: Não foi possível salvar a capa. Continuando sem imagem...<br>";
            $capa_nome = NULL;
        }
    }

    // Prepara o campo de capa para o banco
    $capa_valor = $capa_nome ? "'imagens/" . $capa_nome . "'" : "NULL";

    // Insere o CD
    $sql_cd = "INSERT INTO CD (titulo, capa, disponibilidade, preco, destaque, anoLancamento, genero, descricao) 
               VALUES ('$titulo', $capa_valor, '$disponibilidade', $preco, '$destaque', $anoLancamento, '$genero', '$descricao')";

    if ($conn->query($sql_cd) === TRUE) {
        $id_cd = $conn->insert_id; // Pega o id do CD recém inserido

        // Liga o CD aos artistas
        foreach ($artistas as $id_artista) {
            $id_artista = intval($id_artista);
            $conn->query("INSERT INTO CD_Artista (id_cd, id_artista) VALUES ($id_cd, $id_artista)");
        }

        // Liga o CD às músicas
        foreach ($musicas as $id_musica) {
            $id_musica = intval($id_musica);
            $conn->query("INSERT INTO CD_Musica (id_cd, id_musica) VALUES ($id_cd, $id_musica)");
        }

        echo "✅ CD adicionado com sucesso!";
    } else {
        echo "❌ Erro ao adicionar CD: " . $conn->error;
    }

} else {
    echo "❌ Erro: Campos obrigatórios não foram enviados.";
}

$conn->close();
?>
