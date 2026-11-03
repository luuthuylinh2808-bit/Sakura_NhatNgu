<?php
// Xử lý lấy thống kê nếu người dùng đã đăng nhập
$completed_lessons = 0;
$total_lessons = 50; // Giá trị mặc định dự phòng
$streak = 0;

if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $user_id = $_SESSION['user_id'];

    // 1. Lấy tổng số bài học từ bảng lessons
    $res_total = $conn->query("SELECT COUNT(*) as total FROM lessons");
    if ($res_total && $row = $res_total->fetch_assoc()) {
        $total_lessons = $row['total'];
    }

    // 2. Lấy số bài đã hoàn thành từ bảng user_progress
    $stmt_comp = $conn->prepare("SELECT COUNT(DISTINCT id_lesson) as completed FROM user_progress WHERE id = ?");
    $stmt_comp->bind_param("i", $user_id);
    $stmt_comp->execute();
    $res_comp = $stmt_comp->get_result();
    if ($res_comp && $row = $res_comp->fetch_assoc()) {
        $completed_lessons = $row['completed'];
    }

    // 3. Xử lý logic Streak (Chuỗi ngày học liên tiếp)
    $stmt_user = $conn->prepare("SELECT streak, last_study_date FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    
    if ($res_user && $user_data = $res_user->fetch_assoc()) {
        $streak = (int)$user_data['streak'];
        $last_study = $user_data['last_study_date'];
        
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // Cập nhật lại streak nếu người dùng truy cập vào một ngày mới
        if ($last_study !== $today) {
            if ($last_study === $yesterday) {
                $streak++; // Vừa học hôm qua, hôm nay vào tiếp -> Tăng chuỗi
            } else {
                $streak = 1; // Chưa từng học, hoặc đã nghỉ quá 1 ngày -> Reset về 1
            }
            
            // Lưu mức streak mới và cập nhật ngày học thành hôm nay
            $stmt_update = $conn->prepare("UPDATE users SET streak = ?, last_study_date = ? WHERE id = ?");
            $stmt_update->bind_param("isi", $streak, $today, $user_id);
            $stmt_update->execute();
        }
    }
}

// XÁC ĐỊNH CẤP ĐỘ ĐANG ĐƯỢC CHỌN (Mặc định là N5)
$current_level = isset($_GET['level']) ? $_GET['level'] : 'N5';
// [THÊM MỚI] Bổ sung 'N3' vào mảng kiểm tra
if (!in_array($current_level, ['N3', 'N4', 'N5'])) {
    $current_level = 'N5';
}
?>

<aside>
    <div class="white-card">
        <h3 class="section-title">CHỌN CẤP ĐỘ</h3>
        <div class="btn-level-group">
            <button class="btn-lvl <?= ($current_level == 'N5') ? 'active' : 'inactive' ?>" 
                    onclick="window.location.href='index.php?page=lessons&level=N5'">N5</button>
                    
            <button class="btn-lvl <?= ($current_level == 'N4') ? 'active' : 'inactive' ?>" 
                    onclick="window.location.href='index.php?page=lessons&level=N4'">N4</button>

            <!-- [THÊM MỚI] Nút N3 -->
            <button class="btn-lvl <?= ($current_level == 'N3') ? 'active' : 'inactive' ?>" 
                    onclick="window.location.href='index.php?page=lessons&level=N3'">
                    <i class="fas fa-crown" style="color: #f39c12; font-size: 12px;"></i> N3
            </button>
        </div>
        
        <h3 class="section-title">MENU CHÍNH</h3>
        <ul class="side-menu">
            <li><a href="index.php?page=home" class="<?= $page=='home'?'active':'' ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li><a href="index.php?page=lessons&level=<?= $current_level ?>" class="<?= $page=='lessons'?'active':'' ?>"><i class="fas fa-book-open"></i> Danh sách bài học</a></li>
            <li><a href="index.php?page=practice" class="<?= $page=='practice'?'active':'' ?>"><i class="fas fa-tasks"></i> Làm bài tập</a></li>
            <li><a href="index.php?page=vocab" class="<?= $page=='vocab'?'active':'' ?>"><i class="fas fa-spell-check"></i> Từ vựng đã học</a></li>
            <li><a href="index.php?page=grammar" class="<?= $page=='grammar'?'active':'' ?>"><i class="fas fa-scroll"></i> Ngữ pháp cơ bản</a></li>
            <li><a href="index.php?page=favorites"><i class="fas fa-heart" style="color: #e74c3c;"></i> Bài học yêu thích</a></li>
            
            <!-- [THÊM MỚI] Mục lịch sử thanh toán -->
            <li><a href="index.php?page=payment_history" class="<?= $page=='payment_history'?'active':'' ?>"><i class="fas fa-receipt" style="color: #f39c12;"></i> Lịch sử thanh toán</a></li>
        </ul>
    </div>

    <div class="white-card">
        <h3 class="section-title">THỐNG KÊ CHUNG</h3>
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
            <div style="background:#e8f5e9; padding: 10px; border-radius: 12px; color:#2ecc71;"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <p style="font-size:12px; color:#999;">Bài đã học</p>
                <b id="stat-lessons">
                    <?= isset($_SESSION['user_id']) ? "{$completed_lessons} / {$total_lessons}" : "-- / --" ?>
                </b>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="background:#fff3e0; padding: 10px; border-radius: 12px; color:#e67e22;"><i class="fas fa-fire"></i></div>
            <div>
                <p style="font-size:12px; color:#999;">Streak</p>
                <b><?= isset($_SESSION['user_id']) ? "{$streak} Ngày 🔥" : "Đăng nhập để xem" ?></b>
            </div>
        </div>
    </div>

    <div class="white-card" style="padding: 10px; text-align: center;">
        <img src="https://i.postimg.cc/6qJMRjrv/meo-than-tai-png.png" alt="Mèo thần tài Sakura" style="width: 80%; margin-bottom: 10px;">
        <p style="font-size: 13px; font-weight: bold; color: #e15f41;">"Kiên trì là sức mạnh !" 🌸</p>
    </div>
</aside>