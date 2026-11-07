<?php
require_once '../config/database.php';

// Đảm bảo cột premium_expiry luôn tồn tại trong bảng users để không bao giờ lỗi SQL
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'premium_expiry'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN premium_expiry DATETIME DEFAULT NULL");
}

// Xử lý nút xóa tin nhắn
if (isset($_GET['del_contact_id'])) {
    $del_id = (int)$_GET['del_contact_id'];
    $conn->query("DELETE FROM contacts WHERE id_contact = $del_id");
    echo "<script>window.location='admin.php?page=contacts';</script>";
    exit;
}

// Lọc theo loại người gửi (Tất cả / Chỉ VIP N3)
$contact_filter = $_GET['c_filter'] ?? 'all';
?>

<div class="contact-admin-header">
    <div>
        <h2 class='page-title' style="margin-bottom: 6px;">
            <i class="fas fa-envelope" style="color: var(--pink-primary);"></i> Hộp thư từ học viên
        </h2>
        <p style="color: #666; margin: 0; font-size: 14px;">
            Danh sách phản hồi, góp ý hoặc cần hỗ trợ. Học viên <b>VIP N3</b> luôn được ghim ưu tiên lên đầu danh sách.
        </p>
    </div>

    <div class="contact-filter-group">
        <a href="admin.php?page=contacts&c_filter=all" class="btn <?= $contact_filter == 'all' ? '' : 'btn-outline' ?>"
            style="padding: 8px 16px; font-size: 13px;">
            Tất cả tin nhắn
        </a>
        <a href="admin.php?page=contacts&c_filter=vip" class="btn <?= $contact_filter == 'vip' ? '' : 'btn-outline' ?>"
            style="<?= $contact_filter == 'vip' ? 'background:#f39c12;' : 'border-color:#f39c12; color:#f39c12;' ?> padding: 8px 16px; font-size: 13px;">
            <i class="fas fa-crown"></i> Ưu tiên VIP N3
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table>
        <thead>
            <tr>
                <th style="padding-left: 25px; width: 65px;">ID</th>
                <th style="width: 240px;">Người gửi</th>
                <th>Nội dung tin nhắn</th>
                <th style="width: 150px;">Thời gian</th>
                <th style="width: 110px; text-align: right; padding-right: 25px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
        // Truy vấn an toàn: Kiểm tra quyền VIP thông qua cả bảng user_course_unlocks và premium_expiry
        $sql = "
            SELECT c.*, 
                   u.id AS matched_user_id,
                   u.premium_expiry,
                   (SELECT COUNT(*) FROM user_course_unlocks ucu WHERE ucu.id_user = u.id AND ucu.level = 'N3') AS has_n3_unlock
            FROM contacts c
            LEFT JOIN users u ON LOWER(TRIM(c.email)) = LOWER(TRIM(u.email))
        ";

        if ($contact_filter === 'vip') {
            $sql .= " WHERE (u.premium_expiry IS NOT NULL AND u.premium_expiry > NOW()) OR (SELECT COUNT(*) FROM user_course_unlocks ucu WHERE ucu.id_user = u.id AND ucu.level = 'N3') > 0 ";
        }

        $sql .= "
            ORDER BY 
                CASE 
                    WHEN (u.premium_expiry IS NOT NULL AND u.premium_expiry > NOW()) 
                         OR (SELECT COUNT(*) FROM user_course_unlocks ucu WHERE ucu.id_user = u.id AND ucu.level = 'N3') > 0 
                    THEN 1 
                    ELSE 2 
                END ASC, 
                c.submitted_at DESC
        ";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $date = !empty($row['submitted_at']) ? date("d/m/Y H:i", strtotime($row['submitted_at'])) : "---";
                
                // Xác định học viên VIP N3
                $is_vip = (
                    (!empty($row['premium_expiry']) && strtotime($row['premium_expiry']) > time()) || 
                    ((int)($row['has_n3_unlock'] ?? 0) > 0)
                );
                
                $vip_tag = $is_vip 
                    ? "<span class='vip-priority-badge'><i class='fas fa-crown'></i> VIP N3</span>" 
                    : "<span class='normal-user-badge'>Học viên</span>";
                
                $row_class = $is_vip ? "vip-contact-row" : "";
                $safe_name = htmlspecialchars($row['name'] ?? 'Không tên');
                $safe_email = htmlspecialchars($row['email'] ?? '');
                $safe_msg = nl2br(htmlspecialchars($row['message'] ?? ''));

                echo "<tr class='{$row_class}'>
                    <td style='padding-left: 25px; color: #999; font-weight: 700;'>#{$row['id_contact']}</td>
                    <td>
                        <div class='sender-name-wrap'>
                            <b style='color: var(--text-main); font-size: 14px;'>{$safe_name}</b>
                            {$vip_tag}
                        </div>
                        <a href='mailto:{$safe_email}' class='sender-email-link'>
                            <i class='fas fa-envelope'></i> {$safe_email}
                        </a>
                    </td>
                    <td style='line-height: 1.6; color: #444; font-size: 13.5px; padding-top: 18px; padding-bottom: 18px;'>
                        " . ($is_vip ? "<div class='vip-priority-note'><i class='fas fa-bolt'></i> Ưu tiên hỗ trợ nhanh cho học viên N3</div>" : "") . "
                        {$safe_msg}
                    </td>
                    <td style='font-size: 12.5px; color: #888; font-weight: 600;'>{$date}</td>
                    <td style='text-align: right; padding-right: 25px; white-space: nowrap;'>
                        <a href='mailto:{$safe_email}?subject=Phản hồi từ Sakura Nhật Ngữ' class='btn-action' style='background: #ebf5fb; color: #3498db; text-decoration: none;' title='Trả lời qua Email'>
                            <i class='fas fa-reply'></i>
                        </a>
                        <a href='admin.php?page=contacts&del_contact_id={$row['id_contact']}' class='btn-action btn-delete' style='text-decoration: none;' title='Xóa tin nhắn' onclick=\"return confirm('Xác nhận xóa tin nhắn này?');\">
                            <i class='fas fa-trash'></i>
                        </a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr>
                    <td colspan='5' style='text-align: center; padding: 70px 20px; color: #b2bec3;'>
                        <i class='far fa-envelope-open fa-4x' style='margin-bottom: 15px; color: #dfe6e9;'></i><br>
                        <span style='font-size: 15px; font-weight: 700;'>Chưa có tin nhắn liên hệ nào.</span>
                    </td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<style>
.contact-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 15px;
    flex-wrap: wrap;
}

.contact-filter-group {
    display: flex;
    gap: 10px;
}

.sender-name-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 5px;
}

.sender-email-link {
    font-size: 12px;
    color: #666;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.sender-email-link:hover {
    color: var(--pink-primary);
}

.vip-priority-badge {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #d35400;
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 10.5px;
    font-weight: 800;
    border: 1px solid #f9ca24;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.normal-user-badge {
    background: #f1f2f6;
    color: #7f8c8d;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}

.vip-contact-row td {
    background-color: #fffdf5 !important;
}

.vip-priority-note {
    display: inline-block;
    background: #fff0f3;
    color: var(--pink-primary);
    font-size: 11px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    margin-bottom: 6px;
}
</style>