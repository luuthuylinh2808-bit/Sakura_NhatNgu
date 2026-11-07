<?php
// 1. Kiểm tra ID bài học
if (!isset($_GET['id_lesson'])) {
    echo "Không tìm thấy mã bài học.";
    return;
}
$id_lesson = (int)$_GET['id_lesson'];

$conn = mysqli_connect("localhost", "root", "", "sakuraweb");
if (!$conn) {
    die("Lỗi kết nối: " . mysqli_connect_error());
}

// Lấy thông tin bài học
$sql = "SELECT * FROM lessons WHERE id_lesson = $id_lesson";
$result = mysqli_query($conn, $sql);
$lesson = mysqli_fetch_assoc($result);

if (!$lesson) {
    echo "Bài học không tồn tại.";
    return;
}

// ==========================================================
// TRUY VẤN DỮ LIỆU PHỤ
$vocab_result = mysqli_query($conn, "SELECT * FROM vocabulary WHERE id_lesson = $id_lesson");
$grammar_result = mysqli_query($conn, "SELECT * FROM grammars WHERE id_lesson = $id_lesson");
$quiz_result = mysqli_query($conn, "SELECT * FROM exercise WHERE id_lesson = $id_lesson");
?>

<!-- BỔ SUNG FONT CHUẨN TIẾNG VIỆT VÀ TIẾNG NHẬT -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Noto+Sans+JP:wght@400;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --pink-primary: #ff6b81; 
        --pink-soft-bg: #fff5f7;
        --shadow-soft: rgba(255, 107, 129, 0.15);
    }

    /* ĐẶT FONT CHUẨN TIẾNG VIỆT CHO TOÀN BỘ TRANG BÀI HỌC */
    .hoc-page-wrapper, 
    .hoc-page-wrapper h1, 
    .hoc-page-wrapper h2, 
    .hoc-page-wrapper h3, 
    .hoc-page-wrapper p, 
    .hoc-page-wrapper span, 
    .hoc-page-wrapper button, 
    .hoc-page-wrapper label {
        font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }

    /* CHỈ NƠI NÀO LÀ CHỮ NHẬT THẬT SỰ MỚI DÙNG FONT NOTO SANS JP */
    .ja-text { 
        font-family: 'Noto Sans JP', 'Be Vietnam Pro', sans-serif !important; 
    }   

    /* --- 1. BANNER TIÊU ĐỀ BÀI HỌC SOFT 3D --- */
    .lesson-header-card {
        background: #ffffff;
        border: 1px solid #ffe6ea;
        border-radius: 20px;
        padding: 24px 28px;
        margin-bottom: 22px;
        box-shadow: 0 8px 25px var(--shadow-soft);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }
    .lesson-badge-tag {
        background: #ff6b81;
        color: #ffffff;
        font-weight: 800;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
        margin-bottom: 8px;
        box-shadow: 0 4px 10px rgba(255, 107, 129, 0.3);
    }
    
    /* --- 2. THANH TAB CHUYỂN ĐỔI --- */
    .lesson-tabs-container {
        display: flex;
        gap: 10px;
        background: #f8f9fa;
        padding: 6px;
        border-radius: 18px;
        margin-bottom: 25px;
        border: 1px solid #f1f3f5;
    }
    .tab-btn-item {
        flex: 1;
        text-align: center;
        padding: 12px 16px;
        font-weight: 800;
        font-size: 15px;
        color: #888888;
        background: transparent;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .tab-btn-item.active {
        background: #ff6b81;
        color: #ffffff;
        box-shadow: 0 6px 15px rgba(255, 107, 129, 0.35);
    }
    .tab-btn-item:hover:not(.active) {
        color: #ff6b81;
        background: #fff0f3;
    }

    /* --- 3. KHỐI NỘI DUNG CHÍNH --- */
    .content-panel-card {
        background: #ffffff;
        border: 1px solid #ffe6ea;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 8px 25px var(--shadow-soft);
        margin-bottom: 25px;
    }

    /* CARD TỪ VỰNG SOFT SAKURA */
    .vocab-card-item {
        background: #ffffff;
        border: 1px solid #ffeaf0;
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 12px;
        box-shadow: 0 4px 15px rgba(255, 107, 129, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.25s ease;
    }
    .vocab-card-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 129, 0.18);
        border-color: #ffb7c5;
    }
    .vocab-jp-word {
        font-size: 20px;
        font-weight: 800;
        color: #2d3436;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .vocab-furigana {
        font-size: 14px;
        color: #888888;
        font-weight: 600;
    }
    .vocab-mean {
        font-size: 15px;
        font-weight: 700;
        color: #4a4a4a;
    }

    /* CARD NGỮ PHÁP */
    .grammar-card-box {
        background: #fff0f3;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 18px;
        border: 1px solid #ffe0e6;
    }
    .grammar-structure-title {
        font-size: 17px;
        font-weight: 800;
        color: #ff4757;
        background: #ffffff;
        padding: 8px 16px;
        border-radius: 12px;
        display: inline-block;
        margin-bottom: 12px;
        box-shadow: 0 3px 10px rgba(255, 71, 87, 0.12);
    }

    /* CARD BÀI TẬP TRẮC NGHIỆM */
    .quiz-question-card {
        background: #ffffff;
        border: 1px solid #f0f0f0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .quiz-option {
        display: flex; 
        align-items: center; 
        gap: 10px; 
        padding: 12px 18px;
        background: #f8f9fa; 
        border: 1px solid #f1f3f5; 
        border-radius: 14px;
        cursor: pointer; 
        transition: all 0.2s ease; 
        font-weight: 700; 
        margin-bottom: 10px;
        color: #444;
    }
    .quiz-option:hover { 
        background: #ff6b81; 
        color: white; 
        border-color: #ff6b81;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.3);
    }

    /* NÚT PHÁT ÂM AUDIO SOFT */
    .btn-audio-play {
        width: 38px;
        height: 38px;
        background: #fff0f3;
        border: none;
        border-radius: 50%;
        color: #ff4757;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 3px 8px rgba(255, 71, 87, 0.15);
        transition: all 0.2s ease;
        margin-left: 8px;
    }
    .btn-audio-play:hover {
        background: #ff4757;
        color: #ffffff;
        transform: scale(1.1);
        box-shadow: 0 5px 12px rgba(255, 71, 87, 0.35);
    }
