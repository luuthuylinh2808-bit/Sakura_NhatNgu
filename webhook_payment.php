<?php
require_once 'config/database.php';
header('Content-Type: application/json');

// Lấy dữ liệu thô từ webhook
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Ghi log để debug (tùy chọn)
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . ' - ' . $rawData . PHP_EOL, FILE_APPEND);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
    exit;
}

// --- XỬ LÝ DỮ LIỆU TỪ CASSO ---
// Cấu trúc của Casso có thể khác, bạn cần điều chỉnh cho phù hợp.
// Dưới đây là ví dụ giả định trường 'description' chứa nội dung chuyển khoản.
$description = $data['description'] ?? ''; // Nội dung CK, ví dụ: "SAKURA12N3ABCDE"
$amount = $data['amount'] ?? 0;

// Tìm mã đơn hàng trong nội dung chuyển khoản
// Giả sử mã đơn hàng có dạng SAKURA{id_user}N3{rand} và nằm trong description
if (preg_match('/SAKURA\d+N3[A-Z0-9]+/', $description, $matches)) {
    $order_code = $matches[0];

    // 1. Kiểm tra đơn hàng có tồn tại và đang pending không
    $stmt = $conn->prepare("SELECT id_order, id_user, amount, package_type FROM orders WHERE order_code = ? AND status = 'pending'");
    $stmt->bind_param("s", $order_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_user = $row['id_user'];
        $package_type = $row['package_type'];
        $order_amount = $row['amount'];

        // (Tùy chọn) Kiểm tra số tiền có khớp không
        // if ($amount < $order_amount) { ... xử lý thiếu tiền ... }

        // 2. Cập nhật trạng thái đơn hàng thành 'completed'
        $update_order = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id_order = ?");
        $update_order->bind_param("i", $row['id_order']);
        $update_order->execute();

        // 3. Tính toán ngày hết hạn Premium dựa trên gói
        $expiry_date = null;
        if ($package_type === 'N3_MONTH') {
            $expiry_date = date('Y-m-d H:i:s', strtotime('+1 month'));
        } elseif ($package_type === 'N3_6MONTH') {
            $expiry_date = date('Y-m-d H:i:s', strtotime('+6 months'));
        } elseif ($package_type === 'N3_LIFETIME') {
            $expiry_date = '2099-12-31 23:59:59'; // Gói trọn đời
        }

        // 4. Cập nhật quyền Premium cho user
        if ($expiry_date) {
            $update_user = $conn->prepare("UPDATE users SET premium_expiry = ? WHERE id = ?");
            $update_user->bind_param("si", $expiry_date, $id_user);
            $update_user->execute();
        }

        // 5. Ghi nhận vào bảng user_course_unlocks (nếu chưa có)
        $check_unlock = $conn->prepare("SELECT id FROM user_course_unlocks WHERE id_user = ? AND level = 'N3'");
        $check_unlock->bind_param("i", $id_user);
        $check_unlock->execute();
        if ($check_unlock->get_result()->num_rows == 0) {
            $insert_unlock = $conn->prepare("INSERT INTO user_course_unlocks (id_user, level) VALUES (?, 'N3')");
            $insert_unlock->bind_param("i", $id_user);
            $insert_unlock->execute();
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Order completed and N3 unlocked.']);
        exit;
    }
}

http_response_code(200); // Vẫn trả 200 để webhook không gửi lại
echo json_encode(['status' => 'ignored', 'message' => 'No matching pending order found.']);
?>