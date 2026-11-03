<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $subject = $conn->real_escape_string($_POST['subject'] ?? '');
    $message_content = $conn->real_escape_string($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit;
    }

    $full_message = "Chủ đề: " . $subject . "\n\nNội dung:\n" . $message_content;

    $sql = "INSERT INTO contacts (name, email, message) VALUES ('$name', '$email', '$full_message')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Cảm ơn bạn! Tin nhắn đã được gửi tới Ban quản trị.']);
    } else {
        // ĐÃ THÊM CHỖ NÀY: Trả về lỗi chi tiết từ MySQL nếu lưu thất bại
        echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ!']);
}
?>