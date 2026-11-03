<?php
session_start();
// Đảm bảo đường dẫn này trỏ đúng vào file database của bạn
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$user_id = $_SESSION['user_id'];
$fullname = trim($_POST['fullname'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$dob = trim($_POST['dob'] ?? '');

if (empty($fullname)) {
    echo json_encode(['status' => 'error', 'message' => 'Họ và tên không được để trống!']);
    exit;
}

// Xử lý chống SQL Injection
$fullname = $conn->real_escape_string($fullname);
$phone = $conn->real_escape_string($phone);

// Xử lý giá trị ngày sinh (nếu rỗng thì gán NULL vào Database)
$dob_sql = empty($dob) ? "NULL" : "'" . $conn->real_escape_string($dob) . "'";

$sql = "UPDATE users SET fullname = '$fullname', phone = '$phone', dob = $dob_sql WHERE id = $user_id";

if ($conn->query($sql)) {
    // Cập nhật lại session tên người dùng để hiển thị ngay trên thanh Header
    $_SESSION['user_name'] = $fullname;
    
    echo json_encode(['status' => 'success', 'message' => 'Cập nhật hồ sơ thành công!']);
} else {
    // Báo lỗi chi tiết nếu MySQL gặp vấn đề
    echo json_encode(['status' => 'error', 'message' => 'Lỗi CSDL: ' . $conn->error]);
}
?>