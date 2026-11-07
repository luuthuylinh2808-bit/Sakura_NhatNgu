<?php
require_once '../config/database.php';

// 1. Đảm bảo cột premium_expiry luôn tồn tại trong bảng users
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'premium_expiry'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN premium_expiry DATETIME DEFAULT NULL");
}

$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['q'] ?? '');

// ==========================================================
// 2. XỬ LÝ CÁC THAO TÁC QUẢN TRỊ HỌC VIÊN (POST / GET)
// ==========================================================
if (isset($_GET['action']) && isset($_GET['uid'])) {
    $target_uid = (int)$_GET['uid'];

    // A. Cấp hoặc Gia hạn thêm +30 ngày VIP N3 (Cộng dồn lịch)
    if ($_GET['action'] === 'grant_30d') {
        $u_q = $conn->query("SELECT premium_expiry FROM users WHERE id = $target_uid");
        $u_r = $u_q ? $u_q->fetch_assoc() : null;
        
        $base_time = time();
        if (!empty($u_r['premium_expiry']) && strtotime($u_r['premium_expiry']) > time()) {
            $base_time = strtotime($u_r['premium_expiry']);
        }
        $new_exp = date('Y-m-d H:i:s', strtotime('+30 days', $base_time));
        
        $conn->query("UPDATE users SET premium_expiry = '$new_exp' WHERE id = $target_uid");
        
        $chk = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $target_uid AND level = 'N3'");
        if ($chk && $chk->num_rows == 0) {
            $conn->query("INSERT INTO user_course_unlocks (id_user, level) VALUES ($target_uid, 'N3')");
        }
    }

    // B. Cấp VIP Trọn đời (Vĩnh viễn)
    elseif ($_GET['action'] === 'grant_lifetime') {
        $conn->query("UPDATE users SET premium_expiry = '2099-12-31 23:59:59' WHERE id = $target_uid");
        $chk = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $target_uid AND level = 'N3'");
        if ($chk && $chk->num_rows == 0) {
            $conn->query("INSERT INTO user_course_unlocks (id_user, level) VALUES ($target_uid, 'N3')");
        }
    }

    // C. Thu hồi quyền VIP N3 (Hạ xuống tài khoản Thường)
    elseif ($_GET['action'] === 'revoke_vip') {
        $conn->query("UPDATE users SET premium_expiry = NULL WHERE id = $target_uid");
        $conn->query("DELETE FROM user_course_unlocks WHERE id_user = $target_uid AND level = 'N3'");
    }

    // D. Reset tiến độ học tập của học viên về 0%
    elseif ($_GET['action'] === 'reset_progress') {
        $conn->query("DELETE FROM user_progress WHERE id = $target_uid");
        $conn->query("DELETE FROM user_progress WHERE id_user = $target_uid");
    }

    // E. Xóa tài khoản học viên
    elseif ($_GET['action'] === 'delete_user') {
        $conn->query("DELETE FROM user_course_unlocks WHERE id_user = $target_uid");
        $conn->query("DELETE FROM user_progress WHERE id = $target_uid");
        $conn->query("DELETE FROM orders WHERE id_user = $target_uid");
        $conn->query("DELETE FROM users WHERE id = $target_uid AND role != 'admin'");
    }

    echo "<script>window.location='admin.php?page=manage_users&filter=" . urlencode($filter) . "&q=" . urlencode($search) . "';</script>";
    exit;
}

// ==========================================================
// 3. XÂY DỰNG TRUY VẤN DANH SÁCH HỌC VIÊN
// ==========================================================
$where_sql = "WHERE u.role = 'user'";
if ($search !== '') {
    $safe_q = $conn->real_escape_string($search);
    $where_sql .= " AND (u.fullname LIKE '%$safe_q%' OR u.email LIKE '%$safe_q%')";
}

