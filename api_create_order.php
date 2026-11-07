<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi xác thực']);
    exit;
}

$uid = $_SESSION['user_id'];
$package_type = $_POST['package_type'] ?? 'N3_MONTH';
$amount = (int)($_POST['amount'] ?? 49000);

// Tạo mã đơn hàng duy nhất
$order_code = 'SAKURA' . $uid . 'N3' . strtoupper(substr(md5(uniqid()), 0, 5));

$stmt = $conn->prepare("INSERT INTO orders (id_user, order_code, amount, package_type, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("isis", $uid, $order_code, $amount, $package_type);

if ($stmt->execute()) {
    $bank_id = 'VCB'; 
    $account_no = '0123456789'; 
    $account_name = 'SAKURA NHAT NGU'; 
    
    // Gọi API miễn phí của VietQR để tạo ảnh QR
    $qr_url = "https://img.vietqr.io/image/{$bank_id}-{$account_no}-compact2.png?amount={$amount}&addInfo={$order_code}&accountName=" . urlencode($account_name);

    echo json_encode([
        'status' => 'success',
        'order_code' => $order_code,
        'qr_url' => $qr_url
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Không thể tạo đơn hàng']);
}
?>