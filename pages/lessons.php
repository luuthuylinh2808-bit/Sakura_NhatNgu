<div class="white-card"> 
    <?php 
    // 1. Kết nối cơ sở dữ liệu 
    $conn = mysqli_connect("localhost", "root", "", "sakuraweb"); 
    if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error()); 
    
    // TỰ ĐỘNG KÍCH HOẠT PREMIUM NẾU ĐÃ THANH TOÁN NHƯNG CHƯA MỞ KHÓA
    if (isset($_SESSION['user_id'])) {
        $uid_fix = (int)$_SESSION['user_id'];
        
        $chk_fix = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $uid_fix AND level = 'N3'");
        $has_n3 = ($chk_fix && $chk_fix->num_rows > 0);
        
        if (!$has_n3) {
            $completed_order = $conn->query("SELECT * FROM orders WHERE id_user = $uid_fix AND status = 'completed' ORDER BY id_order DESC LIMIT 1")->fetch_assoc();
            
            if ($completed_order) {
                $expiry_fix = null;
                switch ($completed_order['package_type']) {
                    case 'N3_MONTH':    $expiry_fix = date('Y-m-d H:i:s', strtotime('+1 month')); break;
                    case 'N3_6MONTH':   $expiry_fix = date('Y-m-d H:i:s', strtotime('+6 months')); break;
                    case 'N3_LIFETIME': $expiry_fix = '2099-12-31 23:59:59'; break;
                }
                
                if ($expiry_fix) {
                    $conn->query("UPDATE users SET premium_expiry = '$expiry_fix' WHERE id = $uid_fix");
                    $conn->query("INSERT INTO user_course_unlocks (id_user, level) VALUES ($uid_fix, 'N3')");
                }
            }
        }
    }

    // 2. Lấy trình độ (level) và trang hiện tại 
    $current_level = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : 'N5'; 
 
    if (!in_array($current_level, ['N3', 'N4', 'N5'])) { 
        $current_level = 'N5'; 
    }

    // Kiểm tra quyền Premium của User
    $has_premium = false; 
    $uid = 0;
    if (isset($_SESSION['user_id'])) { 
        $uid = (int)$_SESSION['user_id']; 
        $prem = $conn->query("SELECT premium_expiry FROM users WHERE id = $uid")->fetch_assoc(); 
        if (!empty($prem['premium_expiry']) && strtotime($prem['premium_expiry']) > time()) { 
            $has_premium = true; 
        } 
        if (!$has_premium) { 
            $chk = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $uid AND level = '$current_level'"); 
            if ($chk && $chk->num_rows > 0) $has_premium = true; 
        } 
    } 
 
    $limit = 12; 
    $p = isset($_GET['p']) ? (int)$_GET['p'] : 1; 
    if ($p < 1) $p = 1; 
    $offset = ($p - 1) * $limit; 
 
    // 3. Lấy danh sách ID bài học đã yêu thích VÀ BÀI ĐÃ HỌC 
    $fav_lessons = []; 
    $completed_lessons = []; 
 
    if (isset($_SESSION['user_id'])) { 
        $uid = $_SESSION['user_id']; 
 
        $res_fav = $conn->query("SELECT id_lesson FROM favourite_lesson WHERE id = $uid"); 
        while ($r = $res_fav->fetch_assoc()) { 
            $fav_lessons[] = $r['id_lesson']; 
        } 
 
        $res_prog = $conn->query("SELECT id_lesson FROM user_progress WHERE id = $uid"); 
        while ($r = $res_prog->fetch_assoc()) { 
            $completed_lessons[] = $r['id_lesson']; 
        } 
    }
    
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : ''; 
 
    // LOGIC TÌM KIẾM THÔNG MINH 
    $where_sql = ""; 
    if ($search !== '') { 
        $search_number = preg_replace('/[^0-9]/', '', $search); 
        if (!empty($search_number)) { 
            $where_sql = "title LIKE '%$search%' OR order_index = '$search_number'"; 
        } else { 
            $where_sql = "title LIKE '%$search%'"; 
        } 
    } 
 
    // 4. Tính toán tổng số bài để chia trang 
    if ($search !== '') { 
        $count_sql = "SELECT COUNT(*) FROM lessons WHERE $where_sql"; 
        $sql = "SELECT * FROM lessons WHERE $where_sql ORDER BY order_index ASC LIMIT $offset, $limit"; 
    } else { 
        $count_sql = "SELECT COUNT(*) FROM lessons WHERE level = '$current_level'"; 
        $sql = "SELECT * FROM lessons WHERE level = '$current_level' ORDER BY order_index ASC LIMIT $offset, $limit"; 
    } 
    $total_rows = mysqli_query($conn, $count_sql)->fetch_row()[0]; 
    $total_pages = ceil($total_rows / $limit); 
    ?> 

    <style>
        :root {
            --sakura-primary: #ff6b81;
            --sakura-hover: #ff4757;
            --sakura-soft-pink: #fff0f3;
            --sakura-border: #ffe6ea;
            --sakura-gold: #ffa502;
        }

        /* HERO SALES BANNER */
        .sales-hero-banner {
            background: linear-gradient(135deg, #ffffff 0%, #fff0f3 100%);
            border: 1.5px solid var(--sakura-border);
            border-radius: 24px;
            padding: 24px 28px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(255, 107, 129, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }

        .sales-hero-banner::before {
            content: '🌸';
            position: absolute;
            right: -10px;
            bottom: -20px;
            font-size: 110px;
            opacity: 0.12;
            pointer-events: none;
        }

        .perks-list-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 20px;
            margin: 14px 0 0 0;
            padding: 0;
            list-style: none;
        }

        .perks-list-grid li {
            font-size: 13.5px;
            font-weight: 700;
            color: #4b6584;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .check-icon-circle {
            width: 22px;
            height: 22px;
            background: #eafaf1;
            color: #2ed573;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(46, 213, 115, 0.25);
        }

        .rating-proof-box {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #636e72;
            margin-top: 6px;
        }

        .btn-cta-pulse {
            background: linear-gradient(135deg, #ff6b81 0%, #ff4757 100%);
            color: #ffffff !important;
            font-weight: 800;
            font-size: 14.5px;
            padding: 13px 26px;
            border-radius: 16px;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 22px rgba(255, 107, 129, 0.4);
            cursor: pointer;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .btn-cta-pulse:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(255, 71, 87, 0.5);
        }

        /* CARD BÀI HỌC BỊ KHÓA VỚI HIỆU ỨNG BLUR */
        .lesson-card {
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            background: #ffffff;
            border: 1.5px solid var(--sakura-border) !important;
            border-radius: 20px !important;
            padding: 20px !important;
            box-sizing: border-box;
            box-shadow: 0 8px 25px rgba(255, 107, 129, 0.1) !important;
            transition: all 0.25s ease !important;
            overflow: hidden;
        }

        .lesson-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 12px 30px rgba(255, 107, 129, 0.18) !important;
            border-color: var(--sakura-primary) !important;
        }

        .lesson-card.is-locked-card .card-inner-blur {
            filter: blur(3.5px) grayscale(20%);
            opacity: 0.45;
            user-select: none;
            pointer-events: none;
        }

        .locked-card-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(4px);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 18px;
            text-align: center;
            z-index: 20;
        }

        .lock-badge-icon {
            width: 50px;
            height: 50px;
            background: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sakura-gold);
            font-size: 22px;
            box-shadow: 0 6px 18px rgba(255, 165, 0, 0.35);
            margin-bottom: 10px;
        }

        .btn-unlock-cta {
            background: linear-gradient(135deg, #ffa502 0%, #ff7f50 100%);
            color: #ffffff !important;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13.5px;
            cursor: pointer;
            box-shadow: 0 5px 18px rgba(255, 165, 0, 0.4);
            text-decoration: none;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-unlock-cta:hover {
            transform: scale(1.05);
        }

        .badge-preview-tag {
            background: #2ed573;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 10px;
            margin-left: 6px;
        }

        /* POPUP FOMO GÓC DƯỚI */
        .fomo-notification-toast {
            position: fixed;
            bottom: 25px;
            left: 25px;
            background: #ffffff;
            border: 1.5px solid var(--sakura-border);
            border-radius: 18px;
            padding: 12px 18px;
            box-shadow: 0 12px 35px rgba(255, 107, 129, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 99999;
            animation: slideInToast 0.5s ease, fadeOutToast 0.5s ease 4.5s forwards;
        }

        .fomo-avatar-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--sakura-soft-pink);
            color: var(--sakura-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13.5px;
            box-shadow: 0 4px 10px rgba(255, 107, 129, 0.2);
        }

        @keyframes slideInToast {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOutToast {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }
    </style>
 
    <!-- NAV CHỌN LEVEL -->
    <?php if ($search === ''): ?> 
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;"> 
        <a href="index.php?page=lessons&level=N5&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N5' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>">Tiếng Nhật N5</a> 
 
        <a href="index.php?page=lessons&level=N4&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N4' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>">Tiếng Nhật N4</a> 

        <!-- Nút chọn N3 -->
        <a href="index.php?page=lessons&level=N3&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N3' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>"><i class="fas fa-crown" style="color: #f39c12;"></i> Tiếng Nhật N3</a> 
    </div> 
    <?php endif; ?> 

    <!-- CHỈ HIỂN THỊ TIÊU ĐỀ KHI TÌM KIẾM BÀI HỌC -->
    <?php if ($search !== ''): ?>
        <h2><i class='fas fa-search'></i> Kết quả cho: '<?= htmlspecialchars($search) ?>'</h2>
    <?php endif; ?>

    <!-- BANNER HERO VÀ QUYỀN LỢI TÍCH XANH -->
    <?php if ($current_level === 'N3'): ?>
        <div class="sales-hero-banner">
            <div style="max-width: 580px;">
                <h3 style="margin: 0 0 4px 0; font-size: 22px; font-weight: 800; color: #2d3436;">
                    Khóa học Tiếng Nhật N3 Toàn diện
                </h3>

                <!-- BẰNG CHỨNG XÃ HỘI (RATING) -->
                <div class="rating-proof-box">
                    <span style="color: #ffa502;">⭐⭐⭐⭐⭐</span>
                    <span><b>4.9/5</b> (1,240 học viên đã sở hữu)</span>
                </div>

                <ul class="perks-list-grid">
                    <li><span class="check-icon-circle"><i class="fas fa-check"></i></span> Mở khóa 53/53 bài học N3 độc quyền</li>
                    <li><span class="check-icon-circle"><i class="fas fa-check"></i></span> Luyện nghe qua Video HD có phụ đề</li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 10px;">
                <?php if (!$has_premium): ?>
                    <a href="index.php?page=pricing" class="btn-cta-pulse">
                        <i class="fas fa-crown"></i> Nâng cấp Premium ngay <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <span style="color: #2ecc71; font-weight: 800; border: 2px solid #2ecc71; background: #eafaf1; padding: 8px 16px; border-radius: 12px; display: inline-block; font-size: 13.5px;">
                        <i class="fas fa-check-circle"></i> Đã kích hoạt Premium
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
 
    <p style="color:#888; margin-bottom:20px;">Trang <?= $p ?> / <?= $total_pages ?> - Lộ trình theo giáo trình Minna no Nihongo.</p> 
 
    <!-- CARD LUYỆN NGHE BẰNG VIDEO N3 -->
    <?php if ($current_level === 'N3'): ?>
        <style>
            .listening-n3-banner {
                background: #fff0f3;
                border: 1.5px solid var(--sakura-border);
                border-radius: 20px;
                padding: 18px 24px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 6px 20px rgba(255, 107, 129, 0.12);
                transition: all 0.25s ease;
                gap: 15px;
            }
            .listening-n3-banner.unlocked {
                background: linear-gradient(135deg, #fff0f3 0%, #ffe6ea 100%);
                border-color: var(--sakura-primary);
            }
            .listening-n3-banner:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(255, 107, 129, 0.18);
            }
            .listening-n3-info {
                display: flex;
                align-items: center;
                gap: 16px;
            }
            .listening-n3-icon {
                width: 52px;
                height: 52px;
                background: #ffffff;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: var(--sakura-primary);
                box-shadow: 0 4px 10px rgba(255, 107, 129, 0.15);
                flex-shrink: 0;
            }
            .listening-n3-text h3 {
                margin: 0 0 4px 0;
                font-size: 17px;
                color: #2d3436;
                font-weight: 800;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .listening-n3-text p {
                margin: 0;
                font-size: 13px;
                color: #636e72;
            }
            .badge-n3-status {
                font-size: 11px;
                padding: 3px 10px;
                border-radius: 20px;
                font-weight: 700;
            }
            .badge-n3-locked { background: #ffeaa7; color: #d63031; }
            .badge-n3-unlocked { background: #2ecc71; color: #ffffff; }

            .btn-listening-action {
                padding: 10px 22px;
                border-radius: 14px;
                font-weight: 800;
                font-size: 13.5px;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.25s ease;
                white-space: nowrap;
            }
            .btn-n3-lock {
                background: var(--sakura-gold);
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(255, 165, 0, 0.3);
            }
            .btn-n3-lock:hover {
                background: #e67e22;
            }
            .btn-n3-play {
                background: var(--sakura-primary);
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(255, 107, 129, 0.3);
            }
            .btn-n3-play:hover {
                background: var(--sakura-hover);
            }
        </style>

        <?php if ($has_premium): ?>
            <!-- === ĐÃ MUA PREMIUM (UNLOCKED) === -->
            <div class="listening-n3-banner unlocked">
                <div class="listening-n3-info">
                    <div class="listening-n3-icon">
                        <i class="fas fa-headphones-alt"></i>
                    </div>
                    <div class="listening-n3-text">
                        <h3>
                            Luyện nghe qua Video
                            <span class="badge-n3-status badge-n3-unlocked"><i class="fas fa-check-circle"></i> Đã mở khóa</span>
                        </h3>
                        <p>Luyện nghe chép chính tả qua video anime Chiikawa, phụ đề tiếng Nhật thông minh chuẩn mốc thời gian.</p>
                    </div>
                </div>
                <a href="index.php?page=listening&level=N3" class="btn-listening-action btn-n3-play">
                    <i class="fas fa-play-circle"></i> Học ngay
                </a>
            </div>
        <?php else: ?>
            <!-- === CHƯA MUA (LOCKED) === -->
            <div class="listening-n3-banner">
                <div class="listening-n3-info">
                    <div class="listening-n3-icon" style="background: #fff9e6;">
                        <i class="fas fa-lock" style="color: #f39c12;"></i>
                    </div>
                    <div class="listening-n3-text">
                        <h3>
                            Luyện nghe qua Video
                            <span class="badge-n3-status badge-n3-locked"><i class="fas fa-crown"></i> Gói Premium</span>
                        </h3>
                        <p>Yêu cầu nâng cấp tài khoản Premium để mở khóa tính năng luyện nghe độc quyền qua Video có phụ đề!</p>
                    </div>
                </div>
                <a href="index.php?page=pricing" class="btn-listening-action btn-n3-lock">
                    <i class="fas fa-key"></i> Mở khóa ngay
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="grid-cards" id="lessons-container"> 
        <?php 
        $result = mysqli_query($conn, $sql); 
        if ($result && mysqli_num_rows($result) > 0) { 
            while ($row = $result->fetch_assoc()) { 
                $id_lesson = $row['id_lesson']; 
                $title = htmlspecialchars($row['title']); 
                $description = htmlspecialchars($row['description']); 
                $order_index = (int)$row['order_index']; 
                $level = $row['level']; 
 
                // Kiểm tra Premium / Khóa bài
                $is_premium = isset($row['is_premium']) ? (int)$row['is_premium'] : 0;
                $is_locked = ($is_premium === 1 && !$has_premium);

                // CHỈ CHO BÀI 1 HỌC THỬ MIỄN PHÍ
                $is_free_preview = ($order_index === 1);
                if ($is_free_preview) {
                    $is_locked = false; 
                }
 
                $is_fav = in_array($id_lesson, $fav_lessons); 
                $heart_class = $is_fav ? "fas fa-heart" : "far fa-heart"; 
                $heart_color = $is_fav ? "#e74c3c" : "#ccc"; 
 
                $is_completed = in_array($id_lesson, $completed_lessons); 
                $prog_percent = $is_completed ? 100 : 0; 
                $prog_color = $is_completed ? "#2ecc71" : "var(--pink-primary)"; 
                $btn_text = $is_completed ? "<i class='fas fa-check-circle'></i> Ôn tập lại" : "Học ngay"; 
                $btn_bg = $is_completed ? "background: #f1f3f5; color: #333; border: 1px solid #ddd;" : ""; 
                ?> 
                <div class="lesson-card <?= $is_locked ? 'is-locked-card' : '' ?>"> 
                    
                    <!-- LỚP NỘI DUNG THÔNG TIN BÀI HỌC (SẼ BỊ BLUR NẾU BÀI 2+ VÀ CHƯA CÓ PREMIUM) -->
                    <div class="card-inner-blur">
                        <i class="<?php echo $heart_class; ?> btn-heart" 
                            data-id="<?php echo $id_lesson; ?>" 
                            style="position: absolute; top: 15px; right: 15px; font-size: 22px; color: <?php echo $heart_color; ?>; cursor: pointer; transition: 0.3s; z-index: 10;"> 
                        </i> 
                        <div style="display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; align-items: center;">
                            <span class="badge" style="margin: 0;"><?php echo $level; ?> - Bài <?php echo $order_index; ?></span> 

                            <!-- CHỈ HIỂN THỊ "HỌC THỬ MIỄN PHÍ" BÀI 1 -->
                            <?php if ($is_free_preview): ?>
                                <span class="badge-preview-tag">Học thử miễn phí</span>
                            <?php elseif ($is_locked): ?>
                                <span class="badge" style="background: #f39c12; margin-left: 5px;"><i class="fas fa-lock"></i> Premium</span>
                            <?php elseif ($is_premium === 1): ?>
                                <span class="badge" style="background: #2ecc71; margin-left: 5px;"><i class="fas fa-unlock"></i> Đã mở</span>
                            <?php endif; ?> 
                        </div>
                    
                        <h3 style="margin:12px 0 5px; padding-right: 25px;"><?php echo $title; ?></h3> 
                        <p style="color:#888; font-size:14px; flex-grow: 1;"><?php echo $description; ?></p> 
     
                        <div class="progress-container"> 
                            <small style="<?= $is_completed ? 'color:#2ecc71; font-weight:bold;' : '' ?>">Hoàn thành: <?php echo $prog_percent; ?>%</small> 
                            <div class="p-bar"><div class="p-fill" style="width:<?php echo $prog_percent; ?>%; background: <?php echo $prog_color; ?>;"></div></div> 
                        </div> 

                        <a href="index.php?page=hoc&id_lesson=<?php echo $id_lesson; ?>" style="text-decoration: none;"> 
                            <button class="btn-lvl active" style="width:100%; <?php echo $btn_bg; ?>"><?php echo $btn_text; ?></button> 
                        </a> 
                    </div>

                    <!-- LỚP KHÓA LÀM MỜ TÒ MÒ CHO BÀI 2 TRỞ ĐI -->
                    <?php if ($is_locked): ?>
                        <div class="locked-card-overlay">
                            <div class="lock-badge-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h4 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 800; color: #2d3436;">
                                Bài học Premium bị khóa
                            </h4>
                            <p style="margin: 0 0 12px 0; font-size: 12px; color: #636e72; font-weight: 600;">
                                Mở khóa toàn bộ lộ trình N3
                            </p>
                            <a href="index.php?page=pricing" class="btn-unlock-cta">
                                🔑 Mở khóa ngay
                            </a>
                        </div>
                    <?php endif; ?>

                </div> 
                <?php 
            } 
        } else { 
            echo "<div style='grid-column: 1 / -1; text-align: center; padding: 40px;'><p style='color:#999;'>Không tìm thấy bài học nào.</p></div>"; 
        } 
        ?> 
    </div> 
 
    <style> 
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 30px; } 
        .page-link { padding: 10px 18px; border-radius: 12px; border: 1px solid #eee; text-decoration: none; color: #666; font-weight: bold; transition: 0.3s; } 
        .page-link:hover { background: var(--pink-light); color: var(--pink-primary); } 
        .page-link.active { background: var(--pink-primary); color: white; border-color: var(--pink-primary); } 
    </style> 
 
    <?php if ($total_pages > 1): ?> 
    <div class="pagination"> 
        <?php 
        $base_url = "index.php?page=lessons" . ($search !== '' ? "&search=$search" : "&level=$current_level"); 
        ?> 
 
        <?php if ($p > 1): ?> 
            <a href="<?= $base_url ?>&p=<?= $p - 1 ?>" class="page-link"><i class="fas fa-chevron-left"></i></a> 
        <?php endif; ?> 
 
        <?php for ($i = 1; $i <= $total_pages; $i++): ?> 
            <a href="<?= $base_url ?>&p=<?= $i ?>" class="page-link <?= ($i == $p) ? 'active' : '' ?>"><?= $i ?></a> 
        <?php endfor; ?> 
 
        <?php if ($p < $total_pages): ?> 
            <a href="<?= $base_url ?>&p=<?= $p + 1 ?>" class="page-link"><i class="fas fa-chevron-right"></i></a> 
        <?php endif; ?> 
    </div> 
    <?php endif; ?> 
</div> 
<script> 
// Xử lý trái tim yêu thích
document.querySelectorAll('.btn-heart').forEach(btn => { 
    btn.addEventListener('click', function(e) { 
        e.stopPropagation();
        let isLogged = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>; 
        if (!isLogged) { 
            alert("Bạn cần đăng nhập để lưu bài học yêu thích nhé! 🌸"); 
            return; 
        } 
        let lessonId = this.getAttribute('data-id'); 
        let icon = this; 
        let formData = new FormData(); 
        formData.append('id_lesson', lessonId); 
 
        fetch('api_toggle_favorite.php', { method: 'POST', body: formData }) 
            .then(res => res.json()) 
            .then(data => { 
                if (data.status === 'added') { 
                    icon.className = "fas fa-heart btn-heart"; 
                    icon.style.color = '#e74c3c'; 
                } else { 
                    icon.className = "far fa-heart btn-heart"; 
                    icon.style.color = '#ccc'; 
                } 
            }); 
    }); 
}); 

</script>