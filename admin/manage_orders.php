<?php
require_once '../config/database.php';

// Xử lý Xóa Đơn Hàng (thường dùng để dọn dẹp đơn test)
if (isset($_GET['del_order_id'])) {
    $del_id = (int)$_GET['del_order_id'];
    $conn->query("DELETE FROM orders WHERE id_order = $del_id");
    echo "<script>window.location='admin.php?page=manage_orders';</script>";
}

// Xử lý bộ lọc Tháng/Năm
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$where_clause = "WHERE DATE_FORMAT(o.created_at, '%Y-%m') = '$selected_month'";

// Tính tổng doanh thu trong tháng đang lọc
$total_revenue = $conn->query("SELECT SUM(amount) FROM orders o $where_clause AND status='completed'")->fetch_row()[0] ?? 0;
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 class="page-title" style="margin:0;">
            <i class="fas fa-chart-line" style="color: #f39c12;"></i> Quản lý Doanh thu
        </h2>
        <p style="color: #666; margin-top: 5px; font-size: 14px;">
            Tổng doanh thu tháng <b><?= date('m/Y', strtotime($selected_month)) ?></b>:
            <span style="color: var(--pink-primary); font-weight: bold; font-size: 16px;">
                <?= number_format($total_revenue, 0, ',', '.') ?>đ
            </span>
        </p>
    </div>

    <!-- Lọc theo tháng -->
    <form method='GET' action='admin.php'
        style='display:flex; align-items:center; gap:10px; background:#fff; padding:6px 12px; border-radius:12px; border:1px solid #eee; box-shadow: 0 2px 10px rgba(0,0,0,0.02);'>
        <input type='hidden' name='page' value='manage_orders'>
        <i class="fas fa-calendar-alt" style="color: #a4b0be;"></i>
        <input type='month' name='month' value='<?= $selected_month ?>'
            style='border:none; outline:none; font-family:inherit; font-weight:700; color:#444; cursor:pointer;'
            onchange='this.form.submit()'>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; text-align: left; border-collapse: collapse;">
        <tr style="background-color: #fafafa; border-bottom: 1px solid #eee;">
            <th style="padding: 15px 15px 15px 25px; color: #888; font-size: 13px; width: 80px;">Mã ĐH</th>
            <th style="padding: 15px; color: #888; font-size: 13px;">Người mua</th>
            <th style="padding: 15px; color: #888; font-size: 13px;">Gói đăng ký</th>
            <th style="padding: 15px; color: #888; font-size: 13px;">Số tiền</th>
            <th style="padding: 15px; color: #888; font-size: 13px;">Thời gian</th>
            <th style="padding: 15px; color: #888; font-size: 13px;">Trạng thái</th>
            <th style="text-align: right; padding-right: 25px; color: #888; font-size: 13px;">Thao tác</th>
        </tr>
        <?php
        // JOIN bảng orders với users (dùng fullname)
        $sql = "SELECT o.*, u.fullname, u.email FROM orders o JOIN users u ON o.id_user = u.id $where_clause ORDER BY o.id_order DESC";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Định dạng dữ liệu hiển thị
                $order_id = "#" . str_pad($row['id_order'], 4, '0', STR_PAD_LEFT);
                $amount = number_format($row['amount'], 0, ',', '.') . 'đ';
                $date = date("d/m/Y H:i", strtotime($row['created_at']));
                
                // Trạng thái đơn hàng
                $is_completed = ($row['status'] == 'completed');
                $status_bg = $is_completed ? '#eafaf1' : '#fef5e7';
                $status_color = $is_completed ? '#2ecc71' : '#f39c12';
                $status_text = $is_completed ? 'Thành công' : 'Chờ xử lý';
                $status_badge = "<span style='background:{$status_bg}; color:{$status_color}; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold;'>{$status_text}</span>";

                echo "<tr style='border-bottom: 1px solid #f9f9f9;'>
                    <td style='padding: 15px 15px 15px 25px; font-weight:bold; color:#7f8c8d;'>{$order_id}</td>
                    <td style='padding: 15px;'>
                        <b style='color:#444; display:block; font-size: 14px;'>{$row['fullname']}</b>
                        <span style='font-size:12px; color:#888;'>{$row['email']}</span>
                    </td>
                    <td style='padding: 15px; color: var(--pink-primary); font-weight:700; font-size: 13px;'>Khóa N3 Premium</td>
                    <td style='padding: 15px; font-weight:800; color:#2f3542;'>{$amount}</td>
                    <td style='padding: 15px; font-size: 13px; color:#666;'>{$date}</td>
                    <td style='padding: 15px;'>{$status_badge}</td>
                    <td style='text-align: right; padding-right: 25px;'>
                        <a href='admin.php?page=manage_orders&del_order_id={$row['id_order']}' class='btn-action btn-delete' style='padding:8px 10px; border-radius:8px;' title='Xóa lịch sử đơn này' onclick=\"return confirm('Bạn có chắc chắn muốn xóa lịch sử giao dịch này không?');\">
                            <i class='fas fa-trash'></i>
                        </a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr>
                    <td colspan='7' style='text-align: center; padding: 60px 20px; color: #b2bec3;'>
                        <i class='fas fa-receipt fa-4x' style='margin-bottom:20px; color:#dfe6e9;'></i><br>
                        <span style='font-size:15px; font-weight:600;'>Chưa có giao dịch nào trong tháng này.</span>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</div>