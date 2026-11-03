<div class="contact-wrapper" id="contact-section" style="display: none; grid-column: 1 / -1; width: 100%; margin-top: 20px; margin-bottom: 30px;">
    <div class="contact-container" style="display: flex; gap: 25px; align-items: stretch; width: 100%;">
        
        <div class="white-card contact-form" style="flex: 1.6; margin-bottom: 0 !important;">
            <h2>Gửi tin nhắn cho chúng tôi</h2>
            <form id="form-contact">
                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="input-group" style="flex: 1;">
                        <label>Tên của bạn <span>*</span></label>
                        <i class="fas fa-user"></i>
                        <input type="text" id="contact-name" placeholder="Tên của bạn" required>
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <label>Email của bạn <span>*</span></label>
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="contact-email" placeholder="Email của bạn" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Chủ đề <span>*</span></label>
                    <select id="contact-subject" style="padding-left: 15px;" required>
                        <option value="">---- Chọn chủ đề ----</option>
                        <option value="Tư vấn khóa học">Tư vấn khóa học</option>
                        <option value="Hỗ trợ kỹ thuật">Hỗ trợ kỹ thuật</option>
                        <option value="Góp ý - Báo lỗi">Góp ý - Báo lỗi</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Nội dung</label>
                    <textarea id="contact-message" placeholder="Nội dung tin nhắn..." required></textarea>
                </div>
                
                <div id="contact-msg" style="text-align: center; margin-bottom: 15px; font-weight: bold; display: none;"></div>

                <button type="submit" class="btn-send" id="btn-submit-contact">Gửi tin nhắn</button>
            </form>
        </div>

        <div class="white-card contact-info" style="flex: 1; margin-bottom: 0 !important;">
            <h2>Thông tin liên hệ</h2>
            <div class="info-list">
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="info-text">
                        <h4>Hotline / Zalo</h4>
                        <p>+84 91 234 5678</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fab fa-facebook-messenger"></i></div>
                    <div class="info-text">
                        <h4>Facebook Messenger</h4>
                        <p>m.me/sakuranhatngu</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-text">
                        <h4>Địa chỉ trung tâm</h4>
                        <p>123 Đường Hoa Anh Đào, P.4, Q.3, TP. HCM</p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<style>
    /* Chỉ giữ lại style cho mobile */
    @media (max-width: 768px) {
        .contact-container {
            flex-direction: column !important;
        }
    }
</style>

<script>
function initContact() {
    let urlParams = new URLSearchParams(window.location.search);
    let page = urlParams.get('page') || 'home';
    let contactSec = document.getElementById('contact-section');
    
    if (contactSec) {
        // Cần đảm bảo khi block, nó vẫn giữ các thuộc tính grid
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
            btnSubmit.innerText = "Đang gửi...";
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
                btnSubmit.innerText = "Gửi tin nhắn";
                btnSubmit.disabled = false;
                msgBox.style.display = 'block';
                msgBox.innerHTML = data.message;
                
                if(data.status === 'success') {
                    msgBox.style.color = '#2ecc71'; 
                    document.getElementById('form-contact').reset(); 
                    setTimeout(() => { msgBox.style.display = 'none'; }, 5000);
                } else {
                    msgBox.style.color = '#e74c3c'; 
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                btnSubmit.innerText = "Gửi tin nhắn";
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