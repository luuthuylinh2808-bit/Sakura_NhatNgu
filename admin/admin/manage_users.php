<?php
require_once '../config/database.php';

// Xử lý bộ lọc trạng thái (Tất cả / N3 Premium / Miễn phí)
$filter = $_GET['filter'] ?? 'all';
$where_clause = "WHERE role = 'user'";

if ($filter == 'premium') {
    $where_clause .= " AND premium_expiry IS NOT NULL AND premium_expiry > NOW()";
} elseif ($filter == 'free') {
    $where_clause .= " AND (premium_expiry IS NULL OR premium_expiry <= NOW())";
}

// Xử lý nút khóa/mở khóa tài khoản
if (isset($_GET['toggle_status_id'])) {
    $uid = (int)$_GET['toggle_status_id'];
    $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $uid");
    echo "<script>window.location='admin.php?page=manage_users&filter=$filter';</script>";
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 class="page-title" style="margin:0;">
        <i class="fas fa-users-cog" style="color: #3498db;"></i> Quản lý Học viên
    </h2>
    <div style="display:flex; gap:10px;">
        <a href="admin.php?page=manage_users&filter=all" class="btn <?= $filter=='all' ? '' : 'btn-outline' ?>"
            style="<?= $filter=='all' ? 'background:#3498db;' : 'border-color:#3498db; color:#3498db;' ?>">Tất cả</a>
        <a href="admin.php?page=manage_users&filter=premium" class="btn <?= $filter=='premium' ? '' : 'btn-outline' ?>"
            style="<?= $filter=='premium' ? 'background:#f39c12;' : 'border-color:#f39c12; color:#f39c12;' ?>"><i
                class="fas fa-crown"></i> N3 Premium (VIP)</a>
        <a href="admin.php?page=manage_users&filter=free" class="btn <?= $filter=='free' ? '' : 'btn-outline' ?>"
            style="<?= $filter=='free' ? 'background:#95a5a6;' : 'border-color:#95a5a6; color:#95a5a6;' ?>">Miễn phí
            N5-N4</a>
    </div>
</div>

<p style="color: #666; margin-bottom: 20px;">Theo dõi tiến độ, hạng thành viên và quản lý trạng thái tài khoản người
    dùng hệ thống.</p>

<div class="card" style="padding: 0; overflow: hidden;">
    <table>
        <tr>
            <th style="padding-left: 25px;">Tài khoản & Email</th>
            <th>Hạng N3</th>
            <th>Ngày hết hạn VIP</th>
            <th>Tiến độ học tập</th>
            <th style="text-align: right; padding-right: 25px;">Thao tác</th>
        </tr>
        <?php
        $sql = "SELECT id, username, email, premium_expiry, is_active FROM users $where_clause ORDER BY id DESC";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $is_premium = (!empty($row['premium_expiry']) && strtotime($row['premium_expiry']) > time());
                $badge = $is_premium ? "<span style='background:#fef5e7; color:#f39c12; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold;'><i class='fas fa-crown'></i> VIP N3</span>" : "<span style='background:#f1f2f6; color:#7f8c8d; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold;'>Thường</span>";
                $expiry = $is_premium ? date("d/m/Y", strtotime($row['premium_expiry'])) : "---";
                
                // Đếm tiến độ tổng quan của user
                $uid = $row['id'];
                $learned = $conn->query("SELECT COUNT(DISTINCT id_lesson) FROM user_progress WHERE id = $uid")->fetch_row()[0] ?? 0;
                $total = $conn->query("SELECT COUNT(*) FROM lessons")->fetch_row()[0] ?? 1;
                $pct = round(($learned / $total) * 100);

                // Setup màu sắc cho nút Khóa / Mở khóa
                $is_active = isset($row['is_active']) ? $row['is_active'] : 1; // Mặc định là 1 nếu cột chưa có
                $status_icon = $is_active ? 'fa-lock' : 'fa-unlock';
                $status_color = $is_active ? '#e74c3c' : '#2ecc71';
                $status_title = $is_active ? 'Khóa tài khoản này' : 'Mở khóa tài khoản';
                $status_bg = $is_active ? '#fff5f5' : '#eafaf1';

                echo "<tr>
                    <td style='padding-left: 25px;'>
                        <b style='color:#444; display:block; font-size: 14px;'>{$row['username']}</b>
                        <span style='font-size:12px; color:#888;'>{$row['email']}</span>
                    </td>
                    <td>{$badge}</td>
                    <td style='font-size:13px; color:#666; font-weight:bold;'>{$expiry}</td>
                    <td>
                        <div style='font-size:12px; font-weight:bold; color:var(--pink-primary); margin-bottom:4px;'>{$learned} / {$total} bài ({$pct}%)</div>
                        <div style='width:120px; height:6px; background:#eee; border-radius:10px;'>
                            <div style='width:{$pct}%; height:100%; background:var(--pink-primary); border-radius:10px;'></div>
                        </div>
                    </td>
                    <td style='text-align: right; padding-right: 25px;'>
                        <a href='admin.php?page=manage_users&toggle_status_id={$row['id']}&filter={$filter}' class='btn-action' style='background:{$status_bg}; color:{$status_color}; font-size:14px;' title='{$status_title}' onclick=\"return confirm('Xác nhận thay đổi trạng thái tài khoản này?');\">
                            <i class='fas {$status_icon}'></i>
                        </a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center; padding:50px; color:#999;'>Danh sách trống. Không tìm thấy học viên nào trong bộ lọc này.</td></tr>";
        }
        ?>
    </table>
</div>