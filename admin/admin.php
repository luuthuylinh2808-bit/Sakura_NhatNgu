<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?page=login");
    exit;
}
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// KẾT NỐI DATABASE ĐỂ LẤY SỐ LIỆU THỐNG KÊ
require_once '../config/database.php';

// Đếm số lượng dữ liệu chung
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0] ?? 0;
$total_lessons = $conn->query("SELECT COUNT(*) FROM lessons")->fetch_row()[0] ?? 0;
$total_vocab = $conn->query("SELECT COUNT(*) FROM vocabulary")->fetch_row()[0] ?? 0;
$total_contacts = $conn->query("SELECT COUNT(*) FROM contacts")->fetch_row()[0] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản Trị Sakura Web</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
    :root {
        --pink-primary: #ff6b81;
        --pink-light: #ffe6ea;
        --bg-body: #fff0f5;
        --text-main: #444;
        --text-gray: #888;
        --white: #ffffff;
    }

    body {
        font-family: 'Nunito', sans-serif;
        margin: 0;
        display: flex;
        background: var(--bg-body);
        color: var(--text-main);
        height: 100vh;
    }

    /* Thanh Menu Bên Trái */
    .sidebar {
        width: 260px;
        background: var(--white);
        padding: 20px 0;
        box-shadow: 2px 0 15px rgba(255, 107, 129, 0.1);
        display: flex;
        flex-direction: column;
        gap: 5px;
        z-index: 10;
    }

    .sidebar .logo {
        text-align: center;
        color: var(--pink-primary);
        margin-bottom: 30px;
        font-size: 24px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-gray);
        padding: 12px 20px;
        margin: 0 15px;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        transition: 0.3s;
    }

    .sidebar a i {
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .sidebar a:hover,
    .sidebar a.active {
        background: var(--pink-light);
        color: var(--pink-primary);
    }

    /* Nội dung chính */
    .content {
        flex: 1;
        padding: 30px 40px;
        overflow-y: auto;
    }

    h2.page-title {
        color: var(--text-main);
        font-weight: 800;
        margin-top: 0;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card {
        background: var(--white);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        margin-bottom: 20px;
    }

    /* UI Mới cho Dashboard */
    .welcome-banner {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%);
        border-radius: 20px;
        padding: 30px 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(255, 107, 129, 0.2);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--white);
        padding: 20px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        border-bottom: 4px solid transparent;
        transition: 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    th {
        background-color: #fafafa;
        color: var(--text-gray);
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
    }

    tr:hover td {
        background-color: #fdfdfd;
    }

    .btn {
        padding: 10px 20px;
        background: var(--pink-primary);
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-weight: 700;
        font-size: 14px;
        transition: 0.2s;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-outline {
        background: transparent;
        color: var(--pink-primary);
        border: 2px solid var(--pink-primary);
    }

    .btn-outline:hover {
        background: var(--pink-light);
    }

    .btn-action {
        padding: 8px 12px;
        border-radius: 10px;
        margin-right: 5px;
    }

    .btn-edit {
        background: #fff8e1;
        color: #f39c12;
    }

    .btn-delete {
        background: #fff5f5;
        color: #e74c3c;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo">🌸 Sakura Admin</div>

        <a href="admin.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Tổng quan
        </a>

        <a href="admin.php?page=manage_users" class="<?= $page == 'manage_users' ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> Quản lý học viên
        </a>
        <a href="admin.php?page=manage_orders" class="<?= $page == 'manage_orders' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Quản lý doanh thu
        </a>

        <div style="margin: 10px 15px; border-bottom: 1px dashed #ddd;"></div>

        <a href="admin.php?page=lessons" class="<?= $page == 'lessons' ? 'active' : '' ?>">
            <i class="fas fa-book-open"></i> Quản lý bài học
        </a>
        <a href="admin.php?page=vocab" class="<?= $page == 'vocab' ? 'active' : '' ?>">
            <i class="fas fa-spell-check"></i> Quản lý từ vựng
        </a>
        <a href="admin.php?page=grammar" class="<?= $page == 'grammar' ? 'active' : '' ?>">
            <i class="fas fa-scroll"></i> Quản lý ngữ pháp
        </a>
        <a href="admin.php?page=practice" class="<?= $page == 'practice' ? 'active' : '' ?>">
            <i class="fas fa-tasks"></i> Quản lý bài tập
        </a>
        <a href="admin.php?page=listening" class="<?= $page == 'listening' ? 'active' : '' ?>">
            <i class="fas fa-headphones"></i> Quản lý luyện nghe
        </a>
        <a href="admin.php?page=contacts" class="<?= $page == 'contacts' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Tin nhắn liên hệ
        </a>

        <div style="margin-top: auto; padding-bottom: 20px;">
            <a href="../index.php" target="_blank" style="color: #3498db;">
                <i class="fas fa-external-link-alt"></i> Xem trang chủ
            </a>
            <a href="../index.php?page=login" style="color: #e74c3c; margin-top: 5px;">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </div>
    </div>

    <div class="content">
        <?php
if ($page == 'dashboard') {
    // Xử lý bộ lọc Tháng/Năm
    $selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
    $year = date('Y', strtotime($selected_month));
    $month = date('m', strtotime($selected_month));

    // LẤY SỐ LIỆU TỔNG QUAN THEO THÁNG ĐÃ CHỌN
    $total_revenue = $conn->query("SELECT SUM(amount) FROM orders WHERE status='completed' AND DATE_FORMAT(created_at, '%Y-%m') = '$selected_month'")->fetch_row()[0] ?? 0;
    
    // Tổng VIP và Tổng Học Viên
    $total_vip = $conn->query("SELECT COUNT(DISTINCT id_user) FROM user_course_unlocks WHERE level='N3'")->fetch_row()[0] ?? 0;
    $conversion_rate = ($total_users > 0) ? round(($total_vip / $total_users) * 100, 1) : 0;
    
    // Đã sửa 'u.username' thành 'u.fullname' để tương thích với CSDL của bạn
    $recent_orders = $conn->query("SELECT o.*, u.fullname FROM orders o JOIN users u ON o.id_user = u.id WHERE DATE_FORMAT(o.created_at, '%Y-%m') = '$selected_month' ORDER BY o.id_order DESC LIMIT 5");

    // TẠO DỮ LIỆU BIỂU ĐỒ DOANH THU THEO TỪNG NGÀY
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $chart_labels = [];
    $chart_data = [];
    
    for ($d = 1; $d <= $days_in_month; $d++) {
        $date_str = sprintf("%04d-%02d-%02d", $year, $month, $d);
        $chart_labels[] = "$d/$month";
        $daily_rev = $conn->query("SELECT SUM(amount) FROM orders WHERE status='completed' AND DATE(created_at) = '$date_str'")->fetch_row()[0] ?? 0;
        $chart_data[] = (float)$daily_rev; 
    }
    
    $labels_json = json_encode($chart_labels);
    $data_json = json_encode($chart_data);

    echo "
    <div class='welcome-banner'>
        <div style='position: relative; z-index: 1;'>
            <h1 style='margin: 0 0 10px 0; font-weight: 800; font-size: 28px;'>Xin chào, " . htmlspecialchars($_SESSION['user_name'] ?? 'Admin') . " ✨</h1>
            <p style='margin: 0; font-size: 16px; opacity: 0.9; max-width: 500px; line-height: 1.5;'>Tổng quan tình hình tài chính, hoạt động kinh doanh và tiến độ học tập của hệ thống Sakura Web.</p>
        </div>
        <img src='https://i.postimg.cc/6qJMRjrv/meo-than-tai-png.png' alt='Sakura Mascot' style='height: 140px; position: relative; z-index: 1; filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.1));'>
    </div>

    <!-- HÀNG 1: CHỈ SỐ KINH DOANH -->
    <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;'>
        <h3 style='color: var(--text-main); margin:0;'><i class='fas fa-chart-pie' style='color: var(--pink-primary);'></i> Báo cáo tổng quan</h3>
        <form method='GET' action='admin.php' style='display:flex; align-items:center; gap:10px; background:#fff; padding:5px 10px; border-radius:12px; border:1px solid #eee;'>
            <input type='hidden' name='page' value='dashboard'>
            <label style='font-size:13px; font-weight:bold; color:#555;'>Chọn tháng:</label>
            <input type='month' name='month' value='{$selected_month}' style='border:none; outline:none; font-family:inherit; font-weight:600; color:var(--pink-primary); cursor:pointer;' onchange='this.form.submit()'>
        </form>
    </div>

    <div class='stat-grid'>
        <a href='admin.php?page=manage_orders' class='stat-card' style='border-bottom-color: #f39c12; text-decoration: none;'>
            <div class='stat-icon' style='background: #fef5e7; color: #f39c12;'><i class='fas fa-wallet'></i></div>
            <div>
                <div style='font-size: 22px; font-weight: 800; color: #444;'>" . number_format($total_revenue, 0, ',', '.') . "đ</div>
                <div style='font-size: 11px; color: #888; font-weight: 700; text-transform:uppercase;'>Doanh thu ($month/$year)</div>
            </div>
        </a>
        <a href='admin.php?page=manage_users' class='stat-card' style='border-bottom-color: #2ecc71; text-decoration: none;'>
            <div class='stat-icon' style='background: #eafaf1; color: #2ecc71;'><i class='fas fa-crown'></i></div>
            <div>
                <div style='font-size: 24px; font-weight: 800; color: #444;'>{$total_vip}</div>
                <div style='font-size: 11px; color: #888; font-weight: 700;'>HỌC VIÊN N3 (VIP)</div>
            </div>
        </a>
        <a href='admin.php?page=manage_users' class='stat-card' style='border-bottom-color: #3498db; text-decoration: none;'>
            <div class='stat-icon' style='background: #ebf5fb; color: #3498db;'><i class='fas fa-users'></i></div>
            <div>
                <div style='font-size: 24px; font-weight: 800; color: #444;'>{$total_users}</div>
                <div style='font-size: 11px; color: #888; font-weight: 700;'>TỔNG HỌC VIÊN</div>
            </div>
        </a>
        <div class='stat-card' style='border-bottom-color: #9b59b6;'>
            <div class='stat-icon' style='background: #f5eef8; color: #9b59b6;'><i class='fas fa-percent'></i></div>
            <div>
                <div style='font-size: 24px; font-weight: 800; color: #444;'>{$conversion_rate}%</div>
                <div style='font-size: 11px; color: #888; font-weight: 700;'>TỶ LỆ CHUYỂN ĐỔI</div>
            </div>
        </div>
    </div>

  <!-- HÀNG 2: BIỂU ĐỒ & ĐƠN HÀNG MỚI -->
    <div style='display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; align-items: start;'>
        <!-- Cột Biểu đồ -->
        <div class='card' style='margin-bottom: 0; padding-bottom: 15px;'>
            <h3 style='margin-top:0; color:#444; font-size:16px;'><i class='fas fa-chart-line' style='color:#3498db;'></i> Biểu đồ doanh thu tháng $month/$year</h3>
            <!-- Bọc canvas trong div có height cố định để ép chiều cao biểu đồ -->
            <div style='position: relative; height: 300px; width: 100%;'>
                <canvas id='revenueChart'></canvas>
            </div>
        </div>
        
        <!-- Cột Đơn hàng -->
        <div class='card' style='margin-bottom: 0; padding-right: 15px;'>
            <h3 style='margin-top:0; color:#444; font-size:16px;'><i class='fas fa-shopping-cart' style='color:#e74c3c;'></i> Giao dịch mới ($month/$year)</h3>
            
            <!-- Cho khối chứa danh sách cuộn dọc với chiều cao tối đa tương đương biểu đồ bên trái -->
            <div style='display:flex; flex-direction:column; gap:12px; max-height: 300px; overflow-y: auto; padding-right: 5px;'>";
            
            if ($recent_orders && $recent_orders->num_rows > 0) {
                while($ord = $recent_orders->fetch_assoc()) {
                    $status_color = ($ord['status'] == 'completed') ? '#2ecc71' : '#f39c12';
                    $status_txt = ($ord['status'] == 'completed') ? 'Thành công' : 'Chờ xử lý';
                    echo "
                    <div style='padding:10px; border:1px solid #eee; border-radius:10px; display:flex; justify-content:space-between; align-items:center;'>
                        <div>
                            <b style='color:var(--pink-primary); font-size:13px;'>{$ord['fullname']}</b>
                            <div style='font-size:12px; color:#888;'>Khóa N3 Premium</div>
                        </div>
                        <div style='text-align:right;'>
                            <b style='color:#444; font-size:13px;'>" . number_format($ord['amount'], 0, ',', '.') . "đ</b>
                            <div style='font-size:11px; font-weight:bold; color:{$status_color};'>{$status_txt}</div>
                        </div>
                    </div>";
                }
            } else {
                echo "<p style='color:#999; font-size:13px; text-align:center; padding: 20px 0;'>Không có đơn hàng nào.</p>";
            }
            
    echo "  </div>
        </div>
    </div>

    <!-- Tích hợp biểu đồ Chart.js -->
    <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {$labels_json},
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: {$data_json},
                    borderColor: '#ff6b81',
                    backgroundColor: 'rgba(255, 107, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#ff6b81',
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Bắt buộc phải là false để chart tự co giãn theo thẻ div bọc ngoài
                plugins: { 
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(context) { return context.parsed.y.toLocaleString('vi-VN') + ' VNĐ'; } } }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true, 
                        ticks: { 
                            callback: function(value) { 
                                if(value === 0) return '0'; 
                                return (value / 1000).toLocaleString('vi-VN') + 'k'; 
                            } 
                        } 
                    }
                }
            }
        });
    </script>
    <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {$labels_json},
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: {$data_json},
                    borderColor: '#ff6b81',
                    backgroundColor: 'rgba(255, 107, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#ff6b81',
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(context) { return context.parsed.y.toLocaleString('vi-VN') + ' VNĐ'; } } }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { callback: function(value) { if(value === 0) return '0'; return (value / 1000).toLocaleString('vi-VN') + 'k'; } } }
                }
            }
        });
    </script>
    ";

} elseif ($page == 'manage_users') {
    include 'manage_users.php';
} elseif ($page == 'manage_orders') {
    include 'manage_orders.php';
} elseif ($page == 'lessons') {
    include 'manage_lessons.php';
} elseif ($page == 'listening') {
    include 'manage_listening.php';
} elseif ($page == 'vocab') {
    include 'manage_vocab.php';
} elseif ($page == 'grammar') {
    include 'manage_grammar.php';
} elseif ($page == 'practice') {
    include 'manage_practice.php';
} elseif ($page == 'contacts') {
    include 'manage_contacts.php';
} else {
    echo "<h2 class='page-title'>Trang không tồn tại!</h2>";
}
?>
    </div>
</body>

</html>