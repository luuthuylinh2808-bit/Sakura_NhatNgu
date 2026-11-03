<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực']);
    exit;
}

$uid = (int)$_SESSION['user_id'];
$order_code = $_POST['order_code'] ?? '';

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = ? AND id_user = ?");
$stmt->bind_param("si", $order_code, $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($order = $result->fetch_assoc()) {
    
    // ===============================================
    // [FIX] TỰ ĐỘNG KÍCH HOẠT PREMIUM KHI ĐƠN HOÀN TẤT
    // ===============================================
    if ($order['status'] === 'completed') {
        // Kiểm tra xem user đã được mở khóa N3 chưa
        $chk = $conn->prepare("SELECT id FROM user_course_unlocks WHERE id_user = ? AND level = 'N3'");
        $chk->bind_param("i", $uid);
        $chk->execute();
        $already = $chk->get_result()->num_rows > 0;
        
        if (!$already) {
            // Tính ngày hết hạn dựa trên loại gói
            $expiry = null;
            switch ($order['package_type']) {
                case 'N3_MONTH':    $expiry = date('Y-m-d H:i:s', strtotime('+1 month')); break;
                case 'N3_6MONTH':   $expiry = date('Y-m-d H:i:s', strtotime('+6 months')); break;
                case 'N3_LIFETIME': $expiry = '2099-12-31 23:59:59'; break;
            }
            
            if ($expiry) {
                // 1. Cập nhật hạn Premium cho user
                $upd = $conn->prepare("UPDATE users SET premium_expiry = ? WHERE id = ?");
                $upd->bind_param("si", $expiry, $uid);
                $upd->execute();
            }
            
            // 2. Ghi vào bảng user_course_unlocks
            $ins = $conn->prepare("INSERT INTO user_course_unlocks (id_user, level) VALUES (?, 'N3')");
            $ins->bind_param("i", $uid);
            $ins->execute();
        }
    }
    // ===============================================

    echo json_encode([
        'status' => 'success',
        'payment_status' => $order['status']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
}
?>