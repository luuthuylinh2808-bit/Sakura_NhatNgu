<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}

// Đảm bảo cột premium_expiry tồn tại trong bảng users
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'premium_expiry'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN premium_expiry DATETIME DEFAULT NULL");
}

$is_logged_in = isset($_SESSION['user_id']);
$is_n3_unlocked = false;
$is_lifetime = false;
$expiry_display = '';
$fav_lessons = [];

if ($is_logged_in) {
    $uid = (int)$_SESSION['user_id'];
    
    // 1. Kiểm tra trạng thái mở khóa N3 & Thời hạn
    $chk_unlock = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $uid AND level = 'N3'");
    if ($chk_unlock && $chk_unlock->num_rows > 0) {
        $u_row = $conn->query("SELECT premium_expiry FROM users WHERE id = $uid")->fetch_assoc();
        
        if (!empty($u_row['premium_expiry'])) {
            $exp_time = strtotime($u_row['premium_expiry']);
            if ($exp_time > time()) {
                $is_n3_unlocked = true;
                $expiry_display = date('d/m/Y', $exp_time);
                if ((int)date('Y', $exp_time) >= 2090) {
                    $is_lifetime = true;
                }
            } else {
                $conn->query("DELETE FROM user_course_unlocks WHERE id_user = $uid AND level = 'N3'");
                $is_n3_unlocked = false;
            }
        } else {
            $default_exp = date('Y-m-d H:i:s', strtotime('+30 days'));
            $conn->query("UPDATE users SET premium_expiry = '$default_exp' WHERE id = $uid");
            $is_n3_unlocked = true;
            $expiry_display = date('d/m/Y', strtotime($default_exp));
        }
    }

    // 2. Lấy danh sách Bài học yêu thích (Giới hạn gọn hoặc cuộn mượt để không đẩy dài cột phải)
    $sql_fav = "
        SELECT l.id_lesson, l.level, l.order_index, l.title 
        FROM favourite_lesson f 
        INNER JOIN lessons l ON f.id_lesson = l.id_lesson 
        WHERE f.id = $uid 
        ORDER BY l.level ASC, l.order_index ASC 
        LIMIT 6
    ";
    $res_fav = $conn->query($sql_fav);
    if (!$res_fav) {
        $sql_fav_alt = "
            SELECT l.id_lesson, l.level, l.order_index, l.title 
            FROM favourite_lesson f 
            INNER JOIN lessons l ON f.id_lesson = l.id_lesson 
            WHERE f.id_user = $uid 
            ORDER BY l.level ASC, l.order_index ASC 
            LIMIT 6
        ";
        $res_fav = $conn->query($sql_fav_alt);
    }
    if ($res_fav && $res_fav->num_rows > 0) {
        while ($f_row = $res_fav->fetch_assoc()) {
            $fav_lessons[] = $f_row;
        }
    }
}

// 3. Lấy danh sách câu hỏi cho khối "Luyện tập nhanh"
$quick_quizzes = [];

