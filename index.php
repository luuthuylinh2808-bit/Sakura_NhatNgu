<?php
session_start();
// Thiết lập điều hướng
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Danh sách các trang hợp lệ (ĐÃ THÊM 'profile')
$allowed_pages = ['home', 'lessons', 'practice', 'vocab', 'grammar', 'login', 'register', 'forgot_password', 'hoc', 'favorites', 'contact', 'profile', 'pricing', 'payment_history', 'listening'];
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// =============================================================
// CHỐT CHẶN AJAX (ĐÃ DỌN SẠCH CODE GÂY LỖI FORM LIÊN HỆ)
// =============================================================
if (isset($_GET['ajax'])) {
    include "pages/{$page}.php";
    exit;
}

$is_auth_page = ($page == 'login' || $page == 'register' || $page == 'forgot_password');
// Bổ sung 'profile' vào danh sách các trang nội dung
// [THÊM MỚI] Bổ sung 'payment_history' và 'pricing'
$is_content_page = ($page == 'lessons' || $page == 'vocab' || $page == 'grammar' || $page == 'practice' || $page == 'hoc' || $page == 'favorites' || $page == 'profile' || $page == 'payment_history' || $page == 'pricing' || $page == 'listening');
?>

<style>
/* =========================================
        HIỆU ỨNG CHUYỂN TRANG MƯỢT MÀ BẰNG AJAX
        ========================================= */
#dynamic-content {
    transition: opacity 0.25s ease-in-out;
    opacity: 1;
    min-height: 85vh;
    min-width: 0;
    /* FIX LỖI ÉP KHUYẾT THẺ KHI BẢN TIN CÓ MARQUEE/SLIDER */
    width: 100%;
}

.content-loading {
    opacity: 0 !important;
}

/* 1. KHUNG CHÍNH (MAIN CONTAINER) - CĂN TOP ĐỂ SIDEBAR KHÔNG BỊ ÉP DÃN NỮA */
.main-container {
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr) 320px;
    gap: 25px;
    align-items: start !important;
    /* Thay 'stretch' thành 'start' */
    max-width: 1350px;
    margin: 20px auto;
    padding: 0 15px;
    box-sizing: border-box;
}

/* 2. CẤU TRÚC ASIDE (SIDEBAR) */
.main-container>aside {
    display: flex !important;
    flex-direction: column !important;
    gap: 20px;
    height: auto !important;
    /* Bỏ 100% để chiều cao vừa khít nội dung */
}

/* 3. TẮT TÍNH NĂNG ÉP DÃN CHO CARD CUỐI */
.main-container>aside>.white-card:last-child {
    flex-grow: 0 !important;
    /* Chuyển từ 1 thành 0 */
    display: block !important;
    margin-bottom: 0 !important;
}

/* 4. ĐỊNH DẠNG LƯỚI CARD */
#grammar-list,
.grid-cards,
#latest-lessons {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    align-items: start;
}

/* 5. TRANG ĐĂNG NHẬP/ĐĂNG KÝ */
<?php if ($is_auth_page): ?>.main-container {
    display: block !important;
    max-width: 500px !important;
    margin: 40px auto !important;
}

<?php endif;

?>header a,
.nav-links a,
.top-menu a {
    font-weight: 600 !important;
}

/* ===== GIAO DIỆN RIÊNG CHO TRANG LUYỆN NGHE ===== */
body:has(.listening-page) .main-container {
    grid-template-columns: 260px minmax(0, 1fr) !important;
}

body:has(.listening-page) .main-container>aside:last-of-type {
    display: none !important;
}

body:has(.listening-page) #dynamic-content {
    min-width: 0;
    width: 100%;
}

/* =========================================
       6. KHỐI LIÊN HỆ TRÀN MÀN HÌNH (FULL-WIDTH)
       ========================================= */
.contact-wrapper-full {
    width: 100%;
    max-width: 1350px;
    margin: 40px auto 20px auto;
    padding: 0 15px;
    box-sizing: border-box;
}

@media (max-width: 1100px) {
    body:has(.listening-page) .main-container {
        grid-template-columns: 1fr !important;
    }

    body:has(.listening-page) .main-container>aside:first-of-type {
        display: none !important;
    }

    .main-container {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<div class="main-container">

    <?php if (!$is_auth_page) include 'includes/sidebar_left.php'; ?>

    <main id="dynamic-content">
        <?php include "pages/{$page}.php"; ?>
    </main>

    <?php if (!$is_auth_page) include 'includes/sidebar_right.php'; ?>

</div>

<!-- ĐÃ ĐƯA KHỐI LIÊN HỆ RA NGOÀI GRID ĐỂ TRÀN ĐẦY ĐỘ RỘNG MÀN HÌNH CÂN ĐỐI -->
<div class="contact-wrapper-full">
    <?php include 'includes/contact.php'; ?>
</div>

<?php include 'includes/footer.php'; ?>