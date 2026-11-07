<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}

// Đảm bảo cột premium_expiry tồn tại
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'premium_expiry'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN premium_expiry DATETIME DEFAULT NULL");
}

$is_logged_in = isset($_SESSION['user_id']);
$is_n3_unlocked = false;
$is_lifetime = false;
$current_expiry_str = '';

if ($is_logged_in) {
    $uid = (int)$_SESSION['user_id'];
    $chk_unlock = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $uid AND level = 'N3'");
    if ($chk_unlock && $chk_unlock->num_rows > 0) {
        $u_info = $conn->query("SELECT premium_expiry FROM users WHERE id = $uid")->fetch_assoc();
        if (!empty($u_info['premium_expiry']) && strtotime($u_info['premium_expiry']) > time()) {
            $is_n3_unlocked = true;
            $current_expiry_str = date('d/m/Y', strtotime($u_info['premium_expiry']));
            if ((int)date('Y', strtotime($u_info['premium_expiry'])) >= 2090) {
                $is_lifetime = true;
            }
        }
    }
}

$btn_label = $is_n3_unlocked ? "Gia hạn thêm (+Cộng dồn)" : "Đăng ký gói này";
?>

<div class="pricing-container">
    <div class="pricing-top-banner">
        <div class="pricing-badge"><i class="fas fa-crown"></i> SAKURA N3 PREMIUM</div>
        <h2 class="pricing-main-title">Đầu Tư Cho Tương Lai Tiếng Nhật Của Bạn</h2>
        <p class="pricing-sub-title">
            Mở khóa toàn bộ lộ trình N3 chuyên sâu, luyện nghe video và bài tập trắc nghiệm.
            <?php if ($is_lifetime): ?>
            <br><b style="color: #27ae60;">🎉 Tài khoản của bạn đã sở hữu gói VIP N3 Trọn Đời!</b>
            <?php elseif ($is_n3_unlocked): ?>
            <br><b style="color: #e67e22;">⚡ Gói N3 hiện tại của bạn còn hạn đến <?= $current_expiry_str ?>. Nếu đăng ký
                thêm, thời gian mới sẽ được cộng dồn vào hạn cũ!</b>
            <?php endif; ?>
        </p>
    </div>

    <?php if (!$is_logged_in): ?>
    <div class="pricing-login-alert">
        <i class="fas fa-exclamation-triangle"></i>
        Bạn cần <a href="index.php?page=login">Đăng nhập</a> trước khi thực hiện đăng ký gói khóa học N3.
    </div>
    <?php endif; ?>

    <div class="pricing-grid">
        <!-- GÓI 1 THÁNG -->
        <div class="p-card">
            <div class="p-card-head">
                <span class="p-plan-name">Gói Tiết Kiệm (1 Tháng)</span>
                <div class="p-price">89.000đ<span>/30 ngày</span></div>
                <div class="p-old-price">Giá gốc: 149.000đ</div>
            </div>
            <ul class="p-features">
                <li><i class="fas fa-check-circle"></i> Mở khóa toàn bộ bài học N3 trong 30 ngày</li>
                <li><i class="fas fa-check-circle"></i> Học từ vựng, ngữ pháp & luyện nghe N3</li>
                <li><i class="fas fa-check-circle"></i> Lưu giữ tiến độ học tập vĩnh viễn</li>
                <li><i class="fas fa-check-circle"></i> Hỗ trợ cộng dồn ngày khi gia hạn sớm</li>
            </ul>
            <?php if ($is_lifetime): ?>
            <button class="p-btn p-btn-disabled" disabled><i class="fas fa-check"></i> Đã sở hữu vĩnh viễn</button>
            <?php else: ?>
            <button type="button" class="p-btn <?= $is_n3_unlocked ? 'p-btn-renew' : '' ?>"
                onclick="openVietQRModal('N3_MONTH', 'Gói N3 1 Tháng (+30 ngày)', 89000)">
                <i class="fas fa-bolt"></i> <?= $btn_label ?>
            </button>
            <?php endif; ?>
        </div>

        <!-- GÓI 6 THÁNG (NỔI BẬT) -->
        <div class="p-card p-card-popular">
            <div class="p-popular-tag">ĐƯỢC CHỌN NHIỀU NHẤT</div>
            <div class="p-card-head">
                <span class="p-plan-name">Gói Chinh Phục (6 Tháng)</span>
                <div class="p-price">399.000đ<span>/180 ngày</span></div>
                <div class="p-old-price">Giá gốc: 699.000đ</div>
            </div>
            <ul class="p-features">
                <li><i class="fas fa-check-circle"></i> <b>Đầy đủ quyền lợi khóa N3 suốt 6 tháng</b></li>
                <li><i class="fas fa-check-circle"></i> Bám sát lộ trình ôn thi JLPT T12 & T7</li>
                <li><i class="fas fa-check-circle"></i> Ưu tiên giải đáp thắc mắc trong Hộp thư</li>
                <li><i class="fas fa-check-circle"></i> Hỗ trợ cộng dồn 180 ngày vào hạn hiện tại</li>
            </ul>
            <?php if ($is_lifetime): ?>
            <button class="p-btn p-btn-disabled" disabled><i class="fas fa-check"></i> Đã sở hữu vĩnh viễn</button>
            <?php else: ?>
            <button type="button" class="p-btn <?= $is_n3_unlocked ? 'p-btn-renew' : '' ?>"
                onclick="openVietQRModal('N3_6MONTH', 'Gói N3 6 Tháng (+180 ngày)', 399000)">
                <i class="fas fa-crown"></i> <?= $btn_label ?>
            </button>
            <?php endif; ?>
        </div>

        <!-- GÓI TRỌN ĐỜI -->
        <div class="p-card">
            <div class="p-card-head">
                <span class="p-plan-name">Gói Vĩnh Viễn (Trọn Đời)</span>
                <div class="p-price">699.000đ<span>/mãi mãi</span></div>
                <div class="p-old-price">Giá gốc: 1.200.000đ</div>
            </div>
            <ul class="p-features">
                <li><i class="fas fa-check-circle"></i> <b>Thanh toán 1 lần, học trọn đời</b></li>
                <li><i class="fas fa-check-circle"></i> Không bao giờ lo hết hạn khóa học</li>
                <li><i class="fas fa-check-circle"></i> Cập nhật miễn phí mọi bài giảng N3 mới</li>
                <li><i class="fas fa-check-circle"></i> Huy hiệu VIP Trọn Đời đặc quyền</li>
            </ul>
            <?php if ($is_lifetime): ?>
            <button class="p-btn p-btn-disabled" disabled><i class="fas fa-check"></i> Đã sở hữu vĩnh viễn</button>
            <?php else: ?>
            <button type="button" class="p-btn" style="background: linear-gradient(135deg, #2ecc71, #27ae60);"
                onclick="openVietQRModal('N3_LIFETIME', 'Gói N3 Vĩnh Viễn (Trọn đời)', 699000)">
                <i class="fas fa-infinity"></i> Mua Trọn Đời
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL QUÉT MÃ VIETQR -->
<div id="vietqr-modal" class="qr-modal-overlay" style="display: none;">
    <div class="qr-modal-card">
        <button type="button" class="qr-close-btn" onclick="closeVietQRModal()">×</button>
        <div class="qr-modal-header">
            <span class="qr-badge"><i class="fas fa-qrcode"></i> CỔNG THANH TOÁN VIETQR</span>
            <h3 id="qr-pkg-title">Thanh toán khóa học N3</h3>
        </div>

        <div class="qr-img-box">
            <img id="qr-image-src" src="" alt="Mã QR Thanh Toán" class="qr-code-img">
        </div>

        <div class="qr-info-table">
            <div class="qr-info-row">
                <span>Số tiền thanh toán:</span>
                <strong id="qr-amount-text"
                    style="color: var(--pink-primary, #ff6b81); font-size: 17px;">89.000đ</strong>
            </div>
            <div class="qr-info-row">
                <span>Nội dung chuyển khoản:</span>
                <strong id="qr-order-code"
                    style="background: #fff0f3; color: #d63031; padding: 3px 8px; border-radius: 6px;">SAKURA...</strong>
            </div>
        </div>

        <div id="qr-status-msg" style="display: none; margin: 12px 0; font-size: 13px; font-weight: 700;"></div>

        <button type="button" id="btn-confirm-payment" class="qr-confirm-btn" onclick="submitMockPayment()">
            <i class="fas fa-check-circle"></i> Tôi đã thanh toán thành công
        </button>
    </div>
