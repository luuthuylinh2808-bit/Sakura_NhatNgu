<footer class="site-footer">
    <div class="footer-container">
        <!-- Cột 1: Thương hiệu -->
        <div class="footer-col brand-col">
            <div class="footer-logo">
                <i class="fas fa-torii-gate"></i> Sakura Nhật Ngữ
            </div>
            <p class="footer-desc">
                Nền tảng tự học tiếng Nhật trực tuyến từ N5 đến N3 chuẩn giáo trình Minna no Nihongo & Soumatome. Học từ
                vựng, ngữ pháp và luyện đề JLPT mọi lúc mọi nơi.
            </p>
            <div class="footer-socials">
                <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://youtube.com" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://tiktok.com" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
                <a href="mailto:support@sakuranhatngu.vn" title="Email"><i class="fas fa-envelope"></i></a>
            </div>
        </div>

        <!-- Cột 2: Điều hướng (Đã căn thẳng lề trái) -->
        <div class="footer-col">
            <h4 class="footer-title">Khám phá</h4>
            <ul class="footer-links">
                <li><a href="index.php?page=home"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>
                <li><a href="index.php?page=lessons&level=N5"><i class="fas fa-chevron-right"></i> Danh sách bài học</a>
                </li>
                <li><a href="index.php?page=practice"><i class="fas fa-chevron-right"></i> Luyện tập trắc nghiệm</a>
                </li>
                <li><a href="index.php?page=vocab"><i class="fas fa-chevron-right"></i> Sổ tay từ vựng</a></li>
                <li><a href="index.php?page=grammar"><i class="fas fa-chevron-right"></i> Ngữ pháp cơ bản</a></li>
            </ul>
        </div>

        <!-- Cột 3: Lộ trình JLPT (Đã căn thẳng lề trái) -->
        <div class="footer-col">
            <h4 class="footer-title">Lộ trình học</h4>
            <ul class="footer-links">
                <li><a href="index.php?page=lessons&level=N5"><i class="fas fa-seedling" style="color: #2ecc71;"></i>
                        Tiếng Nhật N5 (Cơ bản)</a></li>
                <li><a href="index.php?page=lessons&level=N4"><i class="fas fa-leaf" style="color: #27ae60;"></i> Tiếng
                        Nhật N4 (Sơ cấp)</a></li>
                <li><a href="index.php?page=pricing"><i class="fas fa-crown" style="color: #f39c12;"></i> Khóa học N3
                        Premium</a></li>
                <li><a href="index.php?page=listening"><i class="fas fa-headphones" style="color: #3498db;"></i> Luyện
                        nghe qua Video</a></li>
                <li><a href="index.php?page=favorites"><i class="fas fa-heart" style="color: #ff6b81;"></i> Bài học yêu
                        thích</a></li>
            </ul>
        </div>

        <!-- Cột 4: Liên hệ trung tâm (Cột chuẩn mẫu) -->
        <div class="footer-col">
            <h4 class="footer-title">Liên hệ trung tâm</h4>
            <div class="footer-contact-list">
                <div class="contact-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>123 Đường Hoa Anh Đào, P.4, Q.3, TP. HCM</span>
                </div>
                <div class="contact-row">
                    <i class="fas fa-phone-alt"></i>
                    <span>Hotline: <b>+84 91 234 5678</b></span>
                </div>
                <div class="contact-row">
                    <i class="fas fa-clock"></i>
                    <span>08:00 - 21:00 (Thứ 2 - Chủ Nhật)</span>
                </div>
            </div>
            <div class="footer-guarantee">
                <i class="fas fa-shield-alt"></i> Đồng hành cùng bạn đạt chứng chỉ JLPT
            </div>
        </div>
    </div>

    <!-- Bản quyền chân trang -->
    <div class="footer-bottom">
        <p>© 2026 Sakura Nhật Ngữ. All rights reserved. 🌸 <span>継続は力なり (Kiên trì là chìa khóa của thành công)</span>
        </p>
    </div>
</footer>

<style>
/* CSS FOOTER CĂN THẲNG TẮP LỀ TRÁI CẢ 4 CỘT */
.site-footer {
    background: #ffffff;
    border-top: 2px solid #ffeef1;
    margin-top: 50px;
    padding: 45px 0 20px 0;
    box-shadow: 0 -8px 25px rgba(255, 107, 129, 0.04);
    width: 100%;
    position: relative;
    z-index: 5;
    box-sizing: border-box;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 25px;
    display: grid;
    grid-template-columns: 1.3fr 1fr 1.1fr 1.3fr;
    gap: 35px;
    align-items: start;
    box-sizing: border-box;
}

.footer-col {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    /* Ép mọi thành phần canh sát lề trái cột */
    text-align: left;
}

.footer-logo {
    font-size: 19px;
    font-weight: 900;
    color: var(--pink-primary, #ff6b81);
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.footer-desc {
    font-size: 12.5px;
    color: #747d8c;
    line-height: 1.6;
    margin: 0 0 16px 0;
}

.footer-socials {
    display: flex;
    gap: 10px;
}

.footer-socials a {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff0f3;
    color: var(--pink-primary, #ff6b81);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.25s ease;
    border: 1px solid #ffd8e0;
}

.footer-socials a:hover {
    background: var(--pink-primary, #ff6b81);
    color: #ffffff;
    transform: translateY(-2px);
}

/* TIÊU ĐỀ CỘT */
.footer-title {
    font-size: 15px;
    font-weight: 800;
    color: #2f3542;
    margin: 0 0 14px 0;
    padding-bottom: 6px;
    border-bottom: 2px solid #ffccd5;
    display: inline-block;
}

/* FIX LỖI THỤT LỀ: TRIỆT TIÊU TOÀN BỘ PADDING VÀ MARGIN CỦA UL */
ul.footer-links {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
}

ul.footer-links li {
    padding: 0 !important;
    margin: 0 !important;
    list-style: none !important;
    text-align: left !important;
}

ul.footer-links li a {
    color: #57606f;
    text-decoration: none;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    padding: 0 !important;
}

ul.footer-links li a i {
    font-size: 11px;
    width: 14px;
    text-align: center;
    flex-shrink: 0;
}

ul.footer-links li a:hover {
    color: var(--pink-primary, #ff6b81);
    transform: translateX(4px);
}

/* DANH SÁCH LIÊN HỆ CỘT 4 */
.footer-contact-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
}

.contact-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 12.5px;
    color: #57606f;
    line-height: 1.45;
}

.contact-row i {
    color: var(--pink-primary, #ff6b81);
    margin-top: 2px;
    font-size: 13px;
    width: 14px;
    text-align: center;
    flex-shrink: 0;
}

.footer-guarantee {
    margin-top: 14px;
    background: #fff5f7;
    border: 1px dashed #ffccd5;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 11.5px;
    color: var(--pink-primary, #ff6b81);
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ĐÁY FOOTER */
.footer-bottom {
    margin-top: 35px;
    padding-top: 18px;
    border-top: 1px solid #f1f2f6;
    text-align: center;
    font-size: 12px;
    color: #a4b0be;
}

.footer-bottom span {
    color: #747d8c;
    font-style: italic;
    margin-left: 4px;
}

@media (max-width: 992px) {
    .footer-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
}

@media (max-width: 600px) {
    .footer-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="assets/js/main.js?v=2"></script>
</body>

</html>