<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}

$current_level = isset($_GET['level']) ? strtoupper($_GET['level']) : ($_SESSION['selected_level'] ?? 'N5');$current_page = isset($_GET['page']) ?$_GET['page'] : 'home';

$is_logged_in = isset($_SESSION['user_id']);$learned_count = 0;
$total_lessons = 0;
$streak_days = 1;
$progress_percent = 0;

if ($is_logged_in) {
    $uid = (int)$_SESSION['user_id'];
    
    // Đếm tổng số bài học
    $res_total =$conn->query("SELECT COUNT(*) FROM lessons");
    if ($res_total) {
        $total_lessons = (int)$res_total->fetch_row()[0];
    }
    
    // Đếm số bài đã học
    $res_learned =$conn->query("SELECT COUNT(DISTINCT id_lesson) FROM user_progress WHERE id = $uid");
    if (!$res_learned) {
        $res_learned =$conn->query("SELECT COUNT(DISTINCT id_lesson) FROM user_progress WHERE id_user = $uid");
    }
    if ($res_learned) {
        $learned_count = (int)$res_learned->fetch_row()[0];
    }
    
    if ($total_lessons > 0) {$progress_percent = round(($learned_count / $total_lessons) * 100);
        if ($progress_percent > 100)$progress_percent = 100;
    }
    
    $streak_days = ($learned_count > 0) ? 2 : 1;
}
?>

<aside class="sidebar-left">
    <!-- CARD 1: CHỌN CẤP ĐỘ & MENU CHÍNH -->
    <div class="sl-box">
        <div class="sl-heading">CHỌN CẤP ĐỘ</div>
        <!-- Dùng button thay vì thẻ <a> để tránh lỗi script AJAX nhét trùng Header vào giữa màn hình -->
        <div class="sl-level-tabs">
            <button type="button" onclick="goToLevelLessons('N5')"
                class="sl-tab <?= $current_level === 'N5' ? 'active' : '' ?>">N5</button>
            <button type="button" onclick="goToLevelLessons('N4')"
                class="sl-tab <?= $current_level === 'N4' ? 'active' : '' ?>">N4</button>
            <button type="button" onclick="goToLevelLessons('N3')"
                class="sl-tab <?= $current_level === 'N3' ? 'active' : '' ?>">
                <i class="fas fa-crown" style="color: #f39c12; font-size: 11px; margin-right: 3px;"></i>N3
            </button>
        </div>

        <div class="sl-heading" style="margin-top: 22px;">MENU CHÍNH</div>
        <ul class="sl-menu">
            <li>
                <a href="index.php?page=home" class="<?= $current_page === 'home' ? 'active' : '' ?>">
                    <i class="fas fa-home" style="color: #ff6b81;"></i> <span>Trang chủ</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=lessons&level=<?= $current_level ?>"
                    class="<?= $current_page === 'lessons' ? 'active' : '' ?>">
                    <i class="fas fa-book-open"></i> <span>Danh sách bài học</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=practice" class="<?= $current_page === 'practice' ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i> <span>Làm bài tập</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=vocab" class="<?= $current_page === 'vocab' ? 'active' : '' ?>">
                    <i class="fas fa-spell-check"></i> <span>Từ vựng đã học</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=grammar" class="<?= $current_page === 'grammar' ? 'active' : '' ?>">
                    <i class="fas fa-scroll"></i> <span>Ngữ pháp cơ bản</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=favorites" class="<?= $current_page === 'favorites' ? 'active' : '' ?>">
                    <i class="fas fa-heart" style="color: #e74c3c;"></i> <span>Bài học yêu thích</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=order_history" class="<?= $current_page === 'order_history' ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar" style="color: #f39c12;"></i> <span>Lịch sử thanh toán</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- CARD 2: THỐNG KÊ CHUNG -->
    <div class="sl-box">
        <div class="sl-heading">THỐNG KÊ CHUNG</div>

        <div class="sl-stat-row">
            <div class="sl-stat-ic" style="background: #eafaf1; color: #2ecc71;">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="sl-stat-info">
                <span class="sl-stat-lbl">Bài đã học</span>
                <?php if ($is_logged_in): ?>
                <strong class="sl-stat-val"><?= $learned_count ?> / <?= $total_lessons ?></strong>
                <?php else: ?>
                <strong class="sl-stat-val">-- / --</strong>
                <?php endif; ?>
            </div>
        </div>

        <div class="sl-stat-row" style="margin-bottom: 0;">
            <div class="sl-stat-ic" style="background: #fef5e7; color: #f39c12;">
                <i class="fas fa-fire"></i>
            </div>
            <div class="sl-stat-info">
                <span class="sl-stat-lbl"><?= $is_logged_in ? 'Chuỗi học tập' : 'Streak' ?></span>
                <?php if ($is_logged_in): ?>
                <strong class="sl-stat-val"><?= $streak_days ?> Ngày 🔥</strong>
                <?php else: ?>
                <a href="index.php?page=login" class="sl-stat-val" style="text-decoration: none; color: #2f3542;">Đăng
                    nhập để xem</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($is_logged_in): ?>
        <div class="sl-prog-container">
            <div class="sl-prog-head">
                <span>Hoàn thành lộ trình</span>
                <b style="color: var(--pink-primary, #ff6b81);"><?= $progress_percent ?>%</b>
            </div>
            <div class="sl-prog-track">
                <div class="sl-prog-bar" style="width: <?= $progress_percent ?>%;"></div>
            </div>
        </div>

        <button type="button" class="sl-btn-reset" onclick="resetUserProgress(event)">
            <i class="fas fa-undo-alt"></i> Đặt lại tiến độ
        </button>
        <?php endif; ?>
    </div>

    <!-- CARD 3: MÈO THẦN TÀI -->
    <div class="sl-box sl-cat-box" onclick="changeCatQuote()" title="Nhấn vào mèo để đổi câu khích lệ!">
        <div class="sl-cat-frame">
            <img src="https://i.postimg.cc/6qJMRjrv/meo-than-tai-png.png" alt="Maneki Neko" id="sl-cat-img">
        </div>
        <div class="sl-cat-caption">
            <span id="sl-cat-text">"Kiên trì là sức mạnh !"</span> 🌸
        </div>
    </div>