</div>

<style>
.pricing-container {
    width: 100%;
    padding: 10px 5px 30px 5px;
    box-sizing: border-box;
}

.pricing-top-banner {
    text-align: center;
    background: #ffffff;
    padding: 28px 25px;
    border-radius: 20px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(255, 107, 129, 0.05);
    border: 1px solid #fff0f3;
}

.pricing-badge {
    display: inline-block;
    background: #fff0f3;
    color: var(--pink-primary, #ff6b81);
    font-weight: 800;
    font-size: 12px;
    padding: 5px 14px;
    border-radius: 20px;
    margin-bottom: 10px;
}

.pricing-main-title {
    margin: 0 0 10px 0;
    font-size: 24px;
    font-weight: 900;
    color: #2f3542;
}

.pricing-sub-title {
    margin: 0;
    font-size: 13.5px;
    color: #57606f;
    line-height: 1.6;
}

.pricing-login-alert {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 22px;
    text-align: center;
    font-size: 13.5px;
    font-weight: 600;
}

.pricing-login-alert a {
    color: #d35400;
    font-weight: 800;
    text-decoration: underline;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    align-items: stretch;
}

.p-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 26px 22px;
    border: 2px solid #f1f2f6;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    position: relative;
    transition: transform 0.25s ease;
}