// ✅ SỬA: Dùng bảng 'exercise' (đúng tên bảng trong CSDL) + cột option_A/B/C/D (chữ HOA)
try {
    $q_res = $conn->query("SELECT id_ex, question, option_A, option_B, option_C, option_D, correct_option 
                           FROM exercise 
                           ORDER BY RAND() 
                           LIMIT 10");
    
    if ($q_res && $q_res->num_rows > 0) {
        while ($q = $q_res->fetch_assoc()) {
            $quick_quizzes[] = [
                'q'   => $q['question'],
                'a'   => $q['option_A'],
                'b'   => $q['option_B'],
                'c'   => $q['option_C'],
                'd'   => $q['option_D'],
                'ans' => strtoupper(trim($q['correct_option'] ?? 'A'))
            ];
        }
    }
} catch (Throwable $e) {
    // Nếu bảng exercise chưa có dữ liệu hoặc lỗi SQL, bỏ qua để không sập trang
    $quick_quizzes = [];
}

if (empty($quick_quizzes)) {
    $quick_quizzes = [
        [
            'q' => '物価は上がる（ ）だ。',
            'a' => '一方',
            'b' => 'つつ',
            'c' => 'そう',
            'd' => 'よう',
            'ans' => 'A'
        ],
        [
            'q' => '毎日日本語を勉強する（ ）にしています。',
            'a' => 'こと',
            'b' => 'よう',
            'c' => 'もの',
            'd' => 'ところ',
            'ans' => 'B'
        ],
        [
            'q' => '雨が降っている（ ）、出かけません。',
            'a' => 'から',
            'b' => 'ても',
            'c' => 'のに',
            'd' => 'たり',
            'ans' => 'A'
        ],
        [
            'q' => '今何時（ ）分かりますか。',
            'a' => 'を',
            'b' => 'が',
            'c' => 'か',
            'd' => 'に',
            'ans' => 'C'
        ],
        [
            'q' => '日本へ行く（ ）に、ビザをもらわなければなりません。',
            'a' => 'あと',
            'b' => 'まえ',
            'c' => 'とき',
            'd' => 'ながら',
            'ans' => 'B'
        ]
    ];
}
$quizzes_json = json_encode($quick_quizzes, JSON_UNESCAPED_UNICODE);
?>

<aside class="sidebar-right">
    <!-- KHỐI 1: LUYỆN TẬP NHANH -->
    <div class="sr-card sr-quiz-card">
        <div class="sr-quiz-header">
            <i class="fas fa-bolt" style="color: #f39c12;"></i>
            <span>Luyện tập nhanh</span>
        </div>

        <div id="qz-question" class="sr-quiz-question">物価は上がる（ ）だ。</div>

        <div class="sr-quiz-options" id="qz-options">
            <button type="button" class="sr-opt-btn" data-opt="A" onclick="selectQuickOption('A')">
                <span class="opt-letter">A.</span> <span class="opt-text" id="qz-opt-A">一方</span>
            </button>
            <button type="button" class="sr-opt-btn" data-opt="B" onclick="selectQuickOption('B')">
                <span class="opt-letter">B.</span> <span class="opt-text" id="qz-opt-B">つつ</span>
            </button>
            <button type="button" class="sr-opt-btn" data-opt="C" onclick="selectQuickOption('C')">
                <span class="opt-letter">C.</span> <span class="opt-text" id="qz-opt-C">そう</span>
            </button>
            <button type="button" class="sr-opt-btn" data-opt="D" onclick="selectQuickOption('D')">
                <span class="opt-letter">D.</span> <span class="opt-text" id="qz-opt-D">よう</span>
            </button>
        </div>

        <div id="qz-feedback" class="sr-quiz-feedback" style="display: none;"></div>

        <div class="sr-quiz-actions">
            <button type="button" class="sr-btn-retry" onclick="retryQuickQuiz()">
                <i class="fas fa-undo"></i> Làm lại
            </button>
            <button type="button" class="sr-btn-next" onclick="nextQuickQuiz()">
                Câu tiếp <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- KHỐI 2: BOX BÀI HỌC YÊU THÍCH (TỐI ƯU CHIỀU CAO CÓ THANH CUỘN MƯỢT) -->
    <div class="sr-card sr-fav-card">
        <div class="sr-fav-header">
            <span><i class="fas fa-heart" style="color: #ff6b81; margin-right: 6px;"></i> Bài học yêu thích</span>
            <?php if ($is_logged_in && count($fav_lessons) > 0): ?>
            <a href="index.php?page=favorites" class="sr-fav-more">Xem tất cả</a>
            <?php endif; ?>
        </div>

        <?php if (!$is_logged_in): ?>
        <div class="sr-fav-empty">
            <i class="far fa-heart"></i>
            <p>Hãy <a href="index.php?page=login">đăng nhập</a> để lưu bài học yêu thích nhé!</p>
        </div>
        <?php elseif (count($fav_lessons) > 0): ?>
        <div class="sr-fav-list">
            <?php foreach ($fav_lessons as $fav): ?>
            <a href="index.php?page=lesson_detail&id=<?= (int)$fav['id_lesson'] ?>" class="sr-fav-item">
                <span class="sr-fav-badge"><?= htmlspecialchars($fav['level']) ?> - Bài
                    <?= (int)$fav['order_index'] ?></span>
                <span class="sr-fav-title"><?= htmlspecialchars($fav['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="sr-fav-empty">
            <i class="far fa-heart"></i>
            <p>Chưa có bài học yêu thích nào.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- KHỐI 3: BOX QUẢNG CÁO / GIA HẠN N3 NỔI BẬT -->
    <?php if ($is_lifetime): ?>
    <div class="sr-promo-box sr-promo-lifetime">
        <div class="sr-promo-badge" style="background: #27ae60;">
            <i class="fas fa-check-circle"></i> ĐÃ SỞ HỮU VĨNH VIỄN
        </div>
        <h3 class="sr-promo-title" style="color: #1e8449;">Thành Viên VIP Trọn Đời</h3>
        <p class="sr-promo-desc">Bạn đã mở khóa toàn bộ đặc quyền khóa học N3 mãi mãi!</p>
        <div class="sr-lifetime-tag">
            <i class="fas fa-infinity"></i> Hạn sử dụng: Vĩnh viễn
        </div>
    </div>

    <?php elseif ($is_n3_unlocked): ?>
    <div class="sr-promo-box sr-promo-renew">
        <div class="sr-promo-badge" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <i class="fas fa-gem"></i> GIA HẠN GÓI VIP N3
        </div>
        <h3 class="sr-promo-title">Tiếp Tục Chinh Phục N3</h3>
        <p class="sr-promo-desc" style="margin-bottom: 6px;">
            Hạn hiện tại: <b style="color: #d35400;"><?= $expiry_display ?></b>
        </p>
        <div class="sr-stack-note">
            <i class="fas fa-calendar-plus"></i> Đăng ký trước hạn sẽ được <b>cộng dồn ngày</b>!
        </div>
        <button type="button" onclick="goToPricingPage()" class="sr-promo-btn"
            style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); box-shadow: 0 4px 12px rgba(243, 156, 18, 0.35);">
            <span>Gia hạn gói ngay</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <?php else: ?>
    <div class="sr-promo-box sr-promo-upgrade">
        <div class="sr-promo-badge" style="background: linear-gradient(135deg, #ff6b81, #ff4757);">
            <i class="fas fa-crown"></i> KHÓA N3 PREMIUM
        </div>
        <h3 class="sr-promo-title">Chinh Phục N3 Cấp Tốc</h3>
        <p class="sr-promo-desc">Mở khóa trọn bộ bài giảng, từ vựng & bài tập N3 độc quyền!</p>
        <div class="sr-price-banner">
            <span class="sr-old-price">149.000đ</span>
            <span class="sr-new-price">89.000đ<small>/tháng</small></span>
        </div>
        <button type="button" onclick="goToPricingPage()" class="sr-promo-btn"
            style="background: linear-gradient(135deg, #ff6b81 0%, #ff4757 100%); box-shadow: 0 4px 12px rgba(255, 107, 129, 0.35);">
            <span>Nâng cấp ngay</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- KHỐI 4: ĐỒNG HỒ ĐẾM NGƯỢC JLPT (ĐÃ TỐI ƯU KHOẢNG CÁCH KHÔNG BỊ ĐÈ) -->
    <div class="sr-card sr-countdown-card">
        <div class="sr-card-head">
            <span><i class="far fa-calendar-alt" style="color: var(--pink-primary, #ff6b81); margin-right: 4px;"></i>
                Đếm ngược JLPT</span>
            <span class="sr-date-pill">06/12/2026</span>
        </div>
        <div class="sr-exam-name">Kỳ thi JLPT T12/2026</div>
        <div class="sr-timer-grid">
            <div class="sr-time-item">
                <span class="sr-time-num" id="jlpt-d">30</span>
                <span class="sr-time-lbl">NGÀY</span>
            </div>
            <div class="sr-time-sep">:</div>
            <div class="sr-time-item">
                <span class="sr-time-num" id="jlpt-h">00</span>
                <span class="sr-time-lbl">GIỜ</span>
            </div>
            <div class="sr-time-sep">:</div>
            <div class="sr-time-item">
                <span class="sr-time-num" id="jlpt-m">00</span>
                <span class="sr-time-lbl">PHÚT</span>
            </div>
            <div class="sr-time-sep">:</div>
            <div class="sr-time-item">
                <span class="sr-time-num" id="jlpt-s">00</span>
                <span class="sr-time-lbl">GIÂY</span>
            </div>
        </div>
        <div class="sr-card-foot">
            <span><i class="fas fa-arrow-circle-right" style="color: #747d8c;"></i> JLPT T7/2027</span>
            <b style="color: var(--pink-primary, #ff6b81);">Còn 241 ngày</b>
        </div>
    </div>
</aside>

<style>
.sidebar-right {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 14px;
    /* Thu gọn khoảng cách giữa các card để vừa khít chiều cao trang chủ */
    box-sizing: border-box;
    position: relative;
    z-index: 2;
}

.sr-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 16px;
    box-shadow: 0 4px 15px rgba(255, 107, 129, 0.05);
    border: 1px solid #fff0f3;
}

/* 1. CSS KHỐI LUYỆN TẬP NHANH */
.sr-quiz-header {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13.5px;
    font-weight: 800;
    color: #e67e22;
    margin-bottom: 10px;
}

.sr-quiz-question {
    font-size: 14px;
    font-weight: 800;
    color: #2f3542;
    margin-bottom: 10px;
    line-height: 1.4;
}

.sr-quiz-options {
    display: flex;
    flex-direction: column;
    gap: 7px;
    margin-bottom: 10px;
}

.sr-opt-btn {
    width: 100%;
    text-align: left;
    background: #ffffff;
    border: 1px solid #eee;
    border-radius: 9px;
    padding: 8px 12px;
    font-size: 13px;
    color: #2f3542;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sr-opt-btn:hover:not(:disabled) {
    border-color: #f39c12;
    background: #fffdf9;
}

.sr-opt-btn .opt-letter {
    color: #f39c12;
    font-weight: 800;
}

.sr-opt-btn .opt-text {
    font-weight: 700;
    color: #2f3542;
}

.sr-opt-btn.correct {
    background: #eafaf1 !important;
    border-color: #2ecc71 !important;
    color: #27ae60 !important;
}

.sr-opt-btn.wrong {
    background: #fff5f5 !important;
    border-color: #e74c3c !important;
    color: #c0392b !important;
}

.sr-quiz-feedback {
    font-size: 11.5px;
    font-weight: 700;
    padding: 6px 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
}

.sr-quiz-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.sr-btn-retry {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    color: #747d8c;
    border-radius: 9px;
    padding: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
}

.sr-btn-retry:hover {
    background: #eef1f5;
    color: #2f3542;
}

.sr-btn-next {
    background: #fff9eb;
    border: 1px solid #f9ca24;
    color: #e67e22;
    border-radius: 9px;
    padding: 8px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: 0.2s;
}

.sr-btn-next:hover {
    background: #f39c12;
    color: #ffffff;
}

/* 2. CSS KHỐI BÀI HỌC YÊU THÍCH (GIỚI HẠN CHIỀU CAO ĐỂ KHÔNG ĐẨY TRÀN ĐỒNG HỒ) */
.sr-fav-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13.5px;
    font-weight: 800;
    color: #2f3542;
    margin-bottom: 10px;
}

