    <?php
// process_register.php
session_start();
require_once 'config/database.php';

// Nhận dữ liệu từ Frontend gửi lên
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 1. Kiểm tra xem Email đã tồn tại chưa
$check_sql = "SELECT id FROM users WHERE email = '$email'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    // Nếu có người dùng rồi -> Báo lỗi
    echo json_encode(["status" => "error", "message" => "Email này đã được sử dụng!"]);
    exit;
}

// 2. Nếu chưa có -> Lưu vào CSDL (Lưu mật khẩu trực tiếp như bạn muốn)
$insert_sql = "INSERT INTO users (fullname, email, password) VALUES ('$fullname', '$email', '$password')";

if ($conn->query($insert_sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Đăng ký thành công!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi hệ thống, vui lòng thử lại."]);
}

$conn->close();
?>