<?php
session_start();
include "../../../../login_cadastro/conexao.php";

if (!isset($_SESSION["id_usuario"]) || $_SESSION["tipo"] != "admin") {
    header("Location: ../../../../login_cadastro/login.php");
    exit();
}

require('../../../../../fpdf/fpdf.php');

class PDF extends FPDF {
    // Cores principais (mantidas)
    private $headerBgColor = [244, 204, 255]; // roxo claro
    private $headerTextColor = [85, 31, 131]; // roxo escuro
    private $cellGradientStart = [240, 180, 255]; // lilás claro
    private $cellGradientEnd = [185, 115, 255];   // lilás escuro
    private $barFillColor = [185, 115, 255];      // lilás barra
    private $barBorderColor = [85, 31, 131];      // roxo escuro borda barra

    // Cabeçalho: barra fixa com título centralizado e clean
    function Header() {
        $this->SetFillColor(...$this->headerBgColor);
        $this->Rect(0, 0, $this->w, 20, 'F');

        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(...$this->headerTextColor);
        $this->Cell(0, 15, utf8_decode('Relatório Completo de Usuários'), 0, 1, 'C');

        $this->Ln(5);
        $this->SetTextColor(0, 0, 0);
    }

    // Rodapé simples e elegante com número da página
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, 'Página ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }

    // Título de seção com background lilás suave para destacar
    function SectionTitle($title) {
        $this->SetFillColor(...$this->cellGradientStart);
        $this->SetTextColor(...$this->headerTextColor);
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(0, 12, utf8_decode($title), 0, 1, 'L', true);
        $this->Ln(3);
        $this->SetTextColor(0, 0, 0);
    }

    // Célula com efeito degradê lilás para listas e tabelas
    function GradientCell($w, $h, $txt, $border=1, $ln=0, $align='L') {
        $this->SetFillColor(...$this->cellGradientStart);
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Rect($x, $y, $w, $h, 'F');

        $this->SetDrawColor(...$this->cellGradientEnd);
        $this->Rect($x, $y, $w, $h);

        $this->SetTextColor(0, 0, 0);
        $this->Cell($w, $h, utf8_decode($txt), $border, $ln, $align);
    }

    // Gráfico de barras lilás com legenda acima, labels e valores destacados
    function BarGraph($data, $width, $height, $legend = '') {
        if (empty($data)) {
            $this->SetFont('Arial','I',10);
            $this->Cell(0,10,utf8_decode('Nenhum dado para exibir.'),0,1,'C');
            return;
        }

        $maxValue = max($data);
        if ($maxValue == 0) $maxValue = 1;

        $barWidth = $width / count($data);
        $scale = $height / $maxValue;

        $xStart = $this->GetX();
        $yStart = $this->GetY() + $height;

        // Legenda do gráfico (com maior destaque)
        if ($legend) {
            $this->SetFont('Arial', 'B', 12);
            $this->SetTextColor(...$this->headerTextColor);
            $this->Cell($width, 10, utf8_decode($legend), 0, 1, 'C');
            $this->Ln(3);
            $this->SetTextColor(0, 0, 0);
        }

        $i = 0;
        foreach ($data as $label => $value) {
            $barHeight = $value * $scale;
            $x = $xStart + $i * $barWidth;
            $y = $yStart - $barHeight;

            // Barra lilás com borda roxa
            $this->SetFillColor(...$this->barFillColor);
            $this->SetDrawColor(...$this->barBorderColor);
            $this->Rect($x, $y, $barWidth * 0.8, $barHeight, 'DF');

            // Label abaixo da barra (ajustado para evitar sobreposição)
            $this->SetXY($x, $yStart + 3);
            $this->SetFont('Arial', '', 7);
            $this->MultiCell($barWidth * 0.8, 4, utf8_decode($label), 0, 'C');

            // Valor acima da barra
            $this->SetXY($x, $y - 6);
            $this->SetFont('Arial', 'B', 8);
            $this->Cell($barWidth * 0.8, 4, $value, 0, 0, 'C');

            $i++;
        }

        $this->Ln($height + 25);
        $this->SetTextColor(0, 0, 0);
    }
}

