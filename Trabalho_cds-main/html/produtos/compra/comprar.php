<?php
session_start();
include "../../login_cadastro/conexao.php";

// Verifica se usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    echo "Usuário não está logado.";
    exit;
}

// Verifica se o carrinho existe e não está vazio
$carrinho = $_SESSION['carrinho'] ?? [];
if (empty($carrinho)) {
    echo "Carrinho vazio.";
    exit;
}

$totalCompra = 0;
$taxaEntrega = 0;

// Calcula o total da compra considerando descontos
foreach ($carrinho as $id_cd => $quantidade) {
    // Buscar preço do CD
    $sql = "SELECT preco FROM CD WHERE id_cd = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Erro na consulta do preço.";
        exit;
    }
    $stmt->bind_param("i", $id_cd);
    $stmt->execute();
    $stmt->bind_result($preco);
    if (!$stmt->fetch()) {
        $stmt->close();
        continue;
    }
    $stmt->close();

    // Buscar desconto
    $sql = "SELECT desconto FROM Promocao WHERE id_cd = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Erro na consulta do desconto.";
        exit;
    }
    $stmt->bind_param("i", $id_cd);
    $stmt->execute();
    $stmt->bind_result($desconto);
    if (!$stmt->fetch()) {
        $desconto = 0;
    }
    $stmt->close();

    $precoComDesconto = $preco - ($preco * ($desconto / 100));
    $totalCompra += $precoComDesconto * $quantidade;
}

// Buscar endereço do usuário
$sql = "SELECT logradouro, numero, bairro, cidade, estado, pais, cep FROM Usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Erro na consulta do endereço.";
    exit;
}
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$stmt->bind_result($logradouro, $numero, $bairro, $cidade, $estado, $pais, $cep);
if (!$stmt->fetch()) {
    echo "Endereço não encontrado.";
    exit;
}
$stmt->close();

$enderecoEntrega = compact('logradouro', 'numero', 'bairro', 'cidade', 'estado', 'pais', 'cep');

// Determinar taxa de entrega com base no CEP
$cepLimpo = preg_replace("/[^0-9]/", "", $enderecoEntrega['cep']);
$inicioCep = substr($cepLimpo, 0, 2);

switch (true) {
    case in_array($inicioCep, ['01','02','03','04','05','06','07','08','09']):
        $taxaEntrega = 10.00;
        break;
    case in_array($inicioCep, ['80','81','82','83','84','85']):
        $taxaEntrega = 12.00;
        break;
    case in_array($inicioCep, ['20','21','22','23','24']):
        $taxaEntrega = 14.00;
        break;
    case in_array($inicioCep, ['60','61','62','63','64']):
        $taxaEntrega = 16.00;
        break;
    case in_array($inicioCep, ['70','71','72','73']):
        $taxaEntrega = 18.00;
        break;
    default:
        $taxaEntrega = 20.00;
        break;
}

$_SESSION['total_compra'] = $totalCompra + $taxaEntrega;

// Gerar código PIX (exemplo simples)
$codigoPix = strtoupper(bin2hex(random_bytes(4)));

// Gerar linha digitável do boleto (exemplo simples)
function gerarLinhaDigitavel() {
    $partes = [];
    for ($i=0; $i<5; $i++) {
        $partes[] = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
    }
    return implode('.', $partes);
}
$linhaDigitavel = gerarLinhaDigitavel();
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Cd</title>
    <link rel="shortcut icon" href="../../../img/favicon/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="../../../css/compra/comprar.css">
    <link rel="stylesheet" href="../../../css/cabeçalhos/cabeçalho_com_login_sem_menu.css">

    <script src="../../../js/mascaras/mascara_cep.js" defer></script>
    <script src="../../../js/mascaras/mascara_num.js" defer></script>
    <script src="../../../js/mascaras/mascara_cart.js" defer></script>
    <script src="../../../js/mascaras/mascara_data.js" defer></script>
    <script src="../../../js/compra/compra2.js" defer></script>


    <style>
    label { display: block; margin-top: 10px; }
    input, select { padding: 5px; margin-top: 5px; width: 300px; }
    #campos_cartao { display: none; margin-top: 20px; border: 1px solid #ccc; padding: 15px; background-color: #f9f9f9; }
    #modal_pix, #modal_boleto {
      display: none; position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.7);
      justify-content: center; align-items: center;
      z-index: 9999;
    }
    .modal-content {
      background: #fff; padding: 30px; text-align: center; border-radius: 8px;
      max-width: 400px;
      margin: auto;
    }
    .linha-digitavel {
      font-family: monospace;
      font-size: 1.2em;
      background: #eee;
      padding: 10px;
      border-radius: 5px;
      user-select: all;
      margin: 10px 0;
    }
  </style>
