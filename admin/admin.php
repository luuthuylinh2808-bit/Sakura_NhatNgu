<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?page=login");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// --- KẾT NỐI DATABASE ĐỂ LẤY SỐ LIỆU THỐNG KÊ ---
require_once '../config/database.php';

// Đếm số lượng dữ liệu
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0] ?? 0;
$total_lessons = $conn->query("SELECT COUNT(*) FROM lessons")->fetch_row()[0] ?? 0;
$total_vocab = $conn->query("SELECT COUNT(*) FROM vocabulary")->fetch_row()[0] ?? 0;
$total_contacts = $conn->query("SELECT COUNT(*) FROM contacts")->fetch_row()[0] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Trị - Sakura Web</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
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

        .sidebar a i { font-size: 18px; width: 20px; text-align: center; }

        .sidebar a:hover, .sidebar a.active { 
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
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
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.2);
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            border-bottom: 4px solid transparent;
            transition: 0.3s;
        }

        .stat-card:hover { transform: translateY(-5px); }

        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }

        .quick-action-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px; background: var(--white); border-radius: 15px;
            text-decoration: none; color: var(--text-main); font-weight: 700;
            border: 2px dashed #eee; transition: 0.3s; gap: 10px;
        }
        .quick-action-btn:hover { border-color: var(--pink-primary); color: var(--pink-primary); background: var(--pink-light); }
        .quick-action-btn i { font-size: 24px; color: var(--pink-primary); }

        /* Các style khác giữ nguyên */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        th { background-color: #fafafa; color: var(--text-gray); font-weight: 700; font-size: 14px; text-transform: uppercase; }
        tr:hover td { background-color: #fdfdfd; }
        .btn { padding: 10px 20px; background: var(--pink-primary); color: white; border: none; border-radius: 20px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: 700; font-size: 14px; transition: 0.2s; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline { background: transparent; color: var(--pink-primary); border: 2px solid var(--pink-primary); }
        .btn-outline:hover { background: var(--pink-light); }
        .btn-action { padding: 8px 12px; border-radius: 10px; margin-right: 5px; }
        .btn-edit { background: #fff8e1; color: #f39c12; }
        .btn-delete { background: #fff5f5; color: #e74c3c; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: var(--text-main); font-size: 14px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 15px; border: 1px solid #eee; border-radius: 10px; box-sizing: border-box; font-family: inherit; background: #fafafa; transition: 0.3s; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--pink-primary); outline: none; background: var(--white); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo">
            🌸 Sakura Admin
        </div>
        
        <a href="admin.php?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Tổng quan
        </a>
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
                // --- GIAO DIỆN TỔNG QUAN MỚI ---
                echo "
                <div class='welcome-banner'>
                    <div style='position: relative; z-index: 1;'>
                        <h1 style='margin: 0 0 10px 0; font-weight: 800; font-size: 28px;'>Xin chào, " . htmlspecialchars($_SESSION['user_name']) . "! ✨</h1>
                        <p style='margin: 0; font-size: 16px; opacity: 0.9; max-width: 400px; line-height: 1.5;'>Chúc bạn một ngày làm việc tràn đầy năng lượng. Dưới đây là tóm tắt tình hình hoạt động của Sakura Web hôm nay.</p>
                    </div>
                    <img src='https://i.postimg.cc/6qJMRjrv/meo-than-tai-png.png' alt='Sakura Mascot' style='height: 140px; position: relative; z-index: 1; drop-shadow: 2px 4px 6px rgba(0,0,0,0.1);'>
                </div>

                <h3 style='color: var(--text-main); margin-bottom: 15px;'><i class='fas fa-chart-pie' style='color: var(--pink-primary);'></i> Thống kê dữ liệu</h3>
                <div class='stat-grid'>
                    <div class='stat-card' style='border-bottom-color: #3498db;'>
                        <div class='stat-icon' style='background: #ebf5fb; color: #3498db;'><i class='fas fa-users'></i></div>
                        <div>
                            <div style='font-size: 26px; font-weight: 800; color: #444;'>{$total_users}</div>
                            <div style='font-size: 13px; color: #888; font-weight: 700;'>HỌC VIÊN</div>
                        </div>
                    </div>
                    
                    <div class='stat-card' style='border-bottom-color: #2ecc71;'>
                        <div class='stat-icon' style='background: #eafaf1; color: #2ecc71;'><i class='fas fa-book-open'></i></div>
                        <div>
                            <div style='font-size: 26px; font-weight: 800; color: #444;'>{$total_lessons}</div>
                            <div style='font-size: 13px; color: #888; font-weight: 700;'>BÀI HỌC</div>
                        </div>
                    </div>
                    
                    <div class='stat-card' style='border-bottom-color: #9b59b6;'>
                        <div class='stat-icon' style='background: #f5eef8; color: #9b59b6;'><i class='fas fa-spell-check'></i></div>
                        <div>
                            <div style='font-size: 26px; font-weight: 800; color: #444;'>{$total_vocab}</div>
                            <div style='font-size: 13px; color: #888; font-weight: 700;'>TỪ VỰNG</div>
                        </div>
                    </div>

                    <div class='stat-card' style='border-bottom-color: #f1c40f;'>
                        <div class='stat-icon' style='background: #fef9e7; color: #f1c40f;'><i class='fas fa-envelope'></i></div>
                        <div>
                            <div style='font-size: 26px; font-weight: 800; color: #444;'>{$total_contacts}</div>
                            <div style='font-size: 13px; color: #888; font-weight: 700;'>TIN NHẮN</div>
                        </div>
                    </div>
                </div>

                <h3 style='color: var(--text-main); margin-bottom: 15px; margin-top: 30px;'><i class='fas fa-bolt' style='color: #f39c12;'></i> Thao tác nhanh</h3>
                <div style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;'>
                    <a href='admin.php?page=lessons&action=add' class='quick-action-btn'>
                        <i class='fas fa-plus-circle'></i> Tạo bài học mới
                    </a>
                    <a href='admin.php?page=vocab&action=add' class='quick-action-btn'>
                        <i class='fas fa-language'></i> Thêm từ vựng
                    </a>
                    <a href='admin.php?page=contacts' class='quick-action-btn'>
                        <i class='fas fa-inbox'></i> Kiểm tra hộp thư
                    </a>
                </div>
                ";
                // --- KẾT THÚC GIAO DIỆN TỔNG QUAN ---

            } elseif ($page == 'lessons') {
                include 'manage_lessons.php';
            } elseif ($page == 'vocab') {       
                include 'manage_vocab.php';
            } elseif ($page == 'grammar') {     
                include 'manage_grammar.php';
            } elseif ($page == 'practice') {    
                // File quản lý Bài tập cũ của bạn
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