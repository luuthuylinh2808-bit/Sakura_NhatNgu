<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sakura Nhật Ngữ - Hệ thống học Tiếng Nhật N5-N4</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* 1. KHUNG HEADER CHÍNH */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px; 
        }

        /* 2. STYLE CÁCH ĐIỆU CHO LOGO */
        .logo {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
            align-items: center; 
            margin-top: 2px;
        }

        .logo-text b {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(45deg, #ff6b81, #ff9a9e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .logo-text span {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            font-weight: 800;
            white-space: nowrap;
            letter-spacing: 1px;
        }

        /* 3. MENU ĐIỀU HƯỚNG VÀ NGƯỜI DÙNG */
        .top-nav {
            display: flex;
            align-items: center;
            gap: 20px; 
        }

        .top-nav a {
            white-space: nowrap; 
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 12px; 
            white-space: nowrap; 
        }

        /* 4. CHUÔNG THÔNG BÁO VÀ NÚT BẤM */
        .notification-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        #bell-icon {
            cursor: pointer;
            font-size: 20px;
            color: #666;
            transition: 0.3s;
            padding: 5px;
        }

        #bell-icon:hover { color: var(--pink-primary); }

        .notification-box {
            position: absolute;
            top: 40px;
            right: 0;
            width: 300px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            display: none;
            z-index: 9999;
            padding: 20px;
            border: 1px solid #fff1f3;
            animation: slideDown 0.3s ease-out;
        }

        .notification-box.show { display: block; }

        .btn-login-nav {
            color: var(--pink-primary);
            border: 2px solid var(--pink-primary);
            padding: 6px 18px;
            border-radius: 20px;
            font-weight: 800;
            font-size: 14px;
            transition: 0.3s;
            text-decoration: none !important;
        }

        .btn-login-nav:hover {
            background: var(--pink-primary);
            color: white;
            box-shadow: 0 4px 10px rgba(255, 107, 129, 0.3);
        }

        /* Tùy chỉnh form search để giữ nguyên giao diện */
        .search-box { 
            background: #f5f5f5; 
            border-radius: 25px; 
            padding: 8px 20px; 
            width: 300px; 
            display: flex; 
            align-items: center;
            border: none;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php?page=home" class="logo" style="text-decoration: none;">
        <i class="fas fa-pali-chakra" style="color: #ff6b81; font-size: 28px; margin-right: 10px; text-shadow: 0 2px 10px rgba(255,107,129,0.3);"></i>
        <div class="logo-text">
            <b>Sakura Nhật Ngữ</b>
            <span>Chinh phục N5 - N4</span>
        </div>
    </a>

    <form action="index.php" method="GET" class="search-box">
        <input type="hidden" name="page" value="lessons">
        
        <i class="fas fa-search" style="color:#ccc; margin-right: 10px;"></i>
        <input type="text" name="search" 
               placeholder="Tìm kiếm bài học..." 
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
               style="border:none; background:transparent; outline:none; width: 100%;">
    </form>

    <nav class="top-nav">
        <a href="index.php?page=home" class="<?= $page=='home'?'active':'' ?>">Trang chủ</a>
        <a href="index.php?page=lessons" class="<?= $page=='lessons'?'active':'' ?>">Bài học</a>
        <a href="index.php?page=practice" class="<?= $page=='practice'?'active':'' ?>">Luyện tập</a>
        <a href="index.php?page=vocab" class="<?= $page=='vocab'?'active':'' ?>">Từ vựng</a>
        <a href="index.php?page=grammar" class="<?= $page=='grammar'?'active':'' ?>">Ngữ pháp</a>
        <a href="index.php?page=home#contact-section">Liên hệ</a>
    </nav>

    <div class="user-nav">
        <div class="notification-wrapper">
            <i class="far fa-bell" id="bell-icon"></i>
            <div class="notification-box" id="notification-box">
                <div class="notification-header" style="font-weight:800; margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:5px;">Thông báo</div>
                <div style="text-align:center; padding:15px 0;">
                    <i class="fas fa-bell-slash" style="color:#eee; font-size:30px;"></i>
                    <p style="color:#999; font-size:13px; margin-top:10px;">Không có thông báo mới</p>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['user_name'])): ?>
            <span style="font-weight: 800; color: #444;">Xin chào, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
            
            <a href="index.php?page=profile">
                <img src="https://i.postimg.cc/HnbtXK1r/Chat-GPT-Image-11-19-26-4-thg-4-2026.png" class="avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; cursor: pointer;">
            </a>
            
            <a href="logout.php" class="btn-login-nav" style="margin-left: 5px;">Đăng xuất</a>
        <?php else: ?>
            <a href="index.php?page=login" class="btn-login-nav">Đăng nhập</a>
            <img src="https://i.postimg.cc/HnbtXK1r/Chat-GPT-Image-11-19-26-4-thg-4-2026.png" class="avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; opacity: 0.5;">
        <?php endif; ?>
    </div>
</header>

<script>
    const bellIcon = document.getElementById('bell-icon');
    const notifBox = document.getElementById('notification-box');

    if(bellIcon) {
        bellIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            notifBox.classList.toggle('show');
        });
    }

    document.addEventListener('click', (e) => {
        if (notifBox && !notifBox.contains(e.target) && e.target !== bellIcon) {
            notifBox.classList.remove('show');
        }
    });
</script>