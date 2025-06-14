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
        $this->SetFillColor(244, 204, 255);    // fundo do header
        $this->Rect(0, 0, $this->GetPageWidth(), 28, 'F');
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(85, 31, 131);      // cor do texto header
        $this->Cell(0, 20, utf8_decode('Mega Relatório de Clientes'), 0, 1, 'C');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(140);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . ' / {nb}', 0, 0, 'C');
    }

    function SectionTitle($title) {
        $this->SetFont('Arial', 'B', 15);
        $this->SetFillColor(244, 204, 255);  // mesmo fundo do header
        $this->SetTextColor(85, 31, 131);
        $this->Cell(0, 14, utf8_decode($title), 0, 1, 'L', true);
        $this->Ln(3);
        $this->SetTextColor(0);
    }

    function resumoLinha($texto) {
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0);
        $this->Cell(0, 9, utf8_decode($texto), 0, 1);
    }

    function DrawBarChart($x, $y, $width, $height, $values, $labels, $maxValue) {
        $count = count($values);
        if ($count == 0) return;
        $barHeight = $height / $count;
        $margin = 3;

        // cores do seu exemplo original
        $fillColor = [240, 180, 255];
        $borderColor = [185, 115, 255];
        $textColor = [85, 31, 131];

        $this->SetFont('Arial', '', 10);

        for ($i = 0; $i < $count; $i++) {
            $barWidth = ($maxValue > 0) ? ($values[$i] / $maxValue) * $width : 0;

            $this->SetFillColor(...$fillColor);
            $this->SetDrawColor(...$borderColor);
            $this->SetLineWidth(0.8);

            $this->Rect($x, $y + $i * $barHeight + $margin, $barWidth, $barHeight - 2 * $margin, 'FD');

            $this->SetTextColor(...$textColor);
            $this->SetXY($x + $barWidth + 5, $y + $i * $barHeight + $margin + 1);
            $this->Cell(0, $barHeight - 2 * $margin, utf8_decode($labels[$i]) . " ({$values[$i]})", 0, 1);

            $this->SetTextColor(0);
        }
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// === SEÇÃO 1: Países com mais cadastros ===
$pdf->SectionTitle('Países com Mais Cadastros');

$sqlPaises = "
SELECT pais, COUNT(*) AS total
FROM Cadastro
GROUP BY pais
ORDER BY total DESC
LIMIT 10;
";
$resultPaises = $conn->query($sqlPaises);

if ($resultPaises && $resultPaises->num_rows > 0) {
    $labels = [];
    $values = [];
    $maxValue = 0;

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(240, 180, 255);
    $pdf->SetTextColor(85, 31, 131);
    $pdf->Cell(120, 10, 'País', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Cadastros', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0);

    while ($row = $resultPaises->fetch_assoc()) {
        $pdf->Cell(120, 9, utf8_decode($row['pais']), 1);
        $pdf->Cell(40, 9, $row['total'], 1, 1, 'C');

        $labels[] = utf8_decode($row['pais']);
        $values[] = (int)$row['total'];
        if ($row['total'] > $maxValue) $maxValue = $row['total'];
    }

    $pdf->Ln(10);
    $pdf->SectionTitle('Gráfico de Cadastros por País');
    $startY = $pdf->GetY();
    $pdf->DrawBarChart(25, $startY, 160, 70, $values, $labels, $maxValue);
} else {
    $pdf->resumoLinha('Nenhum dado de países encontrado.');
}

$pdf->AddPage();

// === SEÇÃO 2: Cadastros por Período (mês/ano) ===
$pdf->SectionTitle('Cadastros por Período (Mês/Ano)');

$sqlPeriodo = "
SELECT DATE_FORMAT(data_cadastro, '%Y-%m') AS periodo, COUNT(*) AS total
FROM Cadastro
GROUP BY periodo
ORDER BY total DESC
LIMIT 12;
";

$resultPeriodo = $conn->query($sqlPeriodo);

if ($resultPeriodo && $resultPeriodo->num_rows > 0) {
    $labels = [];
    $values = [];
    $maxValue = 0;

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(240, 180, 255);
    $pdf->SetTextColor(85, 31, 131);
    $pdf->Cell(60, 10, 'Período (Ano-Mês)', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Cadastros', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0);

    while ($row = $resultPeriodo->fetch_assoc()) {
        $periodoFormatado = date("M/Y", strtotime($row['periodo'] . "-01"));
        $pdf->Cell(60, 9, utf8_decode($periodoFormatado), 1);
        $pdf->Cell(40, 9, $row['total'], 1, 1, 'C');

        $labels[] = $periodoFormatado;
        $values[] = (int)$row['total'];
        if ($row['total'] > $maxValue) $maxValue = $row['total'];
    }

    $pdf->Ln(10);
    $pdf->SectionTitle('Gráfico de Cadastros por Período');
    $startY = $pdf->GetY();
    $pdf->DrawBarChart(25, $startY, 160, 70, $values, $labels, $maxValue);
} else {
    $pdf->resumoLinha('Nenhum dado de cadastro por período encontrado.');
}

$pdf->AddPage();

// === SEÇÃO 3: Valor gasto por usuário ===
$pdf->SectionTitle('Valor Gasto por Usuário');

$sqlValorGasto = "
SELECT c.id_cds, c.nome, c.pais, IFNULL(SUM(cd.preco),0) AS total_gasto
FROM Cadastro c
LEFT JOIN Compra co ON co.id_cliente = c.id_cds
LEFT JOIN CD cd ON cd.id_cd = co.id_cd
GROUP BY c.id_cds, c.nome, c.pais
ORDER BY total_gasto DESC
LIMIT 15;
";

$resultValorGasto = $conn->query($sqlValorGasto);

if ($resultValorGasto && $resultValorGasto->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(240, 180, 255);
    $pdf->SetTextColor(85, 31, 131);

    $w = [15, 60, 50, 45];
    $header = ['ID', 'Nome', 'País', 'Valor Gasto (R$)'];

    foreach ($header as $i => $col) {
        $pdf->Cell($w[$i], 10, utf8_decode($col), 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0);

    while ($row = $resultValorGasto->fetch_assoc()) {
        $pdf->Cell($w[0], 9, $row['id_cds'], 1, 0, 'C');
        $pdf->Cell($w[1], 9, utf8_decode($row['nome']), 1);
        $pdf->Cell($w[2], 9, utf8_decode($row['pais']), 1);
        $pdf->Cell($w[3], 9, number_format($row['total_gasto'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Ln();
    }
} else {
    $pdf->resumoLinha('Nenhum dado de valor gasto encontrado.');
}

$pdf->Output();
