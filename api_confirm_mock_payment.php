<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thực hiện giao dịch!']);
    exit;
}

// Đảm bảo cột premium_expiry tồn tại trong bảng users
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'premium_expiry'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN premium_expiry DATETIME DEFAULT NULL");
}

$uid = (int)$_SESSION['user_id'];
$order_code = trim($_POST['order_code'] ?? '');
$package_type = trim($_POST['package_type'] ?? 'N3_MONTH');
$amount = (float)($_POST['amount'] ?? 89000);

if (empty($order_code)) {
    $order_code = 'SAKURA' . rand(100000, 999999);
}

// 1. Kiểm tra xem đơn hàng đã được tạo trước đó với trạng thái 'pending' chưa
$stmt = $conn->prepare("SELECT id_order, package_type, amount FROM orders WHERE order_code = ? AND id_user = ? LIMIT 1");
$stmt->bind_param("si", $order_code, $uid);
$stmt->execute();
$res = $stmt->get_result();

if ($existing_order = $res->fetch_assoc()) {
    $package_type = $existing_order['package_type'];
    $upd = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id_order = ?");
    $upd->bind_param("i", $existing_order['id_order']);
    $upd->execute();
} else {
    // Nếu chưa có trong bảng orders thì thêm mới trực tiếp với trạng thái 'completed'
    if ($package_type === 'N3_MONTH' && $amount <= 0) $amount = 89000;
    if ($package_type === 'N3_6MONTH' && $amount <= 0) $amount = 399000;
    if ($package_type === 'N3_LIFETIME' && $amount <= 0) $amount = 699000;

    $ins_ord = $conn->prepare("INSERT INTO orders (id_user, order_code, package_type, amount, status) VALUES (?, ?, ?, ?, 'completed')");
    $ins_ord->bind_param("issd", $uid, $order_code, $package_type, $amount);
    $ins_ord->execute();
}

// 2. Lấy hạn premium_expiry hiện tại của học viên để tính cộng dồn
$u_query = $conn->query("SELECT premium_expiry FROM users WHERE id = $uid");
$u_data = $u_query ? $u_query->fetch_assoc() : null;

$base_timestamp = time(); // Mặc định tính từ hôm nay
if (!empty($u_data['premium_expiry'])) {
    $cur_exp = strtotime($u_data['premium_expiry']);
    // Nếu hạn cũ vẫn còn hiệu lực -> Lấy hạn cũ làm mốc để cộng dồn thêm
    if ($cur_exp > time()) {
        $base_timestamp = $cur_exp;
    }
}

// 3. Tính ngày hết hạn mới sau khi cộng dồn
if ($package_type === 'N3_MONTH') {
    $new_expiry = date('Y-m-d H:i:s', strtotime('+1 month', $base_timestamp));
} elseif ($package_type === 'N3_6MONTH') {
    $new_expiry = date('Y-m-d H:i:s', strtotime('+6 months', $base_timestamp));
} else {
    // Gói trọn đời (N3_LIFETIME)
    $new_expiry = '2099-12-31 23:59:59';
}

// 4. Lưu hạn mới vào bảng users
$upd_u = $conn->prepare("UPDATE users SET premium_expiry = ? WHERE id = ?");
$upd_u->bind_param("si", $new_expiry, $uid);
$upd_u->execute();

// 5. Cấp quyền mở khóa N3 trong bảng user_course_unlocks (nếu chưa có)
$chk_unlock = $conn->prepare("SELECT id FROM user_course_unlocks WHERE id_user = ? AND level = 'N3'");
$chk_unlock->bind_param("i", $uid);
$chk_unlock->execute();
if ($chk_unlock->get_result()->num_rows == 0) {
    $ins_unlock = $conn->prepare("INSERT INTO user_course_unlocks (id_user, level) VALUES (?, 'N3')");
    $ins_unlock->bind_param("i", $uid);
    $ins_unlock->execute();
}

$formatted_exp = ((int)date('Y', strtotime($new_expiry)) >= 2090) ? 'Vĩnh viễn' : date('d/m/Y', strtotime($new_expiry));

echo json_encode([
    'status' => 'success',
    'message' => 'Thanh toán thành công! Hạn N3 Premium của bạn: ' . $formatted_exp,
    'expiry' => $formatted_exp
]);
?>