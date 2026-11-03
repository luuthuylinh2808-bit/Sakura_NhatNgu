<?php
if (!isset($_SESSION['user_id'])) {
    echo "<div class='white-card' style='text-align: center; padding: 50px;'>
            <i class='fas fa-lock' style='font-size: 50px; color: #ccc; margin-bottom: 20px;'></i>
            <h2>Vui lòng đăng nhập</h2>
            <p>Bạn cần đăng nhập để có thể mua khóa học N3 Premium.</p>
            <a href='index.php?page=login' class='btn' style='margin-top: 20px;'>Đăng nhập ngay</a>
          </div>";
    return;
}

// Lấy thông tin user hiện tại để kiểm tra xem đã mua chưa
require_once 'config/database.php';
$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT premium_expiry FROM users WHERE id = $uid")->fetch_assoc();
$has_premium = (!empty($user['premium_expiry']) && strtotime($user['premium_expiry']) > time());

if ($has_premium) {
    echo "<div class='white-card' style='text-align: center; padding: 50px;'>
            <i class='fas fa-check-circle' style='font-size: 50px; color: #2ecc71; margin-bottom: 20px;'></i>
            <h2>Bạn đã là thành viên Premium!</h2>
            <p>Hạn sử dụng của bạn đến ngày: <b>" . date('d/m/Y', strtotime($user['premium_expiry'])) . "</b></p>
            <a href='index.php?page=lessons&level=N3' class='btn' style='margin-top: 20px; background: #2ecc71;'>Vào học N3 ngay</a>
          </div>";
    return;
}
?>

