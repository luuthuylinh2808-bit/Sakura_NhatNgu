<?php
require_once 'config/database.php';
header('Content-Type: application/json');

$sql = "SELECT id_ex AS id, question, option_A, option_B, option_C, option_D, correct_option 
        FROM exercise 
        ORDER BY RAND() LIMIT 1";

$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'error']);
}
$conn->close();
?>