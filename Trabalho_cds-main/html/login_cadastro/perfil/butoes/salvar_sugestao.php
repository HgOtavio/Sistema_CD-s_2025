<?php
session_start();
include "../../../login_cadastro/conexao.php";

$id_usuario = $_SESSION['id_usuario'] ?? 1;

$tipo = $_POST['tipo'];
$genero = $_POST['genero'] ?? null;
$descricao = $_POST['descricao'] ?? null;
$status = 'Pendente';

$titulo = null;
$anoLancamento = null;
$preco = null;
$capa = null;

// Função para limpar nomes (sem acento, sem espaços, tudo minúsculo)
function limparNome($str) {
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $str = preg_replace('/[^a-zA-Z0-9]/', '', $str);
    return strtolower($str);
}

// Salva imagem de CD ou Artista
function salvarImagem($arquivo, $tipo, $referencia) {
    if ($tipo === 'cd') {
        $pastaFisica = '../../../../img/imagens/';
        $caminhoBancoBase = 'imagens/';
        $nomeArquivo = limparNome($referencia) . '_' . uniqid();
    } elseif ($tipo === 'Artista') {
        $pastaFisica = '../../../../img/Artista/';
        $caminhoBancoBase = 'Artista/';
        $nomeArquivo = limparNome($referencia);
    } else {
        return null;
    }

    if (!is_dir($pastaFisica)) {
        mkdir($pastaFisica, 0777, true);
    }

    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeFinal = $nomeArquivo . '.' . $extensao;
    $caminhoFisico = $pastaFisica . $nomeFinal;
    $caminhoBanco = $caminhoBancoBase . $nomeFinal;

    if (move_uploaded_file($arquivo['tmp_name'], $caminhoFisico)) {
        return $caminhoBanco;
    }

    return null;
}

// Salva áudio da música
function salvarAudio($arquivo, $tituloMusica, $id_usuario) {
    $pastaFisica = '../../../../audio/';
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeBase = $id_usuario ;
    $nomeFinal = $nomeBase . '.' . $extensao;
    $caminhoFisico = $pastaFisica . $nomeFinal;
    $caminhoBanco = 'audio/' . $nomeFinal;

    if (!is_dir($pastaFisica)) {
        mkdir($pastaFisica, 0777, true);
    }

    if (move_uploaded_file($arquivo['tmp_name'], $caminhoFisico)) {
        return $caminhoBanco;
    }

    return null;
}

// CD
if ($tipo === 'cd') {
    $titulo = $_POST['titulo_cd'];
    $anoLancamento = $_POST['ano_cd'];
    $preco = $_POST['preco_cd'];

    if (isset($_FILES['capa_cd']) && $_FILES['capa_cd']['error'] === UPLOAD_ERR_OK) {
        $capa = salvarImagem($_FILES['capa_cd'], 'CD', $genero);
    }

// MÚSICA
} elseif ($tipo === 'musica') {
    $titulo = $_POST['nome_musica'];
    $tempo = $_POST['tempo_musica'];

    if (isset($_FILES['audio_musica']) && $_FILES['audio_musica']['error'] === UPLOAD_ERR_OK) {
        $capa = salvarAudio($_FILES['audio_musica'], $titulo, $id_usuario);
    }

// ARTISTA
} elseif ($tipo === 'artista') {
    $titulo = $_POST['nome_artista'];
    $dataNascimento = $_POST['data_nascimento'];

    if (isset($_FILES['foto_artista']) && $_FILES['foto_artista']['error'] === UPLOAD_ERR_OK) {
        $capa = salvarImagem($_FILES['foto_artista'], 'Artista', $titulo);
    }
}

// INSERE NA TABELA SUGESTOES
$sql = "INSERT INTO Sugestoes 
        (id_usuario, titulo, genero, anoLancamento, preco, descricao, capa, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssdsss", $id_usuario, $titulo, $genero, $anoLancamento, $preco, $descricao, $capa, $status);

if ($stmt->execute()) {
    echo "<script>alert('Sugestão enviada com sucesso!'); window.location.href='sugestao.php';</script>";
} else {
    echo "Erro ao salvar sugestão: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
