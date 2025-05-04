<?php
// Conectar ao banco de dados
include "../login_cadastro/conexao.php";
// Buscar todos os artistas
$sql = "SELECT * FROM Artista";
$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Artistas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            font-size: 2.5em;
            color: #333;
            margin-bottom: 20px;
        }

        .artista {
            display: inline-block;
            width: 22%;
            margin: 10px 1%;
            padding: 15px;
            background-color: #fff;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .artista:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .artista img {
            width: 100%;
            height: auto;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .artista h3 {
            font-size: 1.2em;
            margin: 10px 0;
            color: #333;
        }

        .artista a {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .artista a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Artistas</h1>

        <?php
        if (mysqli_num_rows($result) > 0) {
            // Exibir todos os artistas com nome e foto
            while ($row = mysqli_fetch_assoc($result)) {
                $id_artista = $row['id_artista'];
                $nomeArtista = $row['nomeArtista'];
                $fotoPerfil = $row['fotoPerfil'];

                echo '<div class="artista">';
                // O link que redireciona para a página de detalhes
                echo '<a href="artistas.php?id_artista=' . $id_artista . '">';
                echo '<h3>' . $nomeArtista . '</h3>';
                echo '<img src="' . $fotoPerfil . '" alt="Foto de ' . $nomeArtista . '" class="foto-artista">';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo '<p>Nenhum artista encontrado.</p>';
        }
        ?>

    </div>

</body>
</html>

<?php
// Fechar a conexão
mysqli_close($conn);
?>
