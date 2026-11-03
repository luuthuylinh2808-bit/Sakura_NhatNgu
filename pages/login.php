<div style="max-width: 500px; margin: 40px auto;">
    <div class="white-card contact-form">
        <div style="text-align: center; margin-bottom: 25px;">
            <i class="fas fa-sign-in-alt" style="font-size: 50px; color: var(--pink-primary);"></i>
            <h2 style="margin-top: 10px;">Chào mừng trở lại!</h2>
            <p style="font-size: 14px; color: #888;">Đăng nhập để tiếp tục học tập</p>
        </div>

        <form id="login-form">
            <div class="input-group">
                <label>Email <span>*</span></label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="log-email" placeholder="Nhập email của bạn" required>
            </div>
            <div class="input-group">
                <label>Mật khẩu <span>*</span></label>
                <i class="fas fa-lock"></i>
                <input type="password" id="log-password" placeholder="Nhập mật khẩu" required>
            </div>
            
            <div style="text-align: right; margin-bottom: 15px;">
                <a href="index.php?page=forgot_password" style="font-size: 13px; color: var(--pink-primary); font-weight: 700;">Quên mật khẩu?</a>
            </div>

            <div id="log-msg" style="text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 10px; display: none;"></div>

            <button type="submit" class="btn-send" style="width: 100%; padding: 15px;">Đăng nhập</button>
        </form>

        <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f5f5f5;">
            <p style="font-size: 14px; color: #666;">Chưa có tài khoản? <a href="index.php?page=register" style="color: var(--pink-primary); font-weight: bold;">Đăng ký ngay</a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let email = document.getElementById('log-email').value;
    let password = document.getElementById('log-password').value;
    let msgBox = document.getElementById('log-msg');

    let formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    fetch('process_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        msgBox.style.display = 'block';
        msgBox.innerHTML = data.message;
        
        if(data.status === 'success') {
            msgBox.style.color = '#2ecc71';
            setTimeout(() => {
                window.location.href = data.redirect;; // Đăng nhập xong về Trang chủ
            }, 1000);
        } else {
            msgBox.style.color = '#e74c3c';
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
    });
});
</script>