</head>

<body>
    <header> 

        <div id="parte_de_cima_cab">

              <!-- Logo da página -->
              <a href="#" id="logo"><img src="../../../../../img/cabeçario/logo.png" alt="Logo" id="img_logo"></a> 
            
            <!-- Barra de pesquisa -->
            

            <div id="login_carrinho"> <!-- Conta e Carrinho -->
                    <a href="#"><img src="../../../../../img/cabeçario/icone_perfil.png" alt="Perfil" id="Perfil"></a><!-- Imagem de perfil -->

                <a href="#"><img src="../../../../../img/cabeçario/icone_carrinho.png" alt="Carrinho" id="Carrinho"></a><!-- Ícone de carrinho -->
            </div>
        </div>
    </header>

    <main>
        
        <section id="compra">
         <form id="form_pagamento" method="POST" action="processar_compra.php">
            <!-- Título principal da página -->
            <h1 id="titulo">Comprar</h1>
           
                

                <h1 class="subtitulo">Finalizar Compra</h1>
                <div class="inputs">
                <div class="separar cima">
                    <div class="part">
                        <p class="subtitulo_p" >Forma de Pagamento:</p>
                        <select class="tipo" name="forma_pagamento" id="forma_pagamento" required>
                          
                            <option value="à vista">Á Vista</option>
                            <option value="a prazo">Á Prazo</option>
                        </select>
                        <div>
                            <p class="subtitulo_p">Tipo de Pagamento:</p>
                            <select class="tipo" name="tipo_pagamento" id="tipo_pagamento" required>
                              
                                <option value="boleto bancário">Boleto</option>
                                <option value="pix">Pix</option>
                                <option value="cartão">Cartão</option>
                            </select>
                        </div>
                               <input type="hidden" name="codigo_boleto" value="<?= htmlspecialchars($linhaDigitavel) ?>">
                       <input type="hidden" name="codigo_pix" value="<?= htmlspecialchars($codigoPix) ?>">
                      <input type="hidden" name="linha_digitavel" value="<?= htmlspecialchars($linhaDigitavel) ?>">


                        <p class="subtitulo_p">Tipo de Envio:</p>
                        <select class="tipo" name="tipo_envio" required>
                           
                            <option value="aéreo">Aereo</option>
                            <option  value="marítimo">Maritimo</option>
                        </select>
                    </div>

                    
                    <div id="cart" class="item_input">
                          

                        <input type="text" placeholder="Nome no Cartão" class="input"  name="nome_cartao" autocomplete="cc-name" >                         
                        <input type="text" id="numero_cartao" placeholder="Número do Cartão" class="input" maxlength="19" name="num_cartao" autocomplete="cc-number" >                         
                        <input type="text" placeholder="Validade" class="input data" maxlength="10"  name="validade_cartao" autocomplete="cc-exp" >                         
                        <input type="text" placeholder="CVV" class="input num"  name="cvv_cartao" maxlength="4" autocomplete="cc-csc" >      

                        <p class="subtitulo_p ">Número de Parcelas:</p>

                            <select name="tipo_cartao" required>
                               
                                <option value="credito">Crédito</option>
                                <option value="debito">Débito</option>
                            </select> 
                            
                        <select class="tipo parcela"  name="parcelas" id="parcelas" >
                                            <?php 
                        for ($i = 1; $i <= 12; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                        </select>                         
                    </div>
                   
                     
                    
                </div>
            </div>
                <h1 class="subtitulo">Endereço De Entrega</h1>
                <div >
                    
                            <input type="checkbox" id="end_padrao" class="input" checked > <label for="end_padrao"style="display:inline;" >Usar endereço padrão</label><br>
                            <input type="checkbox" id="outro_endereco" class="input"> <label for="outro_endereco" style="display:inline;" >Usar outro endereço</label><br><br>
          
                </div>
                
                <div class="inputs">
                <div class="separar">


                     <?php foreach ($enderecoEntrega as $campo => $valor): ?>
                <label><?= ucfirst($campo) ?>:</label>
                <input type="text" name="<?= htmlspecialchars($campo) ?>" id="<?= htmlspecialchars($campo) ?>" value="<?= htmlspecialchars($valor) ?>" disabled class="input"><br>
                <?php endforeach; ?>
                        
                    </div>
                    </div>
                



                 <div class="alinhar"><div><p class="titulo_valor"><strong>Valor Total:</strong> R$ <?= number_format($_SESSION['total_compra'], 2, ',', '.') ?></p>
                    <p class="titulo_valor"><strong>Taxa de Entrega:</strong> R$ <?= number_format($taxaEntrega, 2, ',', '.') ?></p>
                </div><input type="submit" value="Confirmar Compra"></div>
          </form>
        </section>

                          <!-- Modal PIX -->
  <div id="modal_pix">
    <div class="modal-content">
      <h2>Pagamento via PIX</h2>
      <p>Código PIX: <strong><?= $codigoPix ?></strong></p>
      <p>Use este código para realizar o pagamento.</p>
    </div>
  </div>

   <!-- Modal Boleto -->
  <div id="modal_boleto">
    <div class="modal-content">
      <h2>Boleto Bancário</h2>
      <p>Linha Digitável:</p>
      <p class="linha-digitavel"><?= $linhaDigitavel ?></p>
      <p>Utilize essa linha para pagamento do boleto.</p>
    </div>
  </div>
          
    </main>

     <!-- Seção de imagens à direita -->
     <div id="imgs_direita">
        <!-- Imagens decorativas à direita -->
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita"></div>
        <div class="cortar"><img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_direita" id="sumir_e"></div>
    </div>

    <!-- Seção de imagens à esquerda -->
    <div id="imgs_esquerda">
        <!-- Imagens decorativas à esquerda -->
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda">
        <img src="../../../img/img_cantos/img_cantos.png" alt="notas musicais" class="img_esquerda" id="sumir_d">
    </div>



<script>
  const tipoPagamento = document.getElementById('tipo_pagamento');
  const camposCartao = document.getElementById('campos_cartao');
// Corrigir estas linhas:
const modalPix = document.getElementById('modal_pix');    // estava 'modalPix'
const modalBoleto = document.getElementById('modal_boleto'); // estava 'modalBoleto'
  const form = document.getElementById('form_pagamento');
  const endPadrao = document.getElementById('end_padrao');
  const outroEndereco = document.getElementById('outro_endereco');
  const enderecoInputs = ['logradouro','numero','bairro','cidade','estado','pais','cep'];

  // Mostrar campos do cartão só se for cartão
  tipoPagamento.addEventListener('change', () => {
    if (tipoPagamento.value.toLowerCase() === 'cartão' || tipoPagamento.value.toLowerCase() === 'cartao') {
      camposCartao.style.display = 'block';
    } else {
      camposCartao.style.display = 'none';
    }
  });

  // Controle dos checkboxes endereço
  endPadrao.addEventListener('change', () => {
    if (endPadrao.checked) {
      outroEndereco.checked = false;
      enderecoInputs.forEach(id => {
        document.getElementById(id).value = "<?= addslashes($enderecoEntrega['logradouro']) ?>";
        document.getElementById(id).disabled = true;
      });
    }
  });

  outroEndereco.addEventListener('change', () => {
    if (outroEndereco.checked) {
      endPadrao.checked = false;
      enderecoInputs.forEach(id => {
        document.getElementById(id).value = "";
        document.getElementById(id).disabled = false;
      });
    }
  });

  // Exibir modal e bloquear envio se pagamento for PIX ou boleto
  form.addEventListener('submit', e => {
    e.preventDefault();
    const tipo = tipoPagamento.value.toLowerCase();

    if (tipo === 'pix') {
      modalPix.style.display = 'flex';
      setTimeout(() => form.submit(), 3000);
    } else if (tipo === 'boleto bancário' || tipo === 'boleto') {
      modalBoleto.style.display = 'flex';
      setTimeout(() => form.submit(), 3000);
    } else {
      form.submit();
    }
  });
</script>

</body>
</html>