</style>

<div class="hoc-page-wrapper">
    <!-- BANNER BÀI HỌC -->
    <div class="lesson-header-card">
        <div>
            <span class="lesson-badge-tag"><?= htmlspecialchars($lesson['level']) ?> - BÀI <?= $lesson['order_index'] ?></span>
            <h1 style="font-size: 24px; margin: 6px 0; color: #2d3436; font-weight: 800;">
                <?= htmlspecialchars($lesson['title']) ?>
            </h1>
            <p style="color: #636e72; font-size: 14px; font-weight: 600; margin: 0;">
                <?= htmlspecialchars($lesson['description']) ?>
            </p>
        </div>
        <i class="fas fa-seedling" style="font-size: 50px; color: #ffb7c5; opacity: 0.6;"></i>
    </div>

    <!-- KHỐI CHÍNH VÀ THANH TAB -->
    <div>
        <div class="lesson-tabs-container">
            <button class="tab-btn-item active" onclick="openTab(event, 'vocab')">
                <i class="fas fa-layer-group"></i> Từ vựng
            </button>
            <button class="tab-btn-item" onclick="openTab(event, 'grammar')">
                <i class="fas fa-book-open"></i> Ngữ pháp
            </button>
            <button class="tab-btn-item" onclick="openTab(event, 'exercise')">
                <i class="fas fa-pencil-alt"></i> Bài tập
            </button>
        </div>

        <div class="content-panel-card">
            <!-- TAB 1: TỪ VỰNG -->
            <div id="vocab" class="tab-panel" style="display: block;">
                <h3 style="margin-bottom: 20px; font-weight: 800; color: #2d3436;">
                    <i class="fas fa-spell-check" style="color: #ff4757;"></i> Danh sách từ vựng
                </h3>
                <?php if (mysqli_num_rows($vocab_result) > 0): ?>
                    <?php while($v = mysqli_fetch_assoc($vocab_result)): ?>
                    <?php 
                        $textToRead = (!empty($v['kanji']) && trim($v['kanji']) !== '-') ? $v['kanji'] : $v['hiragana'];
                        $safeText = str_replace("'", "\'", $textToRead);
                    ?>
                    <div class="vocab-card-item">
                        <div class="vocab-jp-word">
                            <span class="ja-text"><?= htmlspecialchars($v['kanji']) ?></span>
                            <span class="vocab-furigana ja-text">(<?= htmlspecialchars($v['hiragana']) ?>)</span>
                            <button class="btn-audio-play" onclick="playAudio(event, '<?= htmlspecialchars($safeText) ?>')" title="Nghe phát âm">
                                <i class="fas fa-volume-up"></i>
                            </button>
                        </div>
                        <div class="vocab-mean"><?= htmlspecialchars($v['meaning']) ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #888; padding: 20px; font-weight: 700;">Chưa có dữ liệu từ vựng.</p>
                <?php endif; ?>
            </div>

            <!-- TAB 2: NGỮ PHÁP -->
            <div id="grammar" class="tab-panel" style="display: none;">
                <h3 style="margin-bottom: 20px; font-weight: 800; color: #2d3436;">
                    <i class="fas fa-scroll" style="color: #ff4757;"></i> Kiến thức Ngữ pháp
                </h3>
                <?php if (mysqli_num_rows($grammar_result) > 0): ?>
                    <?php while($g = mysqli_fetch_assoc($grammar_result)): ?>
                        <div class="grammar-card-box">
                            <div class="grammar-structure-title">
                                <span>Cấu trúc: </span>
                                <span class="ja-text" style="color: #ff4757; font-weight: 800;"><?= htmlspecialchars($g['structure']) ?></span>
                            </div>
                            <p style="font-size: 15px; font-weight: 700; color: #2d3436; margin: 10px 0;">
                                <b>Ý nghĩa:</b> <?= htmlspecialchars($g['meaning']) ?>
                            </p>
                            
                            <?php $safeExample = str_replace("'", "\'", $g['example']); ?>
                            <div style="background: #ffffff; border-left: 4px solid #ff4757; padding: 12px 16px; border-radius: 10px; margin-top: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                <b style="color: #ff4757;">Ví dụ:</b> 
                                <span class="ja-text" style="font-weight: 700; color: #2d3436; margin-left: 5px; font-size: 16px;"><?= htmlspecialchars($g['example']) ?></span>
                                <button class="btn-audio-play" onclick="playAudio(event, '<?= htmlspecialchars($safeExample) ?>')" title="Nghe ví dụ">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #888; padding: 20px; font-weight: 700;">Nội dung đang cập nhật.</p>
                <?php endif; ?>
            </div>

            <!-- TAB 3: BÀI TẬP TRẮC NGHIỆM -->
            <div id="exercise" class="tab-panel" style="display: none;">
                <h3 style="margin-bottom: 20px; font-weight: 800; color: #2d3436;">
                    <i class="fas fa-tasks" style="color: #ff4757;"></i> Luyện tập trắc nghiệm
                </h3>
                <form id="quizForm">
                    <?php if (mysqli_num_rows($quiz_result) > 0): ?>
                        <?php $count = 1; while($q = mysqli_fetch_assoc($quiz_result)): ?>
                        <?php $safeQuestion = str_replace("'", "\'", $q['question']); ?>
                        <div class="quiz-question-card" id="q_cnt_<?= $count ?>">
                            <p style="font-weight: 800; margin-bottom: 15px; font-size: 16px; color: #2d3436; display: flex; align-items: center; gap: 8px;">
                                <span>Câu <?= $count ?>:</span> 
                                <span class="ja-text"><?= htmlspecialchars($q['question']) ?></span>
                                <button type="button" class="btn-audio-play" onclick="playAudio(event, '<?= htmlspecialchars($safeQuestion) ?>')" title="Nghe câu hỏi">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                            </p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <?php $correct = trim($q['correct_option']); ?>
                                <?php foreach(['A', 'B', 'C', 'D'] as $opt): ?>
                                    <label class="quiz-option" id="label_<?= $count ?>_<?= $opt ?>">
                                        <input type="radio" name="q<?= $count ?>" value="<?= $opt ?>" data-ans="<?= ($correct == $opt) ? 'yes' : 'no' ?>">
                                        <span><b><?= $opt ?>.</b> <span class="ja-text"><?= htmlspecialchars($q['option_'.$opt]) ?></span></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $count++; endwhile; ?>

                        <button type="button" onclick="handleCheckQuiz()" class="tab-btn-item active" style="width: 100%; padding: 14px; font-size: 16px; margin-top: 10px;">
                            <i class="fas fa-check-circle"></i> Kiểm tra kết quả
                        </button>

                        <div id="resultSummary" style="display: none; margin-top: 20px; padding: 20px; border-radius: 16px; text-align: center; background: #fff0f3; border: 1px solid #ffe0e6; box-shadow: 0 4px 15px var(--shadow-soft);">
                            <div style="font-size: 24px; font-weight: 900; color: #ff4757;" id="finalScore"></div>
                            <p id="motivateText" style="font-weight: 800; font-size: 16px; margin-top: 5px; color: #2d3436;"></p>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #888; padding: 20px; font-weight: 700;">Chưa có bài tập.</p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    let tabPanels = document.getElementsByClassName("tab-panel");
    for (let i = 0; i < tabPanels.length; i++) tabPanels[i].style.display = "none";
   
    let tabItems = document.getElementsByClassName("tab-btn-item");
    for (let i = 0; i < tabItems.length; i++) tabItems[i].classList.remove("active");

    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
}

