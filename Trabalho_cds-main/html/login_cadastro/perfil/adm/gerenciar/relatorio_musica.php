<?php
session_start();
include "../../../../login_cadastro/conexao.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../../../../login_cadastro/login.php");
    exit();
}

require('../../../../../fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFillColor(116, 69, 204);
        $this->Rect(0, 0, 210, 20, 'F');
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, 'Relatorio Semanal - Ranking Musicas Mais Vendidas', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(0);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function SectionTitle($title) {
        $this->SetFillColor(116, 69, 204);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode($title), 0, 1, 'L', true);
        $this->Ln(2);
    }

    function FancyTable($header, $data) {
        $this->SetFillColor(116, 69, 204);
        $this->SetTextColor(116, 69, 255);
        $this->SetDrawColor(116, 69, 204);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 12);

        $w = array(90, 30, 30, 40);

        // Cabeçalho
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, utf8_decode($header[$i]), 1, 0, 'C', true);
        }
        $this->Ln();

        // Dados
        $this->SetFont('Arial', '', 11);
        $total_vendas = 0;
        foreach ($data as $row) {
            $this->Cell($w[0], 6, utf8_decode($row['nomeMusica']), 'LR', 0, 'L');
            $this->Cell($w[1], 6, $row['tempo'] . ' min', 'LR', 0, 'C');
            $this->Cell($w[2], 6, $row['total_vendas'], 'LR', 0, 'C');
            $this->Cell($w[3], 6, $row['total_assoc'], 'LR', 0, 'C');
            $this->Ln();
            $total_vendas += $row['total_vendas'];
        }

        // Total geral vendas e associações
        $this->SetFont('Arial', 'B', 11);
        $this->Cell($w[0], 6, 'TOTAL', 'LR', 0, 'R');
        $this->Cell($w[1], 6, '-', 'LR', 0, 'C');
        $this->Cell($w[2], 6, $total_vendas, 'LR', 0, 'C');
        $this->Cell($w[3], 6, '-', 'LR', 0, 'C');
        $this->Ln();

        // Linha de fechamento
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// 1. Buscar as músicas com total de vendas, baseado nas compras dos CDs associados
$sql = "
SELECT 
  m.id_musica, m.nomeMusica, m.tempo, 
  COUNT(cmp.id_compra) AS total_vendas,
  (SELECT COUNT(*) FROM CD_Musica WHERE id_musica = m.id_musica) AS total_assoc
FROM Musica m
LEFT JOIN CD_Musica cm ON m.id_musica = cm.id_musica
LEFT JOIN Compra cmp ON cm.id_cd = cmp.id_cd
GROUP BY m.id_musica
ORDER BY total_vendas DESC
LIMIT 10"; // Top 10 músicas mais vendidas

$result = $conn->query($sql);

$musicas_vendidas = [];
while ($row = $result->fetch_assoc()) {
    $musicas_vendidas[] = $row;
}

// Música destaque (mais vendida)
$musica_destaque = !empty($musicas_vendidas) ? $musicas_vendidas[0] : null;

// Inicia PDF
$pdf = new PDF();
$pdf->AddPage();

// Música Destaque
if ($musica_destaque) {
    $pdf->SectionTitle("Música Destaque da Semana");
    $texto = "🎵 {$musica_destaque['nomeMusica']} - Duração: {$musica_destaque['tempo']} minutos\n";
    $texto .= "Vendas: {$musica_destaque['total_vendas']} - CDs associados: {$musica_destaque['total_assoc']}";
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, utf8_decode($texto));
    $pdf->Ln(10);
}

// Tabela Ranking de Músicas Mais Vendidas
$pdf->SectionTitle("Ranking das 10 Músicas Mais Vendidas");
$header = ['Música', 'Duração', 'Vendas', 'CDs Associados'];
$pdf->FancyTable($header, $musicas_vendidas);

$pdf->Output('I', 'Relatorio_Ranking_Musicas.pdf');
?>
