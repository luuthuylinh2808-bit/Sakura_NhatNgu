<div class="contact-wrapper" id="contact-section"
    style="display: none; grid-column: 1 / -1; width: 100%; margin-top: 25px; margin-bottom: 25px;">
    <!-- Khung form căn giữa, loại bỏ cột thông tin bị trùng với Footer -->
    <div class="white-card contact-form"
        style="max-width: 820px; margin: 0 auto !important; padding: 35px 40px; box-sizing: border-box;">
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="font-size: 22px; font-weight: 800; color: #2f3542; margin-bottom: 8px;">
                <i class="fas fa-paper-plane" style="color: var(--pink-primary, #ff6b81); margin-right: 8px;"></i>Gửi
                tin nhắn cho chúng tôi
            </h2>
            <p style="color: #747d8c; font-size: 13.5px; margin: 0;">
                Mọi góp ý, thắc mắc về khóa học hoặc báo lỗi hệ thống đều được phản hồi sớm nhất.
            </p>
        </div>

        <form id="form-contact">
            <div class="form-row" style="display: flex; gap: 20px; margin-bottom: 18px;">
                <div class="input-group" style="flex: 1; position: relative;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #444; margin-bottom: 6px;">
                        Tên của bạn <span style="color: #e74c3c;">*</span>
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-user"
                            style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--pink-primary, #ff6b81); font-size: 13px;"></i>
                        <input type="text" id="contact-name" placeholder="Nhập họ và tên..." required
                            style="width: 100%; padding: 11px 14px 11px 38px; border: 1.5px solid #eee; border-radius: 12px; font-size: 13.5px; outline: none; box-sizing: border-box; transition: 0.2s;"
                            onfocus="this.style.borderColor='var(--pink-primary, #ff6b81)'"
                            onblur="this.style.borderColor='#eee'">
                    </div>
                </div>

                <div class="input-group" style="flex: 1; position: relative;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #444; margin-bottom: 6px;">
                        Email của bạn <span style="color: #e74c3c;">*</span>
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-envelope"
                            style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--pink-primary, #ff6b81); font-size: 13px;"></i>
                        <input type="email" id="contact-email" placeholder="example@gmail.com" required
                            style="width: 100%; padding: 11px 14px 11px 38px; border: 1.5px solid #eee; border-radius: 12px; font-size: 13.5px; outline: none; box-sizing: border-box; transition: 0.2s;"
                            onfocus="this.style.borderColor='var(--pink-primary, #ff6b81)'"
                            onblur="this.style.borderColor='#eee'">
                    </div>
                </div>
            </div>

            <div class="input-group" style="margin-bottom: 18px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #444; margin-bottom: 6px;">
                    Chủ đề <span style="color: #e74c3c;">*</span>
                </label>
                <select id="contact-subject" required
                    style="width: 100%; padding: 11px 14px; border: 1.5px solid #eee; border-radius: 12px; font-size: 13.5px; outline: none; background: #fff; cursor: pointer; box-sizing: border-box; transition: 0.2s;"
                    onfocus="this.style.borderColor='var(--pink-primary, #ff6b81)'"
                    onblur="this.style.borderColor='#eee'">
                    <option value="">---- Chọn chủ đề ----</option>
                    <option value="Tư vấn khóa học">Tư vấn khóa học N3</option>
                    <option value="Hỗ trợ kỹ thuật">Hỗ trợ kỹ thuật / Tài khoản</option>
                    <option value="Góp ý - Báo lỗi">Góp ý nội dung - Báo lỗi bài học</option>
                </select>
            </div>

            <div class="input-group" style="margin-bottom: 22px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #444; margin-bottom: 6px;">
                    Nội dung lời nhắn <span style="color: #e74c3c;">*</span>
                </label>
                <textarea id="contact-message" placeholder="Nội dung chi tiết tin nhắn..." required rows="4"
                    style="width: 100%; padding: 12px 14px; border: 1.5px solid #eee; border-radius: 12px; font-size: 13.5px; outline: none; resize: vertical; box-sizing: border-box; transition: 0.2s;"
                    onfocus="this.style.borderColor='var(--pink-primary, #ff6b81)'"
                    onblur="this.style.borderColor='#eee'"></textarea>
            </div>

            <div id="contact-msg"
                style="text-align: center; margin-bottom: 18px; font-weight: bold; font-size: 13.5px; display: none;">
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn-send" id="btn-submit-contact"
                    style="background: linear-gradient(135deg, #ff6b81 0%, #ff4757 100%); color: white; border: none; padding: 12px 42px; border-radius: 25px; font-size: 14.5px; font-weight: 800; cursor: pointer; box-shadow: 0 6px 18px rgba(255, 107, 129, 0.35); transition: 0.2s;">
                    <i class="fas fa-paper-plane" style="margin-right: 6px;"></i> Gửi tin nhắn
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .contact-form {
        padding: 25px 20px !important;
    }

    .form-row {
        flex-direction: column !important;
        gap: 15px !important;
    }
}
</style>

<script>
function initContact() {
    let urlParams = new URLSearchParams(window.location.search);
    let page = urlParams.get('page') || 'home';
    let contactSec = document.getElementById('contact-section');

    if (contactSec) {
        if (page === 'home' || page === 'contact') {
            contactSec.style.display = 'block';
        } else {
            contactSec.style.display = 'none';
        }
    }

    let contactForm = document.getElementById('form-contact');
    if (contactForm && !contactForm.dataset.listenerAttached) {
        contactForm.dataset.listenerAttached = 'true';

        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            let btnSubmit = document.getElementById('btn-submit-contact');
            btnSubmit.innerHTML =
                '<i class="fas fa-spinner fa-spin" style="margin-right: 6px;"></i> Đang gửi...';
            btnSubmit.disabled = true;

            let formData = new FormData();
            formData.append('name', document.getElementById('contact-name').value);
            formData.append('email', document.getElementById('contact-email').value);
            formData.append('subject', document.getElementById('contact-subject').value);
            formData.append('message', document.getElementById('contact-message').value);

            let msgBox = document.getElementById('contact-msg');

            fetch('process_contact.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btnSubmit.innerHTML =
                        '<i class="fas fa-paper-plane" style="margin-right: 6px;"></i> Gửi tin nhắn';
                    btnSubmit.disabled = false;
                    msgBox.style.display = 'block';
                    msgBox.innerHTML = data.message;

                    if (data.status === 'success') {
                        msgBox.style.color = '#2ecc71';
                        document.getElementById('form-contact').reset();
                        setTimeout(() => {
                            msgBox.style.display = 'none';
                        }, 5000);
                    } else {
                        msgBox.style.color = '#e74c3c';
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    btnSubmit.innerHTML =
                        '<i class="fas fa-paper-plane" style="margin-right: 6px;"></i> Gửi tin nhắn';
                    btnSubmit.disabled = false;
                    msgBox.style.display = 'block';
                    msgBox.style.color = '#e74c3c';
                    msgBox.innerHTML = "Có lỗi xảy ra, vui lòng thử lại!";
                });
        });
    }
}

initContact();
document.addEventListener('DOMContentLoaded', initContact);
</script>