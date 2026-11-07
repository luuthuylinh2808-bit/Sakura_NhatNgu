<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực']);
    exit;
}

$uid = (int)$_SESSION['user_id'];
$level = $_POST['level'] ?? 'ALL'; // Lựa chọn reset toàn bộ hay theo cấp độ

// Trong ví dụ này, ta xóa tiến độ của bảng user_progress (học bài).
// Nếu bạn có thêm bảng tracking bài tập hay từ vựng thì thêm lệnh DELETE tương ứng bên dưới.
if ($level === 'ALL') {
    $conn->query("DELETE FROM user_progress WHERE id = $uid");
    // Thêm nếu cần: $conn->query("DELETE FROM practice_progress WHERE id_user = $uid");
} else {
    // Reset riêng lẻ từng level (cần join với bảng lessons để biết bài nào của level nào)
    $sql = "DELETE up FROM user_progress up 
            INNER JOIN lessons l ON up.id_lesson = l.id_lesson 
            WHERE up.id = $uid AND l.level = '$level'";
    $conn->query($sql);
}

echo json_encode(['status' => 'success', 'message' => 'Đã làm mới tiến độ học tập!']);
?>