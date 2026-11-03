<div class="white-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="margin: 0; color: #333;"><i class="fas fa-receipt" style="color: var(--pink-primary);"></i> Lịch sử thanh toán</h2>
            <p style="color: #888; margin: 5px 0 0 0; font-size: 14px;">Quản lý các giao dịch mua khóa học của bạn.</p>
        </div>
        <a href="index.php?page=pricing" class="btn" style="padding: 10px 20px;">
            <i class="fas fa-plus-circle"></i> Mua khóa học mới
        </a>
    </div>

    <?php
    if (!isset($_SESSION['user_id'])) {
        echo "<div style='text-align: center; padding: 50px;'>
                <i class='fas fa-user-lock' style='font-size: 50px; color: #ddd; margin-bottom: 15px;'></i>
                <p style='color: #999;'>Vui lòng đăng nhập để xem lịch sử thanh toán.</p>
                <a href='index.php?page=login' class='btn' style='margin-top: 15px;'>Đăng nhập ngay</a>
              </div>";
        return;
    }

    require_once 'config/database.php';
    $uid = (int)$_SESSION['user_id'];

    // Lấy danh sách đơn hàng của user
    $sql = "SELECT * FROM orders WHERE id_user = $uid ORDER BY created_at DESC";
    $result = $conn->query($sql);
    ?>

    <!-- Thẻ thống kê tổng quan -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
        <?php
        // Đếm số đơn theo trạng thái
        $total_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE id_user = $uid")->fetch_row()[0];
        $completed_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE id_user = $uid AND status = 'completed'")->fetch_row()[0];
        $pending_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE id_user = $uid AND status = 'pending'")->fetch_row()[0];
        ?>
        <div style="background: #fff5f7; border: 1px solid #ffe6ea; border-radius: 15px; padding: 20px; text-align: center;">
            <i class="fas fa-shopping-bag" style="color: var(--pink-primary); font-size: 24px; margin-bottom: 10px;"></i>
            <div style="font-size: 26px; font-weight: 800; color: #333;"><?= $total_orders ?></div>
            <div style="font-size: 13px; color: #888;">Tổng đơn hàng</div>
        </div>
        <div style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 15px; padding: 20px; text-align: center;">
            <i class="fas fa-check-circle" style="color: #2ecc71; font-size: 24px; margin-bottom: 10px;"></i>
            <div style="font-size: 26px; font-weight: 800; color: #333;"><?= $completed_orders ?></div>
            <div style="font-size: 13px; color: #888;">Đã hoàn thành</div>
        </div>
        <div style="background: #fff8e1; border: 1px solid #ffe0b2; border-radius: 15px; padding: 20px; text-align: center;">
            <i class="fas fa-clock" style="color: #f39c12; font-size: 24px; margin-bottom: 10px;"></i>
            <div style="font-size: 26px; font-weight: 800; color: #333;"><?= $pending_orders ?></div>
            <div style="font-size: 13px; color: #888;">Đang chờ xử lý</div>
        </div>
    </div>

    <!-- Bảng danh sách giao dịch -->
    <?php if ($result && $result->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #fdf2f4; color: var(--pink-primary);">
                        <th style="padding: 15px; text-align: left; border-radius: 10px 0 0 10px;">Mã đơn hàng</th>
                        <th style="padding: 15px; text-align: left;">Gói đăng ký</th>
                        <th style="padding: 15px; text-align: right;">Số tiền</th>
                        <th style="padding: 15px; text-align: center;">Trạng thái</th>
                        <th style="padding: 15px; text-align: left;">Ngày tạo</th>
                        <th style="padding: 15px; text-align: center; border-radius: 0 10px 10px 0;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Xác định tên gói và badge trạng thái
                        $package_names = [
                            'N3_MONTH' => 'Gói 1 Tháng',
                            'N3_6MONTH' => 'Gói 6 Tháng',
                            'N3_LIFETIME' => 'Gói Trọn Đời'
                        ];
                        $package_name = $package_names[$row['package_type']] ?? $row['package_type'];

                        $status_info = [
                            'pending' => ['label' => 'Đang chờ', 'bg' => '#fff8e1', 'color' => '#f39c12', 'icon' => 'fa-clock'],
                            'completed' => ['label' => 'Thành công', 'bg' => '#e8f5e9', 'color' => '#2ecc71', 'icon' => 'fa-check-circle'],
                            'cancelled' => ['label' => 'Đã hủy', 'bg' => '#ffebee', 'color' => '#e74c3c', 'icon' => 'fa-times-circle']
                        ];
                        $status = $status_info[$row['status']] ?? $status_info['pending'];
                    ?>
                        <tr style="border-bottom: 1px solid #f5f5f5;">
                            <td style="padding: 15px;"><b style="color: #555;"><?= htmlspecialchars($row['order_code']) ?></b></td>
                            <td style="padding: 15px; color: #666;"><?= $package_name ?></td>
                            <td style="padding: 15px; text-align: right; font-weight: bold; color: var(--pink-primary);"><?= number_format($row['amount'], 0, ',', '.') ?>đ</td>
                            <td style="padding: 15px; text-align: center;">
                                <span style="background: <?= $status['bg'] ?>; color: <?= $status['color'] ?>; padding: 6px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; white-space: nowrap;">
                                    <i class="fas <?= $status['icon'] ?>"></i> <?= $status['label'] ?>
                                </span>
                            </td>
                            <td style="padding: 15px; color: #888; font-size: 13px;"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td style="padding: 15px; text-align: center;">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <button onclick="reopenPayment('<?= $row['order_code'] ?>', '<?= $row['package_type'] ?>', <?= $row['amount'] ?>)" 
                                            class="btn" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-qrcode"></i> Thanh toán lại
                                    </button>
                                <?php elseif ($row['status'] === 'completed'): ?>
                                    <a href="index.php?page=lessons&level=N3" class="btn" style="padding: 6px 12px; font-size: 12px; background: #2ecc71;">
                                        <i class="fas fa-play"></i> Vào học
                                    </a>
                                <?php else: ?>
                                    <span style="color: #ccc; font-size: 12px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-receipt" style="font-size: 60px; color: #ffe6ea; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">Bạn chưa có giao dịch nào</h3>
            <p style="color: #999; margin-bottom: 20px;">Nâng cấp khóa học N3 Premium để mở khóa toàn bộ nội dung.</p>
            <a href="index.php?page=pricing" class="btn" style="padding: 12px 30px;">
                <i class="fas fa-crown"></i> Khám phá gói Premium
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal thanh toán lại (tái sử dụng) -->
<div class="modal-overlay" id="repay-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; max-width: 400px; width: 90%;">
        <h3 style="color: var(--pink-primary); margin-bottom: 5px;">Quét mã để thanh toán</h3>
        <p style="color: #888; font-size: 13px; margin-bottom: 15px;">Vui lòng không tắt cửa sổ này.</p>

        <img src="" id="repay-qr-image" style="width: 250px; height: 250px; margin: 20px auto; display: block; border-radius: 10px; border: 1px solid #eee;" alt="Mã VietQR">
        <p style="font-size: 14px; margin-bottom: 5px;">Nội dung CK: <b id="repay-order-code" style="color: #e74c3c;"></b></p>
        <p style="font-size: 14px;">Số tiền: <b id="repay-amount" style="color: #e74c3c;"></b></p>

        <div style="margin-top: 20px; padding: 15px; background: #fff8e1; border-radius: 10px; border: 1px dashed #f39c12; display: flex; align-items: center; justify-content: center; gap: 10px;">
            <div class="spinner" style="width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid var(--pink-primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <span style="font-size: 13px; color: #d35400; font-weight: bold;">Đang chờ xác nhận...</span>
        </div>

        <button onclick="closeRepay()" style="margin-top: 20px; background: none; border: none; color: #888; text-decoration: underline; cursor: pointer;">Đóng</button>
    </div>
</div>

<style>
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
    let repayPolling = null;

    function reopenPayment(orderCode, packageType, amount) {
        document.getElementById('repay-modal').style.display = 'flex';
        document.getElementById('repay-order-code').innerText = orderCode;
        document.getElementById('repay-amount').innerText = amount.toLocaleString('vi-VN') + 'đ';

        // Tạo lại ảnh QR từ order_code có sẵn
        const bank_id = 'VCB';
        const account_no = '0123456789';
        const account_name = 'SAKURA NHAT NGU';
        const qr_url = `https://img.vietqr.io/image/${bank_id}-${account_no}-compact2.png?amount=${amount}&addInfo=${orderCode}&accountName=${encodeURIComponent(account_name)}`;
        document.getElementById('repay-qr-image').src = qr_url;

        // Bắt đầu polling kiểm tra
        if (repayPolling) clearInterval(repayPolling);
        repayPolling = setInterval(() => {
            let formData = new FormData();
            formData.append('order_code', orderCode);

            fetch('api_check_payment_status.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.payment_status === 'completed') {
                        clearInterval(repayPolling);
                        alert('Thanh toán thành công! Khóa học đã được mở. 🌸');
                        window.location.reload();
                    }
                });
        }, 3000);
    }

    function closeRepay() {
        document.getElementById('repay-modal').style.display = 'none';
        if (repayPolling) {
            clearInterval(repayPolling);
            repayPolling = null;
        }
    }
</script>