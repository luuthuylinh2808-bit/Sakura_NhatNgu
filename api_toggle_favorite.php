<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['id_lesson'])) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực']);
    exit;
}

$user_id = $_SESSION['user_id'];
$id_lesson = (int)$_POST['id_lesson'];

// Kiểm tra xem đã thả tim chưa
$check = $conn->prepare("SELECT * FROM favourite_lesson WHERE id = ? AND id_lesson = ?");
$check->bind_param("ii", $user_id, $id_lesson);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Đã thả tim -> Bỏ tim (Xóa khỏi DB)
    $del = $conn->prepare("DELETE FROM favourite_lesson WHERE id = ? AND id_lesson = ?");
    $del->bind_param("ii", $user_id, $id_lesson);
    $del->execute();
    echo json_encode(['status' => 'removed']);
} else {
    // Chưa thả tim -> Lưu tim (Thêm vào DB)
    $ins = $conn->prepare("INSERT INTO favourite_lesson (id, id_lesson) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $id_lesson);
    $ins->execute();
    echo json_encode(['status' => 'added']);
}
?>