<style>
    .pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
    .pricing-card { background: #fff; border-radius: 20px; padding: 30px; text-align: center; border: 2px solid #eee; transition: 0.3s; position: relative; }
    .pricing-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); border-color: var(--pink-light); }
    .pricing-card.popular { border-color: var(--pink-primary); box-shadow: 0 10px 25px rgba(255,107,129,0.15); }
    .popular-tag { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: var(--pink-primary); color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; white-space: nowrap; }
    .price { font-size: 40px; font-weight: 900; color: var(--pink-primary); margin: 20px 0; }
    .price span { font-size: 16px; color: #888; font-weight: normal; }
    .features { list-style: none; padding: 0; margin: 0 0 25px 0; text-align: left; }
    .features li { padding: 10px 0; border-bottom: 1px dashed #eee; font-size: 14px; color: #555; }
    .features li i { color: #2ecc71; margin-right: 10px; }
    .btn-buy { width: 100%; padding: 15px; border-radius: 12px; font-weight: bold; font-size: 16px; cursor: pointer; border: none; transition: 0.3s; background: #f1f3f5; color: #444; }
    .pricing-card.popular .btn-buy, .btn-buy:hover { background: var(--pink-primary); color: white; }
    
    /* Modal QR */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 9999; }
    .modal-content { background: white; padding: 30px; border-radius: 20px; text-align: center; max-width: 400px; width: 90%; }
    .qr-image { width: 250px; height: 250px; margin: 20px auto; display: block; border-radius: 10px; border: 1px solid #eee; }
    .spinner { border: 4px solid #f3f3f3; border-top: 4px solid var(--pink-primary); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<div class="white-card" style="padding: 40px;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #333; margin-bottom: 10px;">Nâng cấp Premium N3</h1>
        <p style="color: #888;">Mở khóa toàn bộ 15 bài học N3, video bài giảng và bài tập không giới hạn.</p>
    </div>

    <div class="pricing-grid">
        <!-- Gói 1 Tháng -->
        <div class="pricing-card">
            <h3 style="color: #555;">Gói 1 Tháng</h3>
            <div class="price">49.000<span>đ</span></div>
            <ul class="features">
                <li><i class="fas fa-check-circle"></i> Mở khóa toàn bộ N3</li>
                <li><i class="fas fa-check-circle"></i> Xem video bài giảng</li>
                <li><i class="fas fa-check-circle"></i> Làm bài tập trắc nghiệm</li>
            </ul>
            <button class="btn-buy" onclick="openPayment('N3_MONTH', 49000)">Chọn gói này</button>
        </div>

        <!-- Gói 6 Tháng (Phổ biến) -->
        <div class="pricing-card popular">
            <div class="popular-tag"><i class="fas fa-star"></i> Phổ biến nhất</div>
            <h3 style="color: var(--pink-primary);">Gói 6 Tháng</h3>
            <div class="price">89.000<span>đ</span></div>
            <ul class="features">
                <li><i class="fas fa-check-circle"></i> Mở khóa toàn bộ N3</li>
                <li><i class="fas fa-check-circle"></i> Xem video bài giảng</li>
                <li><i class="fas fa-check-circle"></i> Làm bài tập trắc nghiệm</li>
                <li><i class="fas fa-check-circle"></i> Tiết kiệm 40%</li>
            </ul>
            <button class="btn-buy" onclick="openPayment('N3_6MONTH', 89000)">Chọn gói này</button>
        </div>

        <!-- Gói Trọn Đời -->
        <div class="pricing-card">
            <h3 style="color: #555;">Gói Trọn Đời</h3>
            <div class="price">269.000<span>đ</span></div>
            <ul class="features">
                <li><i class="fas fa-check-circle"></i> Mở khóa toàn bộ N3</li>
                <li><i class="fas fa-check-circle"></i> Xem video bài giảng</li>
                <li><i class="fas fa-check-circle"></i> Làm bài tập trắc nghiệm</li>
                <li><i class="fas fa-check-circle"></i> Thanh toán 1 lần duy nhất</li>
            </ul>
            <button class="btn-buy" onclick="openPayment('N3_LIFETIME', 269000)">Chọn gói này</button>
        </div>
    </div>
</div>

<!-- Modal Thanh Toán -->
<div class="modal-overlay" id="payment-modal">
    <div class="modal-content">
        <h3 style="color: var(--pink-primary); margin-bottom: 5px;">Quét mã để thanh toán</h3>
        <p style="color: #888; font-size: 13px; margin-bottom: 15px;">Vui lòng không tắt cửa sổ này cho đến khi thanh toán xong.</p>
        
        <div id="qr-container">
            <div class="spinner"></div>
            <p>Đang tạo mã QR...</p>
        </div>

        <div id="payment-info" style="display: none;">
            <img src="" id="qr-image" class="qr-image" alt="Mã VietQR">
            <p style="font-size: 14px; margin-bottom: 5px;">Nội dung chuyển khoản: <b id="order-code-text" style="color: #e74c3c; font-size: 16px;"></b></p>
            <p style="font-size: 14px;">Số tiền: <b id="amount-text" style="color: #e74c3c;"></b></p>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff8e1; border-radius: 10px; border: 1px dashed #f39c12; display: flex; align-items: center; gap: 10px;">
                <div class="spinner" style="width: 20px; height: 20px; border-width: 3px; margin: 0;"></div>
                <span style="font-size: 13px; color: #d35400; font-weight: bold;">Hệ thống đang chờ xác nhận thanh toán...</span>
            </div>
        </div>
        
        <button onclick="closePayment()" style="margin-top: 20px; background: none; border: none; color: #888; text-decoration: underline; cursor: pointer;">Hủy giao dịch</button>
    </div>
</div>

<script>
    let pollingInterval = null;

    function openPayment(packageType, amount) {
        document.getElementById('payment-modal').style.display = 'flex';
        document.getElementById('qr-container').style.display = 'block';
        document.getElementById('payment-info').style.display = 'none';

        // Gọi API tạo đơn hàng
        let formData = new FormData();
        formData.append('package_type', packageType);
        formData.append('amount', amount);

        fetch('api_create_order.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('qr-container').style.display = 'none';
                document.getElementById('payment-info').style.display = 'block';
                
                document.getElementById('qr-image').src = data.qr_url;
                document.getElementById('order-code-text').innerText = data.order_code;
                document.getElementById('amount-text').innerText = amount.toLocaleString('vi-VN') + 'đ';

                // Bắt đầu kiểm tra trạng thái thanh toán mỗi 3 giây
                startPolling(data.order_code);
            } else {
                alert('Lỗi: ' + data.message);
                closePayment();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Có lỗi xảy ra khi tạo đơn hàng.');
            closePayment();
        });
    }

    function startPolling(orderCode) {
        if (pollingInterval) clearInterval(pollingInterval);
        
        pollingInterval = setInterval(() => {
            let formData = new FormData();
            formData.append('order_code', orderCode);

            fetch('api_check_payment_status.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.payment_status === 'completed') {
                    clearInterval(pollingInterval);
                    alert('Thanh toán thành công! Chúc mừng bạn đã mở khóa khóa học N3. 🌸');
                    window.location.reload();
                }
            })
            .catch(err => console.error("Lỗi kiểm tra thanh toán:", err));
        }, 3000); // 3 giây kiểm tra 1 lần
    }

    function closePayment() {
        document.getElementById('payment-modal').style.display = 'none';
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }
</script>