// Criar PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Informações gerais para relatório
$sqlTotalUsers = "SELECT COUNT(*) AS total FROM Usuario";
$resTotalUsers = $conn->query($sqlTotalUsers);
$totalUsers = $resTotalUsers->fetch_assoc()['total'];

$sqlFirstDate = "SELECT MIN(data_cadastro) AS primeiro, MAX(data_cadastro) AS ultimo FROM Usuario";
$resDates = $conn->query($sqlFirstDate);
$dates = $resDates->fetch_assoc();
$periodo = date('d/m/Y', strtotime($dates['primeiro'])) . " até " . date('d/m/Y', strtotime($dates['ultimo']));

// Info no topo do relatório
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, "Total de usuários cadastrados: $totalUsers", 0, 1);
$pdf->Cell(0, 8, "Período considerado: $periodo", 0, 1);
$pdf->Ln(5);

// 1. Período com mais cadastros por semana/ano
$pdf->SectionTitle('1. Período com mais cadastros (Semana/Ano)');

$sqlPeriodos = "
    SELECT 
        YEAR(data_cadastro) AS ano, 
        WEEK(data_cadastro, 3) AS semana, 
        COUNT(*) AS total 
    FROM Usuario 
    GROUP BY ano, semana 
    ORDER BY ano ASC, semana ASC
";
$resPeriodos = $conn->query($sqlPeriodos);

$dataPeriodos = [];
$totalSemanas = 0;
while ($row = $resPeriodos->fetch_assoc()) {
    $label = 'Semana ' . $row['semana'] . '/' . $row['ano'];
    $dataPeriodos[$label] = (int)$row['total'];
    $totalSemanas++;
}

$pdf->BarGraph($dataPeriodos, 180, 50, 'Número de cadastros por semana/ano');

// Média de cadastros por semana (informação extra)
$mediaSemanal = $totalSemanas > 0 ? array_sum($dataPeriodos) / $totalSemanas : 0;
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0, 8, 'Média de cadastros por semana: ' . number_format($mediaSemanal, 2, ',', '.'), 0, 1);
$pdf->Ln(5);

// 2. Países com mais cadastros
$pdf->SectionTitle('2. Países com mais cadastros');

$sqlPaises = "SELECT pais, COUNT(*) AS total FROM Usuario GROUP BY pais ORDER BY total DESC";
$resPaises = $conn->query($sqlPaises);

$dataPaises = [];
while ($row = $resPaises->fetch_assoc()) {
    $label = $row['pais'] ?: 'Indefinido';
    $dataPaises[$label] = (int)$row['total'];
}

$pdf->BarGraph($dataPaises, 180, 50, 'Número de cadastros por país');

// 3. Valor gasto por usuário
$pdf->SectionTitle('3. Valor gasto por usuário');

$sqlGasto = "
    SELECT u.nome_completo, COALESCE(SUM(c.valorTotal),0) AS total_gasto 
    FROM Usuario u 
    LEFT JOIN Compra c ON u.id_usuario = c.id_usuario 
    GROUP BY u.id_usuario 
    ORDER BY total_gasto DESC
";
$resGasto = $conn->query($sqlGasto);

$pdf->SetFont('Arial', 'B', 10);
$pdf->GradientCell(110, 8, 'Usuário');
$pdf->GradientCell(40, 8, 'Valor Gasto (R$)', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
while ($row = $resGasto->fetch_assoc()) {
    $pdf->GradientCell(110, 8, $row['nome_completo']);
    $pdf->GradientCell(40, 8, number_format($row['total_gasto'], 2, ',', '.'), 0, 1, 'R');
}

$pdf->Ln(10);
// Nota explicativa no final
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, utf8_decode("Nota: Os valores apresentados consideram as compras registradas no sistema. Caso existam usuários sem compras, eles aparecem com valor gasto zero."));

$pdf->Output();
exit();
