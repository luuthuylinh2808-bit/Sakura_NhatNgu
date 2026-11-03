<div style="max-width: 500px; margin: 40px auto;">
    <div class="white-card contact-form">
        <div style="text-align: center; margin-bottom: 25px;">
            <i class="fas fa-user-plus" style="font-size: 50px; color: var(--text-main);"></i>
            <h2 style="margin-top: 10px;">Tạo tài khoản mới</h2>
            <p style="font-size: 14px; color: #888;">Bắt đầu hành trình chinh phục tiếng Nhật</p>
        </div>

        <form id="register-form">
            <div class="input-group">
                <label>Họ và tên <span>*</span></label>
                <i class="fas fa-user"></i>
                <input type="text" id="reg-fullname" placeholder="Nhập họ tên của bạn" required>
            </div>
            <div class="input-group">
                <label>Email <span>*</span></label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="reg-email" placeholder="Nhập email đăng ký" required>
            </div>
            <div class="input-group">
                <label>Mật khẩu <span>*</span></label>
                <i class="fas fa-lock"></i>
                <input type="password" id="reg-password" placeholder="Tạo mật khẩu (ít nhất 6 ký tự)" required>
            </div>
            
            <div id="reg-msg" style="text-align: center; font-size: 14px; font-weight: bold; margin-top: 10px; display: none;"></div>

            <button type="submit" class="btn-send" style="width: 100%; margin: 20px 0 0; padding: 15px; background: var(--pink-gradient);">Đăng ký ngay</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php?page=login" style="font-size: 14px; color: #666;"><i class="fas fa-arrow-left"></i> Quay lại Đăng nhập</a>
        </div>
    </div>
</div>

<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Ngăn trang tự động reload
    
    // Lấy dữ liệu người dùng nhập
    let fullname = document.getElementById('reg-fullname').value;
    let email = document.getElementById('reg-email').value;
    let password = document.getElementById('reg-password').value;
    let msgBox = document.getElementById('reg-msg');

    // Gom dữ liệu lại
    let formData = new FormData();
    formData.append('fullname', fullname);
    formData.append('email', email);
    formData.append('password', password);

    // Gửi sang file Backend
    fetch('process_register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        msgBox.style.display = 'block';
        msgBox.innerHTML = data.message;
        
        if(data.status === 'success') {
            msgBox.style.color = '#2ecc71'; // Màu xanh thành công
            // Chờ 2 giây rồi tự động nhảy sang trang Đăng nhập
            setTimeout(() => {
                window.location.href = 'index.php?page=login';
            }, 2000);
        } else {
            msgBox.style.color = '#e74c3c'; // Màu đỏ báo lỗi
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
    });
});
</script>