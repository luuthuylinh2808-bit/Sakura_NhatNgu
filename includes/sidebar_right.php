<?php
// Tính toán dữ liệu tiến độ thực tế
$percent_lesson = 0; $percent_vocab = 0; $percent_grammar = 0; $total_progress = 0;
$fav_lessons = [];

if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $uid = $_SESSION['user_id'];
   
    // 1. Lấy dữ liệu tiến độ
    $total_lessons = $conn->query("SELECT COUNT(*) as c FROM lessons")->fetch_assoc()['c'];
    $total_vocab = $conn->query("SELECT COUNT(*) as c FROM vocabulary")->fetch_assoc()['c'];
    $total_grammar = $conn->query("SELECT COUNT(*) as c FROM grammars")->fetch_assoc()['c'];
   
    $learned_lessons = $conn->query("SELECT COUNT(DISTINCT id_lesson) as c FROM user_progress WHERE id = $uid")->fetch_assoc()['c'];
    $learned_vocab = $conn->query("SELECT COUNT(*) as c FROM vocabulary WHERE id_lesson IN (SELECT id_lesson FROM user_progress WHERE id = $uid)")->fetch_assoc()['c'];
    $learned_grammar = $conn->query("SELECT COUNT(*) as c FROM grammars WHERE id_lesson IN (SELECT id_lesson FROM user_progress WHERE id = $uid)")->fetch_assoc()['c'];
    
    $percent_lesson = ($total_lessons > 0) ? round(($learned_lessons / $total_lessons) * 100) : 0;
    $percent_vocab = ($total_vocab > 0) ? round(($learned_vocab / $total_vocab) * 100) : 0;
    $percent_grammar = ($total_grammar > 0) ? round(($learned_grammar / $total_grammar) * 100) : 0;

    // Tổng tiến độ
    $total_items = $total_lessons + $total_vocab + $total_grammar;
    $learned_items = $learned_lessons + $learned_vocab + $learned_grammar;
    $total_progress = ($total_items > 0) ? round(($learned_items / $total_items) * 100) : 0;

    // 2. Lấy danh sách Bài học yêu thích
    $res_fav = $conn->query("SELECT l.id_lesson, l.title, l.order_index FROM favourite_lesson f JOIN lessons l ON f.id_lesson = l.id_lesson WHERE f.id = $uid ORDER BY f.id_lesson DESC LIMIT 3");
    if($res_fav) {
        while($row = $res_fav->fetch_assoc()) { $fav_lessons[] = $row; }
    }
}
?>

<aside class="sidebar-right-wrapper" style="display: flex; flex-direction: column; gap: 20px;">
    
    <div class="white-card" style="margin-bottom: 0;">
        <h3 style="color: #f39c12; margin-bottom: 15px; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-bolt"></i> Luyện tập nhanh
        </h3>
        <div id="sq-content" style="min-height: 100px;">
            </div>
        
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button onclick="loadPrevQuestion()" id="btn-prev-sq" style="flex: 1; background: #fffcf5; color: #888; border: 1px solid #ddd; padding: 8px; border-radius: 10px; font-size: 13px; font-weight: bold; cursor: pointer; transition: 0.2s;" disabled>
                <i class="fas fa-undo"></i> Làm lại
            </button>
            <button onclick="loadNextQuestion()" id="btn-next-sq" style="flex: 1; background: #fffcf5; color: #f39c12; border: 1px dashed #f39c12; padding: 8px; border-radius: 10px; font-size: 13px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                Câu tiếp <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="white-card" style="margin-bottom: 0;">
        <h3 style="color: #29e732; margin-bottom: 15px; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-chart-pie"></i> Tiến độ học tập
        </h3>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 14px; font-weight: 600;">
            <span>Cấp độ hiện tại:</span>
            <span style="background: #fff8e1; color: #f39c12; padding: 4px 12px; border-radius: 8px;">N5</span>
        </div>

        <div style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px; font-weight: bold; color: #444;">
                <span>Tổng tiến độ</span>
                <span style="color: #f39c12;"><?= $total_progress ?>%</span>
            </div>
            <div style="width: 100%; background: #eee; height: 8px; border-radius: 10px; overflow: hidden;">
                <div style="width: <?= $total_progress ?>%; background: #f39c12; height: 100%; border-radius: 10px;"></div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; border-top: 1px solid #f5f5f5; padding-top: 15px;">
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; color: #666; font-weight: bold;">
                    <span><i class="fas fa-book-open" style="color: var(--pink-primary); margin-right: 5px;"></i> Bài học</span>
                    <span><?= $percent_lesson ?>%</span>
                </div>
                <div style="width: 100%; background: #eee; height: 5px; border-radius: 10px;"><div style="width: <?= $percent_lesson ?>%; background: var(--pink-primary); height: 100%; border-radius: 10px;"></div></div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; color: #666; font-weight: bold;">
                    <span><i class="fas fa-book" style="color: #3498db; margin-right: 5px;"></i> Từ vựng</span>
                    <span><?= $percent_vocab ?>%</span>
                </div>
                <div style="width: 100%; background: #eee; height: 5px; border-radius: 10px;"><div style="width: <?= $percent_vocab ?>%; background: #3498db; height: 100%; border-radius: 10px;"></div></div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; color: #666; font-weight: bold;">
                    <span><i class="fas fa-shapes" style="color: #9b59b6; margin-right: 5px;"></i> Ngữ pháp</span>
                    <span><?= $percent_grammar ?>%</span>
                </div>
                <div style="width: 100%; background: #eee; height: 5px; border-radius: 10px;"><div style="width: <?= $percent_grammar ?>%; background: #9b59b6; height: 100%; border-radius: 10px;"></div></div>
            </div>
        </div>
    </div>

    <div class="white-card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: var(--pink-primary); font-size: 16px; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-heart"></i> Bài học yêu thích
            </h3>
            <a href="index.php?page=favourite_lessons" style="font-size: 12px; color: #888; text-decoration: none;">Xem thêm <i class="fas fa-chevron-right" style="font-size: 10px;"></i></a>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php if (!empty($fav_lessons)): ?>
                <?php foreach ($fav_lessons as $fav): ?>
                    <a href="index.php?page=hoc&id_lesson=<?= $fav['id_lesson'] ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; border: 1px solid #f0f0f0; border-radius: 10px; text-decoration: none; color: #444; background: #fff; transition: 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: var(--pink-light); color: var(--pink-primary); width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">
                                B<?= $fav['order_index'] ?>
                            </div>
                            <span style="font-size: 13px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;"><?= htmlspecialchars($fav['title']) ?></span>
                        </div>
                        <i class="fas fa-play-circle" style="color: #eee; font-size: 16px;"></i>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size: 12px; color: #999; text-align: center; padding: 10px;">Chưa có bài học yêu thích</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</aside>