$sql_base = "
    SELECT u.id, u.fullname, u.email, u.premium_expiry,
           (SELECT COUNT(*) FROM user_course_unlocks ucu WHERE ucu.id_user = u.id AND ucu.level = 'N3') AS is_vip_unlocked,
           (SELECT COUNT(*) FROM orders o WHERE o.id_user = u.id AND o.status = 'completed') AS total_orders,
           (SELECT COALESCE(SUM(o.amount), 0) FROM orders o WHERE o.id_user = u.id AND o.status = 'completed') AS total_spent
    FROM users u
    $where_sql
";

if ($filter === 'premium') {
    $sql_base .= " HAVING is_vip_unlocked > 0 OR (u.premium_expiry IS NOT NULL AND u.premium_expiry > NOW())";
} elseif ($filter === 'free') {
    $sql_base .= " HAVING is_vip_unlocked = 0 AND (u.premium_expiry IS NULL OR u.premium_expiry <= NOW())";
}

$sql_base .= " ORDER BY is_vip_unlocked DESC, u.id DESC";
?>

<div class="users-top-bar">
    <div>
        <h2 class="page-title" style="margin: 0 0 6px 0;">
            <i class="fas fa-users-cog" style="color: #3498db;"></i> Quản lý Học viên & Đặc quyền N3
        </h2>
        <p style="color: #666; margin: 0; font-size: 14px;">
            Theo dõi tiến độ học tập, thời hạn khóa N3 và cấp/thu hồi quyền VIP trực tiếp.
        </p>
    </div>

    <div class="users-controls">
        <!-- Ô tìm kiếm học viên -->
        <form method="GET" action="admin.php" class="user-search-form">
            <input type="hidden" name="page" value="manage_users">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên hoặc email..."
                class="user-search-input">
            <button type="submit" class="user-search-btn"><i class="fas fa-search"></i></button>
        </form>

        <!-- Nút lọc phân loại -->
        <div style="display:flex; gap:8px;">
            <a href="admin.php?page=manage_users&filter=all" class="btn <?= $filter=='all' ? '' : 'btn-outline' ?>"
                style="<?= $filter=='all' ? 'background:#3498db;' : 'border-color:#3498db; color:#3498db;' ?> padding: 8px 15px; font-size: 13px;">Tất
                cả</a>
            <a href="admin.php?page=manage_users&filter=premium"
                class="btn <?= $filter=='premium' ? '' : 'btn-outline' ?>"
                style="<?= $filter=='premium' ? 'background:#f39c12;' : 'border-color:#f39c12; color:#f39c12;' ?> padding: 8px 15px; font-size: 13px;"><i
                    class="fas fa-crown"></i> N3 Premium</a>
            <a href="admin.php?page=manage_users&filter=free" class="btn <?= $filter=='free' ? '' : 'btn-outline' ?>"
                style="<?= $filter=='free' ? 'background:#95a5a6;' : 'border-color:#95a5a6; color:#95a5a6;' ?> padding: 8px 15px; font-size: 13px;">Miễn
                phí</a>
        </div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #fafafa; border-bottom: 1px solid #eee;">
                <th style="padding: 15px 15px 15px 25px; color: #888; font-size: 12.5px;">TÀI KHOẢN</th>
                <th style="padding: 15px; color: #888; font-size: 12.5px;">HẠNG THÀNH VIÊN</th>
                <th style="padding: 15px; color: #888; font-size: 12.5px;">THỜI HẠN N3 & CHI TIÊU</th>
                <th style="padding: 15px; color: #888; font-size: 12.5px;">TIẾN ĐỘ HỌC TẬP</th>
                <th style="padding: 15px 25px 15px 15px; color: #888; font-size: 12.5px; text-align: right;">THAO TÁC
                    QUẢN TRỊ</th>
            </tr>
        </thead>
        <tbody>
            <?php
        $result = $conn->query($sql_base);
        
        if ($result && $result->num_rows > 0) {
            $total_lessons = $conn->query("SELECT COUNT(*) FROM lessons")->fetch_row()[0] ?? 1;
            
            while ($row = $result->fetch_assoc()) {
                $uid = (int)$row['id'];
                $has_exp = !empty($row['premium_expiry']) && strtotime($row['premium_expiry']) > time();
                $is_premium = ($row['is_vip_unlocked'] > 0) || $has_exp;
                $is_lifetime = $has_exp && ((int)date('Y', strtotime($row['premium_expiry'])) >= 2090);

                // 1. Huy hiệu Hạng thành viên
                if ($is_lifetime) {
                    $badge = "<span class='u-badge u-badge-lifetime'><i class='fas fa-infinity'></i> VIP Trọn Đời</span>";
                } elseif ($is_premium) {
                    $badge = "<span class='u-badge u-badge-vip'><i class='fas fa-crown'></i> VIP N3</span>";
                } else {
                    $badge = "<span class='u-badge u-badge-free'>Thường (N5-N4)</span>";
                }

                // 2. Thông tin Thời hạn & Tổng chi tiêu
                if ($is_lifetime) {
                    $expiry_html = "<b style='color:#27ae60;'>Vĩnh viễn</b>";
                } elseif ($has_exp) {
                    $days_left = ceil((strtotime($row['premium_expiry']) - time()) / 86400);
                    $exp_date_str = date('d/m/Y', strtotime($row['premium_expiry']));
                    $warn_color = ($days_left <= 7) ? '#e74c3c' : '#2f3542';
                    $expiry_html = "<b style='color:{$warn_color};'>Đến {$exp_date_str}</b> <span style='font-size:11px; color:#888;'>(còn {$days_left} ngày)</span>";
                } elseif ($is_premium) {
                    $expiry_html = "<b style='color:#f39c12;'>Đang kích hoạt</b>";
                } else {
                    $expiry_html = "<span style='color:#aaa;'>Chưa đăng ký N3</span>";
                }

                $spent_str = number_format((float)$row['total_spent'], 0, ',', '.') . 'đ';
                $orders_cnt = (int)$row['total_orders'];

                // 3. Đếm tiến độ bài học
                $res_learned = $conn->query("SELECT COUNT(DISTINCT id_lesson) FROM user_progress WHERE id = $uid");
                if (!$res_learned) {
                    $res_learned = $conn->query("SELECT COUNT(DISTINCT id_lesson) FROM user_progress WHERE id_user = $uid");
                }
                $learned = $res_learned ? (int)$res_learned->fetch_row()[0] : 0;
                $pct = ($total_lessons > 0) ? round(($learned / $total_lessons) * 100) : 0;
                if ($pct > 100) $pct = 100;

                $safe_fullname = htmlspecialchars($row['fullname'] ?: 'Học viên #' . $uid);
                $safe_email = htmlspecialchars($row['email']);
                $base_link = "admin.php?page=manage_users&filter=" . urlencode($filter) . "&q=" . urlencode($search) . "&uid={$uid}";

                echo "<tr style='border-bottom: 1px solid #f5f5f5;'>
                    <td style='padding: 15px 15px 15px 25px;'>
                        <b style='color:#2f3542; display:block; font-size: 14px; margin-bottom: 2px;'>{$safe_fullname}</b>
                        <span style='font-size:12px; color:#888;'>{$safe_email}</span>
                    </td>
                    <td style='padding: 15px;'>{$badge}</td>
                    <td style='padding: 15px; font-size: 13px;'>
                        <div>{$expiry_html}</div>
                        <div style='font-size: 11.5px; color: #888; margin-top: 3px;'>
                            Đã mua: <b>{$orders_cnt} đơn</b> ({$spent_str})
                        </div>
                    </td>
                    <td style='padding: 15px;'>
                        <div style='font-size:12px; font-weight:800; color:var(--pink-primary); margin-bottom:5px;'>{$learned} / {$total_lessons} bài ({$pct}%)</div>
                        <div style='width:130px; height:7px; background:#eee; border-radius:10px; overflow:hidden;'>
                            <div style='width:{$pct}%; height:100%; background:linear-gradient(90deg, #ff9a9e, #ff6b81); border-radius:10px;'></div>
                        </div>
                    </td>
                    <td style='padding: 15px 25px 15px 15px; text-align: right; white-space: nowrap;'>
                        <!-- Nút +30 ngày VIP -->
                        <a href='{$base_link}&action=grant_30d' class='u-act-btn u-act-add' title='Cấp / Cộng dồn +30 ngày VIP N3' onclick=\"return confirm('Cấp thêm +30 ngày VIP N3 cho học viên {$safe_fullname}?');\">
                            <i class='fas fa-calendar-plus'></i> +30 ngày
                        </a>

                        <!-- Nút cấp Vĩnh viễn -->
                        " . (!$is_lifetime ? "
                        <a href='{$base_link}&action=grant_lifetime' class='u-act-btn u-act-life' title='Nâng cấp VIP Trọn đời' onclick=\"return confirm('Nâng cấp tài khoản {$safe_fullname} lên VIP Trọn Đời?');\">
                            <i class='fas fa-infinity'></i> Trọn đời
                        </a>" : "") . "

                        <!-- Nút Thu hồi VIP (nếu đang là VIP) -->
                        " . ($is_premium ? "
                        <a href='{$base_link}&action=revoke_vip' class='u-act-btn u-act-revoke' title='Thu hồi quyền VIP N3' onclick=\"return confirm('Thu hồi quyền VIP N3 của học viên {$safe_fullname}?');\">
                            <i class='fas fa-user-slash'></i> Hạ VIP
                        </a>" : "") . "

                        <!-- Nút Reset tiến độ -->
                        <a href='{$base_link}&action=reset_progress' class='u-act-btn u-act-reset' title='Đặt lại tiến độ học về 0%' onclick=\"return confirm('Đặt lại toàn bộ tiến độ bài học của {$safe_fullname} về 0%?');\">
                            <i class='fas fa-undo-alt'></i>
                        </a>

                        <!-- Nút Xóa tài khoản -->
                        <a href='{$base_link}&action=delete_user' class='u-act-btn u-act-del' title='Xóa học viên' onclick=\"return confirm('CẢNH BÁO: Xóa vĩnh viễn tài khoản {$safe_fullname}?');\">
                            <i class='fas fa-trash'></i>
                        </a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center; padding:60px 20px; color:#999;'>Không tìm thấy học viên nào phù hợp.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<style>
.users-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 15px;
    flex-wrap: wrap;
}

.users-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.user-search-form {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    padding: 3px 5px 3px 14px;
}

.user-search-input {
    border: none;
    outline: none;
    font-family: inherit;
    font-size: 13px;
    width: 180px;
    color: #444;
}

.user-search-btn {
    background: var(--pink-primary);
    color: #fff;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 12px;
}

.u-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 11px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 800;
}

.u-badge-vip {
    background: #fef5e7;
    color: #f39c12;
    border: 1px solid #fde3a7;
}

.u-badge-lifetime {
    background: #eafaf1;
    color: #27ae60;
    border: 1px solid #a9dfbf;
}

.u-badge-free {
    background: #f1f2f6;
    color: #7f8c8d;
}

.u-act-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none;
    margin-left: 4px;
    transition: all 0.2s ease;
}

.u-act-btn:hover {
    transform: translateY(-1px);
    filter: brightness(0.95);
}

.u-act-add {
    background: #fef5e7;
    color: #e67e22;
    border: 1px solid #fad7a0;
}

.u-act-life {
    background: #eafaf1;
    color: #27ae60;
    border: 1px solid #a9dfbf;
}

.u-act-revoke {
    background: #f4ecf7;
    color: #8e44ad;
    border: 1px solid #d7bde2;
}

.u-act-reset {
    background: #ebf5fb;
    color: #2980b9;
}

.u-act-del {
    background: #fff5f5;
    color: #e74c3c;
}
</style>