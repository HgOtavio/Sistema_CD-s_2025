<?php
session_start();
include "../../login_cadastro/conexao.php";

$carrinho = $_SESSION['carrinho'] ?? [];
if (empty($carrinho)) {
    echo "Carrinho vazio.";
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    echo "Usuário não logado.";
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$totalCompra = 0;

// Calcula total com descontos
foreach ($carrinho as $id_cd => $quantidade) {
    $sql = "SELECT preco FROM CD WHERE id_cd = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Erro prepare preco: " . $conn->error); }
    $stmt->bind_param("i", $id_cd);
    $stmt->execute();
    $stmt->bind_result($preco);
    $stmt->fetch();
    $stmt->close();

    $desconto = 0;
    $sql = "SELECT desconto FROM Promocao WHERE id_cd = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Erro prepare desconto: " . $conn->error); }
    $stmt->bind_param("i", $id_cd);
    $stmt->execute();
    $stmt->bind_result($desconto);
    $stmt->fetch();
    $stmt->close();

    $precoComDesconto = $preco - ($preco * ($desconto / 100));
    $totalCompra += $precoComDesconto * $quantidade;
}

// Dados do formulário
$forma_pagamento   = $_POST['forma_pagamento'] ?? '';
$tipo_pagamento    = $_POST['tipo_pagamento'] ?? '';
$tipo_cartao       = $_POST['tipo_cartao'] ?? '';
$tipo_envio        = $_POST['tipo_envio'] ?? '';
$endereco_entrega  = $_POST['enderecoEntrega'] ?? '';
$cep               = $_POST['cep_entrega'] ?? '';

$num_cartao        = $_POST['num_cartao'] ?? '';
$nome_cartao       = $_POST['nome_cartao'] ?? '';
$validade_cartao   = $_POST['validade_cartao'] ?? '';
$cvv_cartao        = $_POST['cvv_cartao'] ?? '';

// Ajusta variáveis que podem ser nulas para string vazia
$cep              = $cep ?: '';
$num_cartao       = $num_cartao ?: '';
$nome_cartao      = $nome_cartao ?: '';
$validade_cartao  = $validade_cartao ?: '';
$cvv_cartao       = $cvv_cartao ?: '';
$tipo_cartao      = $tipo_cartao ?: '';

// Validação básica
if (!$forma_pagamento || !$tipo_pagamento || !$tipo_envio) {
    echo "Todos os campos obrigatórios devem ser preenchidos.";
    exit;
}

// Validação cartão
if ($tipo_pagamento === 'cartao') {
    if (!$tipo_cartao || !$num_cartao || !$nome_cartao || !$validade_cartao || !$cvv_cartao) {
        echo "Todos os dados do cartão são obrigatórios para pagamento com cartão.";
        exit;
    }
}

// Busca endereço no banco se não fornecido
if (empty($endereco_entrega) || empty($cep)) {
    $sql = "SELECT logradouro, numero, complemento, bairro, cidade, estado, pais, cep 
            FROM Usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Erro prepare endereço: " . $conn->error); }
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $stmt->bind_result($logradouro, $numero, $complemento, $bairro, $cidade, $estado, $pais, $cep_db);
    $stmt->fetch();
    $stmt->close();

    $endereco_entrega = "$logradouro, $numero";
    if (!empty($complemento)) $endereco_entrega .= " - $complemento";
    $endereco_entrega .= " - $bairro, $cidade - $estado, $pais";

    if (empty($cep)) {
        $cep = $cep_db;
    }
}

function calcularTaxaEntrega($tipoEnvio) {
    return ($tipoEnvio == "aéreo") ? 50.00 : 20.00;
}

function calcularEstimativaEntrega($tipoEnvio) {
    $data = new DateTime();
    $data->add(new DateInterval($tipoEnvio == "aéreo" ? 'P3D' : 'P10D'));
    return $data->format('d/m/Y');
}

$taxa_entrega = calcularTaxaEntrega($tipo_envio);
$estimativa_entrega = calcularEstimativaEntrega($tipo_envio);
$valor_total_final = $totalCompra + $taxa_entrega;

// Verifica estoque
foreach ($carrinho as $id_cd => $quantidade) {
    $sql = "SELECT disponibilidade FROM CD WHERE id_cd = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("Erro prepare estoque: " . $conn->error); }
    $stmt->bind_param("i", $id_cd);
    $stmt->execute();
    $stmt->bind_result($disponibilidadeAtual);
    $stmt->fetch();
    $stmt->close();

    if ($disponibilidadeAtual < $quantidade) {
        echo "Estoque insuficiente para o CD ID $id_cd.";
        exit;
    }
}

function gerarCodigoBoleto() {
    return strtoupper(bin2hex(random_bytes(10)));
}
function gerarCodigoPix() {
    return strtoupper(bin2hex(random_bytes(16)));
}

foreach ($carrinho as $id_cd => $quantidade) {
    $codigo_pix = null;
    $codigo_boleto = null;

    if ($tipo_pagamento === 'pix') {
        $codigo_pix = gerarCodigoPix();
    } elseif ($tipo_pagamento === 'boleto bancário') {
        $codigo_boleto = gerarCodigoBoleto();
    }

    $stmt = $conn->prepare(
        "INSERT INTO Compra (
            id_usuario, id_cd, forma_pagamento, tipo_pagamento, tipo_envio,
            enderecoEntrega, valorTotal, quantidade, estimativa_entrega,
            taxa_entrega, cep_entrega, status, codigo_pix, codigo_boleto,
            num_cartao, nome_cartao, validade_cartao, cvv_cartao, tipo_cartao, data_compra
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ativa', ?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    if (!$stmt) { die("Erro prepare insert compra: " . $conn->error); }

    $stmt->bind_param(
        "iissssdisdssssssss",
        $id_usuario,
        $id_cd,
        $forma_pagamento,
        $tipo_pagamento,
        $tipo_envio,
        $endereco_entrega,
        $valor_total_final,
        $quantidade,
        $estimativa_entrega,
        $taxa_entrega,
        $cep,
        $codigo_pix,
        $codigo_boleto,
        $num_cartao,
        $nome_cartao,
        $validade_cartao,
        $cvv_cartao,
        $tipo_cartao
    );

    if (!$stmt->execute()) {
        echo "Erro ao processar compra: " . $stmt->error;
        exit;
    }
    $stmt->close();

    $sql_update_cd = "UPDATE CD SET disponibilidade = disponibilidade - ?, numero_vendas = numero_vendas + ? WHERE id_cd = ?";
    $stmt_update = $conn->prepare($sql_update_cd);
    if (!$stmt_update) { die("Erro prepare update CD: " . $conn->error); }
    $stmt_update->bind_param("iii", $quantidade, $quantidade, $id_cd);
    if (!$stmt_update->execute()) {
        echo "Erro ao atualizar estoque/vendas: " . $stmt_update->error;
        exit;
    }
    $stmt_update->close();
}

// Salva última compra para confirmação
$_SESSION['compra'] = [
    'valor_total' => $valor_total_final,
    'cep' => $cep,
    'endereco_entrega' => $endereco_entrega,
    'taxa_entrega' => $taxa_entrega,
    'estimativa_entrega' => $estimativa_entrega,
    'codigo_pix' => $codigo_pix ?? null,
    'codigo_boleto' => $codigo_boleto ?? null
];

unset($_SESSION['carrinho']);
header("Location: confirmar_pagamento.php");
exit;
?>
