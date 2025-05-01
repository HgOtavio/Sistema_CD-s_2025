<?php
$conn = new mysqli("localhost", "root", "", "LojaCDs");
if ($conn->connect_error) {
    die("Erro: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

$sql = "SELECT id_cd, titulo FROM CD WHERE titulo LIKE '%$query%' LIMIT 10";
$result = $conn->query($sql);

$cds = [];
while ($row = $result->fetch_assoc()) {
    $cds[] = $row;
}

header('Content-Type: application/json');
echo json_encode($cds);
$conn->close();
?>
