<?php
session_start();
// Thiết lập điều hướng
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Danh sách các trang hợp lệ (ĐÃ THÊM 'profile')
$allowed_pages = ['home', 'lessons', 'practice', 'vocab', 'grammar', 'login', 'register', 'forgot_password', 'hoc', 'favorites', 'contact', 'profile', 'pricing', 'payment_history'];
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
$is_content_page = ($page == 'lessons' || $page == 'vocab' || $page == 'grammar' || $page == 'practice' || $page == 'hoc' || $page == 'favorites' || $page == 'profile' || $page == 'payment_history' || $page == 'pricing');
?>

<style>
    /* =========================================
       HIỆU ỨNG CHUYỂN TRANG MƯỢT MÀ BẰNG AJAX
       ========================================= */
    #dynamic-content {
        transition: opacity 0.25s ease-in-out;
        opacity: 1;
        min-height: 85vh;
    }
    .content-loading {
        opacity: 0 !important;
    }
   
    /* 1. KHUNG CHÍNH (MAIN CONTAINER) */
    .main-container {
        display: grid;
        grid-template-columns: 260px 1fr 320px;
        gap: 25px;
        <?php
        $is_long_page = ($page == 'hoc' || $page == 'lessons' || $page == 'vocab');
        if ($is_long_page): ?>
            align-items: start !important;
        <?php else: ?>
            align-items: stretch !important;
        <?php endif; ?>
    }

    /* 2. CẤU TRÚC ASIDE (SIDEBAR) */
    .main-container > aside {
        display: flex !important;
        flex-direction: column !important;
        gap: 20px;
       
        <?php if ($page == 'vocab'): ?>
            height: 850px !important;
        <?php else: ?>
            height: <?= $is_long_page ? 'auto' : '100%' ?> !important;
        <?php endif; ?>
    }

    /* 3. XỬ LÝ CÁC THẺ TRẮNG (WHITE-CARD) */
    .main-container > aside > .white-card:last-child {
        <?php if ($page == 'vocab' || !$is_long_page): ?>
            flex-grow: 1 !important;
        <?php else: ?>
            flex-grow: 0 !important;
        <?php endif; ?>
        margin-bottom: 0 !important;
    }

    /* 4. ĐỊNH DẠNG LƯỚI CARD */
    #grammar-list, .grid-cards, #latest-lessons {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        align-items: start;
    }

    /* 5. TRANG ĐĂNG NHẬP/ĐĂNG KÝ */
    <?php if ($is_auth_page): ?>
        .main-container {
            display: block !important;
            max-width: 500px !important;
            margin: 40px auto !important;
        }
    <?php endif; ?>

    header a, .nav-links a, .top-menu a {
        font-weight: 600 !important;
    }
</style>

<?php include 'includes/header.php'; ?>

<div class="main-container">

    <?php if (!$is_auth_page) include 'includes/sidebar_left.php'; ?>

    <main id="dynamic-content">
        <?php include "pages/{$page}.php"; ?>
    </main>

    <?php if (!$is_auth_page) include 'includes/sidebar_right.php'; ?>

    <?php include 'includes/contact.php'; ?>

</div>

<?php include 'includes/footer.php'; ?>