.sr-fav-more {
    font-size: 11px;
    color: var(--pink-primary, #ff6b81);
    text-decoration: none;
    font-weight: 700;
}

.sr-fav-more:hover {
    text-decoration: underline;
}

.sr-fav-list {
    display: flex;
    flex-direction: column;
    gap: 7px;
    max-height: 125px;
    /* Hiển thị vừa vặn 2 bài và cho phép cuộn mượt nếu có nhiều bài hơn */
    overflow-y: auto;
    padding-right: 3px;
}

.sr-fav-list::-webkit-scrollbar {
    width: 4px;
}

.sr-fav-list::-webkit-scrollbar-thumb {
    background: #ffccd5;
    border-radius: 10px;
}

.sr-fav-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 8px 11px;
    border-radius: 10px;
    background: #fff9fa;
    border: 1px solid #ffeef1;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sr-fav-item:hover {
    background: #fff0f3;
    border-color: #ffccd5;
}

.sr-fav-badge {
    align-self: flex-start;
    background: var(--pink-primary, #ff6b81);
    color: #ffffff;
    font-size: 9.5px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 5px;
}

.sr-fav-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #2f3542;
    line-height: 1.3;
}

.sr-fav-empty {
    text-align: center;
    padding: 12px 8px;
    background: #fcfcfc;
    border: 1px dashed #ffd8e0;
    border-radius: 10px;
}

