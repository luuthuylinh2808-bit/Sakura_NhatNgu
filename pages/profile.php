<div style="max-width: 600px; margin: 20px auto;">
    <div class="white-card contact-form">
        <?php
        if (!isset($_SESSION['user_id'])) {
            echo "<p style='text-align:center;'>Vui lòng đăng nhập để xem hồ sơ.</p>";
            return;
        }

        // ĐÃ FIX: Sửa đường dẫn tương đối theo file index.php
        require_once 'config/database.php'; 
        
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        ?>

        <div style="text-align: center; margin-bottom: 25px;">
            <img src="https://i.postimg.cc/HnbtXK1r/Chat-GPT-Image-11-19-26-4-thg-4-2026.png" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid var(--pink-light); margin-bottom: 10px;">
            <h2 style="margin-top: 10px;">Hồ sơ của bạn</h2>
            <p style="font-size: 14px; color: #888;">Cập nhật thông tin cá nhân của bạn</p>
        </div>

        <form id="profile-form">
            <div class="input-group">
                <label>Họ và tên <span>*</span></label>
                <i class="fas fa-user"></i>
                <input type="text" id="prof-fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
            </div>
            
            <div class="input-group">
                <label>Email (Không thể thay đổi)</label>
                <i class="fas fa-envelope" style="color: #ccc;"></i>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background: #f5f5f5; color: #888; cursor: not-allowed;">
            </div>

            <div class="input-group">
                <label>Số điện thoại</label>
                <i class="fas fa-phone"></i>
                <input type="text" id="prof-phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
            </div>

            <div class="input-group">
                <label>Ngày sinh</label>
                <i class="fas fa-calendar-alt"></i>
                <input type="date" id="prof-dob" value="<?= htmlspecialchars($user['dob'] ?? '') ?>" style="padding-left: 45px;">
            </div>
            
            <div id="prof-msg" style="text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 15px; display: none;"></div>

            <button type="submit" class="btn-send" id="btn-submit-prof" style="width: 100%; padding: 15px; background: var(--pink-primary); color: white; border: none; border-radius: 20px; font-weight: bold; cursor: pointer;"><i class="fas fa-save"></i> Lưu thay đổi</button>
        </form>
    </div>
</div>

<script>
document.getElementById('profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let btnSubmit = document.getElementById('btn-submit-prof');
    let msgBox = document.getElementById('prof-msg');
    
    btnSubmit.innerText = "Đang lưu...";
    btnSubmit.disabled = true;

    let formData = new FormData();
    formData.append('fullname', document.getElementById('prof-fullname').value);
    formData.append('phone', document.getElementById('prof-phone').value);
    formData.append('dob', document.getElementById('prof-dob').value);

    // Đảm bảo bạn đã tạo file process_profile.php ngang hàng với index.php nhé
    fetch('process_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnSubmit.innerHTML = '<i class="fas fa-save"></i> Lưu thay đổi';
        btnSubmit.disabled = false;
        
        msgBox.style.display = 'block';
        msgBox.innerHTML = data.message;
        
        if(data.status === 'success') {
            msgBox.style.color = '#2ecc71';
            // Cập nhật lại thanh Header
            setTimeout(() => { location.reload(); }, 1500);
        } else {
            msgBox.style.color = '#e74c3c';
        }
    })
    .catch(error => {
        console.error('Lỗi:', error);
        btnSubmit.innerHTML = '<i class="fas fa-save"></i> Lưu thay đổi';
        btnSubmit.disabled = false;
        msgBox.style.display = 'block';
        msgBox.style.color = '#e74c3c';
        msgBox.innerHTML = "Có lỗi xảy ra, vui lòng thử lại!";
    });
});
</script>