.p-card:hover {
    transform: translateY(-5px);
}

.p-card-popular {
    border-color: var(--pink-primary, #ff6b81);
    background: linear-gradient(180deg, #ffffff 0%, #fff8fa 100%);
    box-shadow: 0 10px 28px rgba(255, 107, 129, 0.14);
}

.p-popular-tag {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #ff6b81, #ff4757);
    color: #fff;
    font-size: 10.5px;
    font-weight: 800;
    padding: 5px 14px;
    border-radius: 20px;
    white-space: nowrap;
}

.p-card-head {
    text-align: center;
    padding-bottom: 18px;
    margin-bottom: 18px;
    border-bottom: 1px dashed #eee;
}

.p-plan-name {
    display: block;
    font-size: 15px;
    font-weight: 800;
    color: #2f3542;
    margin-bottom: 10px;
}

.p-price {
    font-size: 28px;
    font-weight: 900;
    color: var(--pink-primary, #ff6b81);
}

.p-price span {
    font-size: 12.5px;
    color: #747d8c;
    font-weight: 600;
}

.p-old-price {
    font-size: 12px;
    color: #a4b0be;
    text-decoration: line-through;
    margin-top: 5px;
}

.p-features {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 0 22px 0 !important;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.p-features li {
    font-size: 13px;
    color: #57606f;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.45;
}

.p-features li i {
    color: #2ecc71;
    margin-top: 3px;
    flex-shrink: 0;
}

.p-btn {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #ff6b81 0%, #ff4757 100%);
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 5px 15px rgba(255, 107, 129, 0.3);
    transition: 0.2s;
}

.p-btn:hover:not(:disabled) {
    filter: brightness(1.06);
    transform: translateY(-2px);
}

.p-btn-renew {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
}

.p-btn-disabled {
    background: #dfe4ea !important;
    color: #747d8c !important;
    box-shadow: none !important;
    cursor: not-allowed !important;
}

/* MODAL VIETQR */
.qr-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
}

.qr-modal-card {
    background: #ffffff;
    width: 100%;
    max-width: 410px;
    border-radius: 22px;
    padding: 26px 24px;
    text-align: center;
    position: relative;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
}

.qr-close-btn {
    position: absolute;
    top: 14px;
    right: 18px;
    background: #f1f2f6;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 20px;
    color: #57606f;
    cursor: pointer;
}

.qr-badge {
    display: inline-block;
    background: #eafaf1;
    color: #27ae60;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 15px;
    margin-bottom: 8px;
}

.qr-modal-header h3 {
    margin: 0 0 15px 0;
    font-size: 18px;
    color: #2f3542;
}

.qr-img-box {
    background: #fff;
    border: 2px dashed #ffccd5;
    border-radius: 16px;
    padding: 12px;
    margin-bottom: 16px;
    display: inline-block;
}

.qr-code-img {
    width: 220px;
    height: 220px;
    object-fit: contain;
    display: block;
}

.qr-info-table {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px 15px;
    margin-bottom: 18px;
    text-align: left;
}

.qr-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #57606f;
    padding: 6px 0;
}

.qr-confirm-btn {
    width: 100%;
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: #ffffff;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-size: 14.5px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(46, 204, 113, 0.35);
}

@media (max-width: 900px) {
    .pricing-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let selectedPkg = '';
let selectedAmount = 0;
let generatedOrderCode = '';

function openVietQRModal(pkgType, pkgTitle, amount) {
    <?php if (!$is_logged_in): ?>
    alert('Vui lòng đăng nhập tài khoản trước khi đăng ký gói học!');
    window.location.href = 'index.php?page=login';
    return;
    <?php endif; ?>

    selectedPkg = pkgType;
    selectedAmount = amount;
    generatedOrderCode = 'SAKURA' + Math.floor(100000 + Math.random() * 900000);

    document.getElementById('qr-pkg-title').textContent = pkgTitle;
    document.getElementById('qr-amount-text').textContent = amount.toLocaleString('vi-VN') + 'đ';
    document.getElementById('qr-order-code').textContent = generatedOrderCode;

    // Sinh mã QR VietQR động theo số tiền và mã đơn
    const qrUrl =
        `https://img.vietqr.io/image/MB-0333666999-compact2.png?amount=${amount}&addInfo=${generatedOrderCode}&accountName=SAKURA%20NHAT%20NGU`;
    document.getElementById('qr-image-src').src = qrUrl;

    const statusMsg = document.getElementById('qr-status-msg');
    statusMsg.style.display = 'none';

    document.getElementById('vietqr-modal').style.display = 'flex';
}

function closeVietQRModal() {
    document.getElementById('vietqr-modal').style.display = 'none';
}

function submitMockPayment() {
    const btn = document.getElementById('btn-confirm-payment');
    const statusMsg = document.getElementById('qr-status-msg');
    const oldHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xác nhận giao dịch...';

    const fd = new FormData();
    fd.append('order_code', generatedOrderCode);
    fd.append('package_type', selectedPkg);
    fd.append('amount', selectedAmount);

    fetch('api_confirm_mock_payment.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                statusMsg.style.display = 'block';
                statusMsg.style.color = '#27ae60';
                statusMsg.innerHTML = '🎉 ' + (data.message || 'Kích hoạt khóa học N3 thành công!');
                setTimeout(() => {
                    window.location.href = 'index.php?page=lessons&level=N3';
                }, 1000);
            } else {
                statusMsg.style.display = 'block';
                statusMsg.style.color = '#e74c3c';
                statusMsg.textContent = data.message || 'Có lỗi xảy ra khi xử lý đơn hàng!';
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }
        })
        .catch(() => {
            alert('Lỗi kết nối máy chủ!');
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        });
}
</script>