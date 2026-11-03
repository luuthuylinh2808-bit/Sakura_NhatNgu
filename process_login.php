<?php
session_start();
require_once 'config/database.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 1. Tìm user trong CSDL dựa vào email
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Lấy dữ liệu của user đó ra
    
    // 2. So sánh mật khẩu (Vì không mã hóa nên ta so sánh trực tiếp)
    if ($user['password'] === $password) {
        
        // 3. Đúng mật khẩu -> Lưu thông tin vào Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role']; // LƯU THÊM QUYỀN (ROLE) VÀO SESSION
        
        // 4. Phân quyền chuyển hướng
        // 4. Phân quyền chuyển hướng
        if ($user['role'] === 'admin') {
            echo json_encode([
                "status" => "success", 
                "message" => "Xin chào Admin!", 
                // ĐÃ SỬA: Thêm chữ admin/ để trỏ đúng vào thư mục
                "redirect" => "admin/admin.php" 
            ]);
        } else {    
            echo json_encode([
                "status" => "success", 
                "message" => "Đăng nhập thành công!", 
                "redirect" => "index.php"
            ]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Sai mật khẩu!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Tài khoản không tồn tại!"]);
}

$conn->close();
?>