</aside>

<style>
.sidebar-left {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 18px;
    box-sizing: border-box;
}

.sl-box {
    background: #ffffff;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(255, 107, 129, 0.05);
    border: 1px solid #fff0f3;
}

.sl-heading {
    font-size: 11px;
    font-weight: 800;
    color: #a4b0be;
    letter-spacing: 0.8px;
    margin-bottom: 12px;
    text-transform: uppercase;
}

.sl-level-tabs {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.sl-tab {
    text-align: center;
    padding: 9px 0;
    border-radius: 10px;
    background: #f8f9fa;
    color: #57606f;
    font-weight: 800;
    font-size: 13px;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #f1f2f6;
    outline: none;
}

.sl-tab:hover {
    background: #fff0f3;
    color: var(--pink-primary, #ff6b81);
}

.sl-tab.active {
    background: var(--pink-primary, #ff6b81);
    color: #ffffff;
    border-color: var(--pink-primary, #ff6b81);
    box-shadow: 0 4px 10px rgba(255, 107, 129, 0.3);
}

.sl-menu {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.sl-menu li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 12px;
    color: #2f3542;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.sl-menu li a i {
    width: 18px;
    text-align: center;
    font-size: 15px;
    color: #2f3542;
}

.sl-menu li a:hover,
.sl-menu li a.active {
    background: #fff0f3;
    color: var(--pink-primary, #ff6b81);
}

.sl-menu li a:hover i,
.sl-menu li a.active i {
    color: var(--pink-primary, #ff6b81);
}

.sl-stat-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 12px;
    border-radius: 12px;
    background: #fcfcfc;
    border: 1px solid #f1f2f6;
    margin-bottom: 10px;
}

.sl-stat-ic {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

.sl-stat-info {
    display: flex;
    flex-direction: column;
}

.sl-stat-lbl {
    font-size: 11px;
    color: #999;
    font-weight: 600;
}

.sl-stat-val {
    font-size: 13px;
    color: #2f3542;
    font-weight: 800;
}

.sl-prog-container {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px dashed #eee;
}

.sl-prog-head {
    display: flex;
    justify-content: space-between;
    font-size: 11.5px;
    color: #666;
    font-weight: 700;
    margin-bottom: 6px;
}

.sl-prog-track {
    width: 100%;
    height: 6px;
    background: #f1f2f6;
    border-radius: 10px;
    overflow: hidden;
}

.sl-prog-bar {
    height: 100%;
    background: linear-gradient(90deg, #ff9a9e, #ff6b81);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.sl-btn-reset {
    margin-top: 12px;
    width: 100%;
    background: #fff5f5;
    border: 1px dashed #ffccd5;
    color: #e74c3c;
    font-size: 11.5px;
    font-weight: 700;
    padding: 8px 10px;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.sl-btn-reset:hover {
    background: #ffe3e3;
}

.sl-cat-box {
    text-align: center;
    padding: 16px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.sl-cat-box:hover {
    transform: translateY(-2px);
}

.sl-cat-frame {
    background: #fff5f7;
    border-radius: 12px;
    padding: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}

.sl-cat-frame img {
    width: 100%;
    max-width: 145px;
    height: auto;
    border-radius: 8px;
    display: block;
    margin: 0 auto;
    transition: transform 0.3s ease;
}

.sl-cat-caption {
    font-size: 12.5px;
    font-weight: 800;
    color: #2f3542;
}
</style>

<script>
// Điều hướng chuẩn xác theo từng cấp độ (N5, N4, N3)
function goToLevelLessons(level) {
    if (typeof loadPage === 'function') {
        // Nếu trang web dùng AJAX để load nội dung
        loadPage('lessons', {
            level: level
        });
    } else {
        // Nếu chuyển trang thông thường
        window.location.href = 'index.php?page=lessons&level=' + encodeURIComponent(level);
    }
}

const leftCatQuotes = [
    '"Kiên trì là sức mạnh !"',
    '"Mỗi ngày 10 từ vựng mới nhé !"',
    '"Nỗ lực không bao giờ phản bội bạn !"',
    '"Chinh phục JLPT cùng Sakura !"',
    '"Ngã bảy lần, đứng dậy tám lần !"'
];

function changeCatQuote() {
    const img = document.getElementById('sl-cat-img');
    const txt = document.getElementById('sl-cat-text');
    if (img) {
        img.style.transform = 'scale(1.08) rotate(-4deg)';
        setTimeout(() => {
            img.style.transform = 'scale(1) rotate(0deg)';
        }, 250);
    }
    if (txt) {
        const pick = leftCatQuotes[Math.floor(Math.random() * leftCatQuotes.length)];
        txt.textContent = pick;
    }
}

function resetUserProgress(e) {
    if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ tiến độ bài học đã hoàn thành để bắt đầu lại từ 0%?')) {
        return;
    }
    const btn = e.currentTarget;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    btn.disabled = true;

    const fd = new FormData();
    fd.append('level', 'ALL');

    fetch('api_reset_progress.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Đã đặt lại tiến độ học tập về 0%!');
                window.location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            }
        })
        .catch(() => {
            alert('Lỗi kết nối máy chủ!');
            btn.innerHTML = oldHtml;
            btn.disabled = false;
        });
}
</script>