.sr-fav-empty i {
    font-size: 20px;
    color: #ffb8c6;
    margin-bottom: 5px;
    display: block;
}

.sr-fav-empty p {
    margin: 0;
    font-size: 11.5px;
    color: #747d8c;
    line-height: 1.4;
}

.sr-fav-empty a {
    color: var(--pink-primary, #ff6b81);
    font-weight: 800;
    text-decoration: none;
}

/* 3. CSS BOX QUẢNG CÁO N3 GỌN GÀNG NỔI BẬT */
.sr-promo-box {
    border-radius: 18px;
    padding: 15px 15px;
    text-align: center;
    position: relative;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.sr-promo-box:hover {
    transform: translateY(-2px);
}

.sr-promo-upgrade {
    background: linear-gradient(180deg, #ffffff 0%, #fff0f4 100%);
    border: 2px solid #ffccd5;
    box-shadow: 0 6px 18px rgba(255, 107, 129, 0.12);
}

.sr-promo-renew {
    background: linear-gradient(180deg, #ffffff 0%, #fff9eb 100%);
    border: 2px solid #fde3a7;
    box-shadow: 0 6px 18px rgba(243, 156, 18, 0.12);
}

.sr-promo-lifetime {
    background: linear-gradient(180deg, #ffffff 0%, #eafaf1 100%);
    border: 2px solid #a9dfbf;
    box-shadow: 0 6px 18px rgba(46, 204, 113, 0.12);
}

.sr-promo-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #fff;
    font-size: 10.5px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 8px;
}

.sr-promo-title {
    font-size: 15px;
    font-weight: 800;
    color: #2f3542;
    margin: 0 0 5px 0;
}

.sr-promo-desc {
    font-size: 12px;
    color: #57606f;
    line-height: 1.4;
    margin: 0 0 10px 0;
}

.sr-price-banner {
    background: #ffffff;
    border: 1.5px dashed #ffa8b6;
    border-radius: 10px;
    padding: 5px 8px;
    margin-bottom: 10px;
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 8px;
}

.sr-old-price {
    text-decoration: line-through;
    color: #a4b0be;
    font-size: 11.5px;
}

.sr-new-price {
    color: var(--pink-primary, #ff6b81);
    font-size: 18px;
    font-weight: 900;
}

.sr-new-price small {
    font-size: 10.5px;
    color: #747d8c;
    font-weight: 600;
}

.sr-stack-note {
    background: #fff3cd;
    color: #856404;
    border: 1px dashed #ffeeba;
    padding: 5px 8px;
    border-radius: 8px;
    font-size: 11px;
    margin-bottom: 10px;
}

.sr-lifetime-tag {
    background: #d4efdf;
    color: #1e8449;
    font-weight: 800;
    font-size: 11.5px;
    padding: 6px 10px;
    border-radius: 8px;
}

.sr-promo-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 10px;
    color: #ffffff !important;
    font-weight: 800;
    font-size: 12.5px;
    font-family: inherit;
    cursor: pointer;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.sr-promo-btn:hover {
    filter: brightness(1.06);
    transform: translateY(-1px);
}

/* 4. CSS ĐỒNG HỒ ĐẾM NGƯỢC */
.sr-countdown-card {
    padding: 14px 16px;
}

.sr-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11.5px;
    font-weight: 700;
    color: #747d8c;
    margin-bottom: 8px;
}

.sr-date-pill {
    background: #fff0f3;
    color: var(--pink-primary, #ff6b81);
    padding: 2px 7px;
    border-radius: 7px;
    font-size: 10.5px;
    font-weight: 800;
}

.sr-exam-name {
    text-align: center;
    font-size: 13px;
    font-weight: 800;
    color: #2f3542;
    margin-bottom: 8px;
}

.sr-timer-grid {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    background: #fff8f9;
    padding: 8px 6px;
    border-radius: 10px;
    border: 1px solid #ffe6ea;
    margin-bottom: 8px;
}

.sr-time-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 34px;
}

.sr-time-num {
    font-size: 16px;
    font-weight: 900;
    color: var(--pink-primary, #ff6b81);
    line-height: 1.1;
}

.sr-time-lbl {
    font-size: 8px;
    font-weight: 800;
    color: #a4b0be;
    margin-top: 2px;
}

.sr-time-sep {
    font-size: 14px;
    font-weight: 800;
    color: #ffccd5;
    margin-top: -6px;
}

.sr-card-foot {
    display: flex;
    justify-content: space-between;
    font-size: 10.5px;
    color: #747d8c;
    border-top: 1px dashed #eee;
    padding-top: 8px;
}
</style>

<script>
// ĐIỀU HƯỚNG SANG TRANG BẢNG GIÁ AN TOÀN
function goToPricingPage() {
    if (typeof loadPage === 'function') {
        loadPage('pricing');
    } else {
        window.location.href = 'index.php?page=pricing';
    }
}

// 1. LOGIC LUYỆN TẬP NHANH
const quickQuizList = <?= $quizzes_json ?>;
let currentQuizIdx = 0;

function renderQuickQuiz() {
    if (!quickQuizList || quickQuizList.length === 0) return;
    const item = quickQuizList[currentQuizIdx];
    const qEl = document.getElementById('qz-question');
    const fbEl = document.getElementById('qz-feedback');
    if (!qEl) return;

    qEl.textContent = item.q;
    document.getElementById('qz-opt-A').textContent = item.a;
    document.getElementById('qz-opt-B').textContent = item.b;
    document.getElementById('qz-opt-C').textContent = item.c;
    document.getElementById('qz-opt-D').textContent = item.d;

    if (fbEl) fbEl.style.display = 'none';

    const buttons = document.querySelectorAll('#qz-options .sr-opt-btn');
    buttons.forEach(btn => {
        btn.disabled = false;
        btn.classList.remove('correct', 'wrong');
    });
}

function selectQuickOption(chosen) {
    const item = quickQuizList[currentQuizIdx];
    const correctAns = (item.ans || 'A').toUpperCase();
    const buttons = document.querySelectorAll('#qz-options .sr-opt-btn');
    const fbEl = document.getElementById('qz-feedback');

    buttons.forEach(btn => {
        const opt = btn.getAttribute('data-opt');
        btn.disabled = true;
        if (opt === correctAns) {
            btn.classList.add('correct');
        } else if (opt === chosen && chosen !== correctAns) {
            btn.classList.add('wrong');
        }
    });

    if (fbEl) {
        fbEl.style.display = 'block';
        if (chosen === correctAns) {
            fbEl.style.background = '#eafaf1';
            fbEl.style.color = '#27ae60';
            fbEl.innerHTML = '🎉 Chính xác! Đáp án đúng là <b>' + correctAns + '</b>.';
        } else {
            fbEl.style.background = '#fff5f5';
            fbEl.style.color = '#e74c3c';
            fbEl.innerHTML = '💡 Chưa đúng! Đáp án đúng là <b>' + correctAns + '</b>.';
        }
    }
}

function retryQuickQuiz() {
    renderQuickQuiz();
}

function nextQuickQuiz() {
    currentQuizIdx = (currentQuizIdx + 1) % quickQuizList.length;
    renderQuickQuiz();
}

renderQuickQuiz();

// 2. LOGIC ĐỒNG HỒ ĐẾM NGƯỢC JLPT
(function initJlptCountdown() {
    const targetTime = new Date("2026-12-06T08:00:00+07:00").getTime();

    function updateClock() {
        const dEl = document.getElementById('jlpt-d');
        const hEl = document.getElementById('jlpt-h');
        const mEl = document.getElementById('jlpt-m');
        const sEl = document.getElementById('jlpt-s');
        if (!dEl || !hEl || !mEl || !sEl) return;

        const now = new Date().getTime();
        const diff = Math.max(0, targetTime - now);

        const d = Math.floor(diff / (1000 * 60 * 60 * 24));
        const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);

        dEl.textContent = String(d).padStart(2, '0');
        hEl.textContent = String(h).padStart(2, '0');
        mEl.textContent = String(m).padStart(2, '0');
        sEl.textContent = String(s).padStart(2, '0');
    }
    updateClock();
    setInterval(updateClock, 1000);
})();
</script>