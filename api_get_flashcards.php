<?php
// api_get_flashcards.php
require_once 'config/database.php';
header('Content-Type: application/json');


// Lấy ngẫu nhiên các flashcard từ database
$sql = "SELECT * FROM flashcards ORDER BY RAND() LIMIT 20";
$result = $conn->query($sql);


$cards = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cards[] = $row;
    }
}


echo json_encode(['status' => 'success', 'data' => $cards]);
$conn->close();
?>

