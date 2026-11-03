// ==========================================
// 1. HÀM PHÁT ÂM BẰNG AI (DÙNG CHUNG)
// ==========================================
window.playAudio = function(event, text) {
    if (event) event.stopPropagation(); 
    
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel(); 
        
        let msg = new SpeechSynthesisUtterance();
        msg.text = text;
        msg.lang = 'ja-JP'; 
        msg.rate = 0.9;     
        
        window.speechSynthesis.speak(msg);
    } else {
        alert("Trình duyệt của bạn không hỗ trợ tính năng đọc tự động.");
    }
};

// ==========================================
// 2. DỮ LIỆU TĨNH N5 VÀ N4
// ==========================================
const data = {
  N5: {
    lessons: [
      { title: "Bài 1: 自己紹介 (Tự giới thiệu)", desc: "Cách chào hỏi và giới thiệu tên tuổi, nghề nghiệp.", badge: "N5 - Bài 1", progress: 100 },
      { title: "Bài 2: これは何ですか？", desc: "Học về các đại từ chỉ định: Cái này, cái đó, cái kia.", badge: "N5 - Bài 2", progress: 45 },
      { title: "Bài 3: ここは学校です", desc: "Chỉ nơi chốn, giới thiệu trường học.", badge: "N5 - Bài 3", progress: 0 },
      { title: "Bài 4: 今何時ですか", desc: "Hỏi giờ, số đếm cơ bản.", badge: "N5 - Bài 4", progress: 0 },
      { title: "Bài 5: 私はコーヒーが好きです", desc: "Diễn tả sở thích, thích/không thích.", badge: "N5 - Bài 5", progress: 0 },
      { title: "Bài 6: 一緒に勉強しませんか", desc: "Cách rủ rê, đề nghị cùng làm gì.", badge: "N5 - Bài 6", progress: 0 },
      { title: "Bài 7: 日本語は難しいです", desc: "Tính từ cơ bản, miêu tả sự vật.", badge: "N5 - Bài 7", progress: 0 },
      { title: "Bài 8: どこで買いましたか", desc: "Hỏi địa điểm, nơi chốn hành động.", badge: "N5 - Bài 8", progress: 0 },
      { title: "Bài 9: 毎日勉強します", desc: "Động từ thể thường, thói quen hàng ngày.", badge: "N5 - Bài 9", progress: 0 },
      { title: "Bài 10: もう食べましたか", desc: "Thể quá khứ của động từ.", badge: "N5 - Bài 10", progress: 0 },
      { title: "Bài 11: かわいいですね", desc: "Câu cảm thán, khen ngợi.", badge: "N5 - Bài 11", progress: 0 },
      { title: "Bài 12: ちょっと待ってください", desc: "Câu mệnh lệnh nhẹ nhàng, yêu cầu.", badge: "N5 - Bài 12", progress: 0 },
      { title: "Bài 13: どれがいいですか", desc: "Lựa chọn giữa nhiều vật.", badge: "N5 - Bài 13", progress: 0 },
      { title: "Bài 14: 電話番号を教えてください", desc: "Xin thông tin liên lạc.", badge: "N5 - Bài 14", progress: 0 },
      { title: "Bài 15: 病気で学校を休みました", desc: "Nguyên nhân, lý do.", badge: "N5 - Bài 15", progress: 0 },
      { title: "Bài 16: あそこに犬がいます", desc: "Động từ chỉ sự tồn tại.", badge: "N5 - Bài 16", progress: 0 },
      { title: "Bài 17: 春が一番好きです", desc: "So sánh nhất.", badge: "N5 - Bài 17", progress: 0 },
      { title: "Bài 18: 日本語を勉強したいです", desc: "Thể mong muốn (たい).", badge: "N5 - Bài 18", progress: 0 },
      { title: "Bài 19: 映画を見に行きます", desc: "Mục đích của hành động.", badge: "N5 - Bài 19", progress: 0 },
      { title: "Bài 20: 東京は大阪より大きい", desc: "So sánh hơn.", badge: "N5 - Bài 20", progress: 0 },
      { title: "Bài 21: 漢字を書くことができます", desc: "Diễn tả khả năng.", badge: "N5 - Bài 21", progress: 0 },
      { title: "Bài 22: 友達と約束しました", desc: "Thể て + います (kết quả).", badge: "N5 - Bài 22", progress: 0 },
      { title: "Bài 23: プレゼントをもらいました", desc: "Động từ cho - nhận.", badge: "N5 - Bài 23", progress: 0 },
      { title: "Bài 24: もしもし、田中です", desc: "Hội thoại qua điện thoại.", badge: "N5 - Bài 24", progress: 0 },
      { title: "Bài 25: お正月に帰省します", desc: "Văn hóa Nhật Bản, kỳ nghỉ lễ.", badge: "N5 - Bài 25", progress: 0 },
    ],
    vocab: [
      { word: "こんにちは", kanji: "-", meaning: "Chào buổi chiều", audio: true },
      { word: "おはようございます", kanji: "-", meaning: "Chào buổi sáng", audio: true },
      { word: "こんばんは", kanji: "-", meaning: "Chào buổi tối", audio: true },
      { word: "さようなら", kanji: "-", meaning: "Tạm biệt", audio: true },
      { word: "ありがとうございます", kanji: "-", meaning: "Cảm ơn", audio: true },
      { word: "すみません", kanji: "-", meaning: "Xin lỗi", audio: true },
      { word: "せんせい", kanji: "先生", meaning: "Giáo viên", audio: true },
      { word: "がくせい", kanji: "学生", meaning: "Học sinh / Sinh viên", audio: true },
      { word: "かいしゃいん", kanji: "会社員", meaning: "Nhân viên công ty", audio: true },
      { word: "くるま", kanji: "車", meaning: "Xe hơi", audio: true },
      { word: "でんわ", kanji: "電話", meaning: "Điện thoại", audio: true },
      { word: "ほん", kanji: "本", meaning: "Sách", audio: true },
      { word: "ねこ", kanji: "猫", meaning: "Con mèo", audio: true },
      { word: "いぬ", kanji: "犬", meaning: "Con chó", audio: true },
      { word: "みず", kanji: "水", meaning: "Nước", audio: true },
      { word: "ごはん", kanji: "ご飯", meaning: "Cơm", audio: true },
      { word: "やさい", kanji: "野菜", meaning: "Rau củ", audio: true },
      { word: "くだもの", kanji: "果物", meaning: "Trái cây", audio: true },
      { word: "おちゃ", kanji: "お茶", meaning: "Trà", audio: true },
      { word: "コーヒー", kanji: "-", meaning: "Cà phê", audio: true },
    ],
    grammar: [
      { title: "1. Cấu trúc [N1] は [N2] です", explanation: "Khẳng định N1 là N2.", example: "わたし は がくせい です (Tôi là học sinh)." },
      { title: "2. Câu hỏi か", explanation: "Thêm か vào cuối câu để tạo câu hỏi.", example: "これはほんですか？ (Đây là sách phải không?)" },
      { title: "3. Đại từ chỉ định これ・それ・あれ", explanation: "Chỉ vật ở gần/ xa người nói.", example: "これはペンです (Đây là bút)." },
      { title: "4. Đại từ chỉ nơi chốn ここ・そこ・あそこ", explanation: "Chỉ địa điểm.", example: "ここはがっこうです (Đây là trường học)." },
      { title: "5. Cấu trúc [N1] の [N2]", explanation: "Liên kết danh từ, chỉ sở hữu.", example: "わたしのほん (Sách của tôi)." },
      { title: "6. Tính từ い (イ形容詞)", explanation: "Tính từ đuôi い.", example: "おいしい (Ngon), たかい (Cao/Đắt)." },
      { title: "7. Tính từ な (ナ形容詞)", explanation: "Tính từ đuôi な.", example: "しずかな (Yên tĩnh), きれいな (Đẹp)." },
      { title: "8. Phủ định của です", explanation: "Dùng じゃありません hoặc ではありません.", example: "これはほんじゃありません (Đây không phải sách)." },
      { title: "9. Thể quá khứ của です", explanation: "Dùng でした.", example: "きのうはあつかったです (Hôm qua đã nóng)." },
      { title: "10. Động từ thể ます", explanation: "Thể lịch sự của động từ.", example: "たべます (Ăn), のみます (Uống)." },
    ],
    stat: { lessonsDone: 12, totalLessons: 25, percent: 48, text: "Bạn đã hoàn thành gần 50% chặng đường N5!" },
  },
  N4: {
    lessons: [
      { title: "Bài 1: 許可をもらう", desc: "Cách xin phép và cho phép.", badge: "N4 - Bài 1", progress: 30 },
      { title: "Bài 2: 義務・必要", desc: "Diễn tả sự bắt buộc, cần thiết.", badge: "N4 - Bài 2", progress: 10 },
      { title: "Bài 3: 意向形", desc: "Học về thể ý chí.", badge: "N4 - Bài 3", progress: 0 },
      { title: "Bài 4: 受身形", desc: "Thể bị động cơ bản.", badge: "N4 - Bài 4", progress: 0 },
      { title: "Bài 5: 条件形", desc: "Câu điều kiện (ば, たら, なら).", badge: "N4 - Bài 5", progress: 0 },
      { title: "Bài 6: 使役形", desc: "Thể sai khiến.", badge: "N4 - Bài 6", progress: 0 },
      { title: "Bài 7: 敬語入門", desc: "Kính ngữ cơ bản.", badge: "N4 - Bài 7", progress: 0 },
      { title: "Bài 8: 授受表現", desc: "Cách cho - nhận (あげる, くれる, もらう).", badge: "N4 - Bài 8", progress: 0 },
      { title: "Bài 9: 〜てしまう", desc: "Diễn tả sự tiếc nuối hoặc hoàn thành.", badge: "N4 - Bài 9", progress: 0 },
      { title: "Bài 10: 〜ておく", desc: "Chuẩn bị trước cho tương lai.", badge: "N4 - Bài 10", progress: 0 },
      { title: "Bài 11: 〜てみる", desc: "Thử làm gì đó.", badge: "N4 - Bài 11", progress: 0 },
      { title: "Bài 12: 〜そうです", desc: "Nghe nói / Trông có vẻ.", badge: "N4 - Bài 12", progress: 0 },
      { title: "Bài 13: 〜すぎる", desc: "Quá mức.", badge: "N4 - Bài 13", progress: 0 },
      { title: "Bài 14: 〜ながら", desc: "Vừa làm gì vừa làm gì.", badge: "N4 - Bài 14", progress: 0 },
      { title: "Bài 15: 〜ばかり", desc: "Chỉ toàn là...", badge: "N4 - Bài 15", progress: 0 },
      { title: "Bài 16: 〜はず", desc: "Chắc hẳn là... (phỏng đoán).", badge: "N4 - Bài 16", progress: 0 },
      { title: "Bài 17: 〜かもしれません", desc: "Có lẽ, có thể (khả năng).", badge: "N4 - Bài 17", progress: 0 },
      { title: "Bài 18: 〜のに", desc: "Mặc dù... nhưng...", badge: "N4 - Bài 18", progress: 0 },
      { title: "Bài 19: 〜させていただく", desc: "Xin phép được làm gì (khiêm nhường).", badge: "N4 - Bài 19", progress: 0 },
      { title: "Bài 20: 〜によると", desc: "Theo như...", badge: "N4 - Bài 20", progress: 0 },
      { title: "Bài 21: 〜に対して", desc: "Đối với...", badge: "N4 - Bài 21", progress: 0 },
      { title: "Bài 22: 〜によって", desc: "Tùy vào / Bởi vì.", badge: "N4 - Bài 22", progress: 0 },
      { title: "Bài 23: 〜に関して", desc: "Liên quan đến...", badge: "N4 - Bài 23", progress: 0 },
      { title: "Bài 24: 会話・聞き返し", desc: "Kỹ năng giao tiếp, hỏi lại.", badge: "N4 - Bài 24", progress: 0 },
      { title: "Bài 25: 読解・メールの書き方", desc: "Đọc hiểu, viết email cơ bản.", badge: "N4 - Bài 25", progress: 0 },
    ],
    vocab: [
      { word: "おかげさまで", kanji: "お陰様で", meaning: "Nhờ có bạn / May mắn", audio: true },
      { word: "しゅうしょく", kanji: "就職", meaning: "Tìm việc làm", audio: true },
      { word: "けいけん", kanji: "経験", meaning: "Kinh nghiệm", audio: true },
      { word: "もんだい", kanji: "問題", meaning: "Vấn đề, bài tập", audio: true },
      { word: "かいしゃいん", kanji: "会社員", meaning: "Nhân viên công ty", audio: true },
      { word: "しょうらい", kanji: "将来", meaning: "Tương lai", audio: true },
      { word: "けっこん", kanji: "結婚", meaning: "Kết hôn", audio: true },
      { word: "りょこう", kanji: "旅行", meaning: "Du lịch", audio: true },
      { word: "びょうき", kanji: "病気", meaning: "Bệnh tật", audio: true },
      { word: "たいへん", kanji: "大変", meaning: "Vất vả, khó khăn", audio: true },
      { word: "しんぱい", kanji: "心配", meaning: "Lo lắng", audio: true },
      { word: "じゅうぶん", kanji: "十分", meaning: "Đủ, đầy đủ", audio: true },
      { word: "まんぞく", kanji: "満足", meaning: "Hài lòng", audio: true },
      { word: "きゅうりょう", kanji: "給料", meaning: "Lương", audio: true },
      { word: "しょくば", kanji: "職場", meaning: "Nơi làm việc", audio: true },
      { word: "せいかく", kanji: "性格", meaning: "Tính cách", audio: true },
      { word: "けっか", kanji: "結果", meaning: "Kết quả", audio: true },
      { word: "りゆう", kanji: "理由", meaning: "Lý do", audio: true },
      { word: "ちゅうい", kanji: "注意", meaning: "Chú ý", audio: true },
      { word: "じゅんび", kanji: "準備", meaning: "Chuẩn bị", audio: true },
    ],
    stat: { lessonsDone: 8, totalLessons: 25, percent: 32, text: "Bạn đã hoàn thành 32% lộ trình N4!" },
  },
};