<script>
// --- CỜ BẢO VỆ CHỐNG NHÂN BẢN ---
if (!window.isQuickQuizReady) {
    window.isQuickQuizReady = true;

    // Chuyển biến ra ngoài global để các hàm truy cập được an toàn
    window.questionHistory = [];
    window.currentIndex = -1;

    window.fetchNewQuestion = function() {
        document.getElementById('sq-content').innerHTML = '<p style="text-align:center; color:#ccc; font-size:12px; padding:20px;">Đang tải...</p>';
        fetch('api_get_random_single.php')
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    window.questionHistory.push(data.data); 
                    window.currentIndex = window.questionHistory.length - 1; 
                    window.renderSQ();
                    window.updateButtons();
                }
            })
            .catch(err => {
                 document.getElementById('sq-content').innerHTML = '<p style="text-align:center; color:#e74c3c; font-size:12px; padding:20px;">Lỗi tải câu hỏi.</p>';
            });
    }

    window.loadNextQuestion = function() {
        if (window.currentIndex < window.questionHistory.length - 1) {
            window.currentIndex++;
            window.renderSQ();
            window.updateButtons();
        } else {
            window.fetchNewQuestion();
        }
    }

    window.loadPrevQuestion = function() {
        if (window.currentIndex > 0) {
            window.currentIndex--;
            window.renderSQ();
            window.updateButtons();
        }
    }

    window.updateButtons = function() {
        const btnPrev = document.getElementById('btn-prev-sq');
        if (window.currentIndex > 0) {
            btnPrev.disabled = false;
            btnPrev.style.color = '#e74c3c';
            btnPrev.style.borderColor = '#e74c3c';
        } else {
            btnPrev.disabled = true;
            btnPrev.style.color = '#ccc';
            btnPrev.style.borderColor = '#ddd';
        }
    }

    window.renderSQ = function() {
        if(window.currentIndex < 0 || !window.questionHistory[window.currentIndex]) return;
        let currentQ = window.questionHistory[window.currentIndex];
        
        let html = `
            <p style="font-weight:700; color:#444; margin-bottom:12px; font-size:14px;">${currentQ.question}</p>
            <div style="display:flex; flex-direction:column; gap:6px;">
                ${['A','B','C','D'].map(opt => `
                    <button class="sq-btn" onclick="window.checkSQ(this, '${opt}')" style="padding:8px 12px; border:1px solid #eee; border-radius:8px; background:white; cursor:pointer; font-weight:600; text-align:left; font-size:13px; transition: 0.2s;">
                        <span style="display:inline-block; width:20px; font-weight:bold; color:#f39c12;">${opt}.</span> ${currentQ['option_'+opt]}
                    </button>
                `).join('')}
            </div>`;
        document.getElementById('sq-content').innerHTML = html;
    }

    window.checkSQ = function(btn, choice) {
        let currentQ = window.questionHistory[window.currentIndex];
        const btns = document.querySelectorAll('.sq-btn');
        btns.forEach(b => b.disabled = true);
        
        if(choice === currentQ.correct_option) {
            btn.style.background = '#e6ffed'; btn.style.borderColor = '#2ecc71'; btn.style.color = '#155724';
        } else {
            btn.style.background = '#fff5f5'; btn.style.borderColor = '#e74c3c'; btn.style.color = '#721c24';
            btns.forEach(b => { 
                if(b.innerText.startsWith(currentQ.correct_option)) {
                    b.style.border = '2px dashed #2ecc71'; 
                }
            });
        }
    }

    // Tự động tải câu hỏi ngay khi script chạy lần đầu
    window.fetchNewQuestion();
}
</script>