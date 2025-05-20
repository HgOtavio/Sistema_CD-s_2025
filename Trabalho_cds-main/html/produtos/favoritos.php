<?php
session_start();


include "../login_cadastro/conexao.php";

// ID do usuário da sessão
$id_usuario = $_SESSION["id_usuario"];

// Query para pegar os CDs favoritos do usuário e o desconto da promoção, se houver
$sql = "SELECT CD.id_cd, CD.titulo, CD.capa, CD.preco, CD.disponibilidade, Promocao.desconto
        FROM Favoritos
        INNER JOIN CD ON Favoritos.id_cd = CD.id_cd
        LEFT JOIN Promocao ON CD.id_cd = Promocao.id_cd
        WHERE Favoritos.id_usuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Meus Favoritos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 30px;
            color: #333;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
            margin: 40px auto;
            max-width: 1200px;
        }

        .cd {
            background: #fff;
            padding: 15px;
            border-radius: 15px;
            width: 260px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .cd:hover {
            transform: scale(1.02);
        }

        .cd img {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 50%;
            border: 8px solid #111; /* borda tipo vinil */
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            margin-top: 10px;
        }

        .cd h3 {
            margin-top: 15px;
            color: #222;
        }

        .cd p {
            margin: 5px 0;
            font-size: 14px;
        }

        .disponivel {
            color: green;
            font-weight: bold;
        }

        .esgotado {
            color: red;
            font-weight: bold;
        }

        .desconto {
            color: #e60000;
            font-weight: bold;
        }

        .remove-btn {
            background: none;
            border: none;
            margin-top: 12px;
            cursor: pointer;
        }

        .remove-btn img {
            width: 32px;
            height: 32px;
            transition: transform 0.2s;
        }

        .remove-btn img:hover {
            transform: scale(1.1);
        }

        .voltar {
            text-align: center;
            margin-bottom: 40px;
        }

        .voltar a {
            text-decoration: none;
            color: #007bff;
        }

        .voltar a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Seus CDs Favoritos</h1>

    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='cd'>";
                echo "<img src='../" . ($row['capa'] ? $row['capa'] : 'sem-capa.jpg') . "' alt='Capa do CD'>";
                echo "<h3>" . htmlspecialchars($row['titulo']) . "</h3>";

                echo "<p>Preço: R$ " . number_format($row['preco'], 2, ',', '.') . "</p>";

                if ($row['desconto'] > 0) {
                    echo "<p class='desconto'>Desconto: " . $row['desconto'] . "% OFF</p>";
                }

                echo "<p class='" . ($row['disponibilidade'] > 0 ? "disponivel" : "esgotado") . "'>" . 
                        ($row['disponibilidade'] > 0 ? "Disponível" : "Esgotado") . "</p>";

                echo "<form action='remover_favorito.php' method='POST'>";
                echo "<input type='hidden' name='id_cd' value='{$row['id_cd']}'>";
                echo "<button type='submit' class='remove-btn' title='Remover dos Favoritos'>";
                echo "<img src='../imagens/favorito.png' alt='Remover'>";
                echo "</button>";
                echo "</form>";

                echo "</div>";
            }
        } else {
            echo "<p style='text-align: center;'>Você ainda não possui CDs favoritos.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>

    <div class="voltar">
        <a href="listar_cds.php">Voltar para a Loja</a>
    </div>
</body>
</html>