// ==============================================================
// ĐÃ SỬA LỖI TẠI ĐÂY: Tự động lấy level từ URL thay vì ép cứng N5
// ==============================================================
let currentLevel = new URLSearchParams(window.location.search).get('level') || "N5";

// ==========================================
// 3. HÀM CẬP NHẬT GIAO DIỆN (UI)
// ==========================================
window.updateUI = function() {
    const levelData = data[currentLevel];
    if (!levelData) return;

    document.querySelectorAll("#level-title, #vocab-level, #grammar-level").forEach((el) => {
        if (el) el.innerText = currentLevel;
    });

    const statLessons = document.getElementById("stat-lessons");
    if (statLessons && !document.querySelector('.side-menu')) {
        statLessons.innerText = `${levelData.stat.lessonsDone} / ${levelData.stat.totalLessons}`;
    }
    const progressPercent = document.getElementById("progress-percent");
    if (progressPercent && !document.querySelector('.side-menu')) progressPercent.innerText = levelData.stat.percent + "%";
    const progressText = document.getElementById("progress-text");
    if (progressText && !document.querySelector('.side-menu')) progressText.innerText = levelData.stat.text;

    // Cập nhật Bảng Từ Vựng
    const vocabBody = document.getElementById("vocab-body");
    if (vocabBody && levelData.vocab) {
        vocabBody.innerHTML = "";
        levelData.vocab.forEach((item) => {
            let textToRead = (item.kanji && item.kanji !== "-") ? item.kanji : item.word;
            let safeTextToRead = textToRead.replace(/'/g, "\\'");
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${item.word}</td>
                <td>${item.kanji}</td>
                <td>${item.meaning}</td>
                <td><i class="fas fa-volume-up" onclick="playAudio(event, '${safeTextToRead}')" title="Nghe phát âm" style="cursor:pointer; color: var(--pink-primary); font-size: 1.2rem; transition: 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'"></i></td>
            `;
            vocabBody.appendChild(row);
        });
    }

    // Cập nhật Danh sách Ngữ Pháp
    const grammarContainer = document.getElementById("grammar-list");
    if (grammarContainer && levelData.grammar) {
        grammarContainer.innerHTML = "";
        levelData.grammar.forEach((gram) => {
            const div = document.createElement("div");
            div.className = "lesson-card";
            div.style.marginBottom = "15px";
            div.innerHTML = `
                <h3 style="color:var(--pink-primary); margin-bottom: 10px;">${gram.title}</h3>
                <p style="margin-bottom: 5px;"><b>Giải thích:</b> ${gram.explanation}</p>
                <p style="background:#f9f9f9; padding:12px; border-radius:10px; margin-top:10px; border-left: 4px solid #ff6b81;">Ví dụ: ${gram.example}</p>
            `;
            grammarContainer.appendChild(div);
        });
    }
};

// ==========================================
// 4. BỘ ĐIỀU HƯỚNG MƯỢT MÀ CÓ CALLBACK (ĐÃ FIX LỖI CACHE)
// ==========================================
window.loadPage = function(pageName, level = currentLevel, params = '', onCompleteCallback = null) {
    // ==============================================================
    // ĐÃ SỬA LỖI TẠI ĐÂY: Đồng bộ biến currentLevel để UI không bị đè sai
    // ==============================================================
    currentLevel = level; 
    
    const contentArea = document.getElementById('dynamic-content');
    if (!contentArea) return;

    // TẠO MÃ CHỐNG LƯỜI (Cache-buster): Ép trình duyệt luôn tải mới
    const timestamp = new Date().getTime();
    const url = `index.php?page=${pageName}&level=${level}${params}&ajax=1&t=${timestamp}`;
    const browserUrl = `index.php?page=${pageName}&level=${level}${params}`;

    contentArea.style.opacity = '0';

    setTimeout(() => {
        // Thêm { cache: 'no-store' } để tuyệt đối cấm trình duyệt lưu cache
        fetch(url, { cache: 'no-store' })
            .then(res => res.text())
            .then(html => {
                contentArea.innerHTML = html;
                window.history.pushState({ page: pageName, level: level }, '', browserUrl);
                
                const scripts = contentArea.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                window.document.dispatchEvent(new Event("DOMContentLoaded", { bubbles: true, cancelable: true }));
                updateUI();

                if (onCompleteCallback) {
                    onCompleteCallback(); 
                } else {
                    window.scrollTo(0, 0); 
                }

                setTimeout(() => {
                    contentArea.style.opacity = '1';
                }, 50);
            })
            .catch(err => {
                console.error("Lỗi tải trang ngầm:", err);
                window.location.href = browserUrl; 
            });
    }, 250); 
};

// ==========================================
// 5. KHỞI TẠO CÁC SỰ KIỆN KHI TRANG LOAD
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // Hàm bôi sáng menu
    function highlightActiveMenu(pageName) {
        document.querySelectorAll('.side-menu a, header a, .nav-links a, .top-menu a').forEach(a => {
            a.classList.remove('active');
            if (!a.closest('.side-menu') && !a.closest('.logo')) {
                a.style.color = ''; 
                a.style.fontWeight = '600'; 
            }
        });

        document.querySelectorAll('.side-menu a, header a, .nav-links a, .top-menu a').forEach(a => {
            const href = a.getAttribute('href') || '';
            let isMatch = false;

            if (pageName === 'contact') {
                if (href.includes('contact') || href.includes('#contact')) isMatch = true;
            } else {
                if (href.includes(`page=${pageName}`) && !href.includes('contact')) isMatch = true;
                if (pageName === 'home' && (href === 'index.php' || href === '/')) isMatch = true;
            }

            if (isMatch) {
                a.classList.add('active');
                if (!a.closest('.side-menu') && !a.closest('.logo')) {
                    a.style.color = 'var(--pink-primary, #ff6b81)';
                    a.style.fontWeight = 'bold';
                }
            }
        });
    }

    // Hàm cuộn tới phần liên hệ bằng ID
    function scrollToContact() {
        const contactBlock = document.getElementById('contact-section') 
                          || document.getElementById('contact') 
                          || document.querySelector('.contact-form');
                          
        if (contactBlock) {
            contactBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        }
    }

    // Xử lý nút Back của trình duyệt
    window.onpopstate = function() { location.reload(); };

    // Set màu ngay khi tải F5
    const initialPage = new URLSearchParams(window.location.search).get('page') || 'home';
    if (window.location.hash.includes('contact') || initialPage === 'contact') {
        highlightActiveMenu('contact');
        setTimeout(scrollToContact, 400); 
    } else {
        highlightActiveMenu(initialPage);
    }

    // BẮT SỰ KIỆN CLICK MENU 
    document.querySelectorAll('.side-menu a, header a, .nav-links a, .top-menu a, .logo a').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href') || '';
            
            if (href.includes('javascript:')) return;

            const isContactLink = href.includes('contact') || href.includes('#contact');
            const hasPageParam = href.includes('page=');
            
            if (hasPageParam || isContactLink || href === 'index.php') {
                e.preventDefault();

                const urlParams = new URLSearchParams(href.split('?')[1] || '');
                let page = urlParams.get('page') || 'home';
                if (isContactLink) page = 'contact';

                // Lấy trang HIỆN TẠI đang đứng
                const currentUrlPage = new URLSearchParams(window.location.search).get('page') || 'home';
                const authPages = ['login', 'register', 'forgot_password'];

                // --- FIX LỖI: ĐANG Ở TRANG ĐĂNG NHẬP, HOẶC ĐÍCH ĐẾN LÀ TRANG ĐĂNG NHẬP ---
                // Phải bắt buộc tải lại toàn bộ web để reset Layout
                if (authPages.includes(page) || authPages.includes(currentUrlPage)) {
                    // Nếu đang ở đăng nhập mà bấm "Liên hệ"
                    if (page === 'contact') {
                        window.location.href = 'index.php?page=home#footer-contact';
                    } else {
                        // Tải lại full trang để hiện lại Sidebar
                        window.location.href = href; 
                    }
                    return;
                }

                // --- NẾU BẤM VÀO LIÊN HỆ TỪ CÁC TRANG BÌNH THƯỜNG ---
                if (page === 'contact') {
                    if (currentUrlPage === 'home') {
                        scrollToContact();
                    } else {
                        loadPage('home', currentLevel, '', function() {
                            setTimeout(scrollToContact, 150);
                        });
                    }
                    highlightActiveMenu('contact');
                    return;
                }

                // --- NẾU BẤM VÀO CÁC TRANG KHÁC (Từ vựng, Luyện tập...) ---
                const level = urlParams.get('level') || currentLevel;
                loadPage(page, level); 
                highlightActiveMenu(page);
            }
        });
    });

    // Các sự kiện thay đổi N5/N4
    const btnN5 = document.querySelector('.btn-lvl[data-level="N5"]');
    const btnN4 = document.querySelector('.btn-lvl[data-level="N4"]');
    
    if (btnN5 && btnN4) {
        btnN5.addEventListener("click", () => { currentLevel = "N5"; updateUI(); });
        btnN4.addEventListener("click", () => { currentLevel = "N4"; updateUI(); });
    }

    updateUI();
});