function handleCheckQuiz() {
    let total = <?= isset($count) ? ($count - 1) : 0 ?>;
    let score = 0;

    for (let i = 1; i <= total; i++) {
        let radios = document.getElementsByName('q' + i);
        radios.forEach(radio => {
            let labelId = 'label_' + i + '_' + radio.value;
            let labelElement = document.getElementById(labelId);
            let isCorrect = radio.getAttribute('data-ans') === 'yes';

            if (isCorrect) {
                labelElement.style.background = "#e6ffed";
                labelElement.style.borderColor = "#2ecc71";
            } else if (radio.checked) {
                labelElement.style.background = "#ffe6ea";
                labelElement.style.borderColor = "#ff4757";
            } else {
                labelElement.style.background = "#f8f9fa";
                labelElement.style.borderColor = "#f1f3f5";
            }
           
            if (radio.checked && isCorrect) score++;
        });
    }

    document.getElementById('resultSummary').style.display = 'block';
    document.getElementById('finalScore').innerText = "Điểm của bạn: " + score + " / " + total;
    document.getElementById('motivateText').innerText = score === total ? "Tuyệt vời! Bạn đã làm đúng hết 🎉" : "Cố gắng lên nhé! Hãy kiểm tra lại các đáp án chưa đúng 🌸";

    <?php if(isset($_SESSION['user_id'])): ?>
        let formData = new FormData();
        formData.append('id_lesson', <?php echo $id_lesson; ?>);
        
        fetch('api_update_progress.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            let statObj = document.getElementById('stat-lessons');
            if (statObj && !statObj.dataset.updated) {
                let textParts = statObj.innerText.split('/');
                if (textParts.length == 2) {
                    let currentCount = parseInt(textParts[0].trim()) + 1;
                    let totalCount = textParts[1].trim();
                    statObj.innerHTML = currentCount + ' / ' + totalCount;
                    statObj.dataset.updated = "true"; 
                }
            }
        }).catch(err => console.error(err));
    <?php endif; ?>
}
</script>