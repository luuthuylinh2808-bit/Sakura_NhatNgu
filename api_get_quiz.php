<?php
// api_get_quiz.php
require_once 'config/database.php'; // Đảm bảo kết nối đúng CSDL
header('Content-Type: application/json');

$level = $_GET['level'] ?? 'N5';

// Bạn có thể tăng số lượng câu hỏi ở đây (ví dụ: 20, 50, hoặc 100)
$limit = 20; 

// ĐÃ SỬA: Thay ORDER BY RAND() thành ORDER BY e.id_ex ASC để cố định thứ tự
$sql = "SELECT e.id_ex AS id, e.question, e.option_A AS option_a, e.option_B AS option_b, e.option_C AS option_c, e.option_D AS option_d, e.correct_option AS correct_answer 
        FROM exercise e
        JOIN lessons l ON e.id_lesson = l.id_lesson
        WHERE l.level = ?
        ORDER BY e.id_ex ASC 
        LIMIT ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $conn->error]);
    exit;
}

$stmt->bind_param("si", $level, $limit);
$stmt->execute();
$result = $stmt->get_result();

$quizzes = [];
while ($row = $result->fetch_assoc()) {
    $quizzes[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $quizzes]);
$stmt->close();
$conn->close();
?>