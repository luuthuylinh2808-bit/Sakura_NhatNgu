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

<style>
    :root { --pink-primary: #ff6b81; --bg-light: #fdf6f6; }
    .ja-font { font-family: 'Noto Sans JP', sans-serif; }

    /* Bỏ qua .container vì index.php đã có khung main-container */
    .card { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 25px; }

    .lesson-banner { display: flex; justify-content: space-between; align-items: center; border-left: 6px solid var(--pink-primary); }
    .badge-n5 { background: #fff1f3; color: var(--pink-primary); padding: 5px 15px; border-radius: 12px; font-weight: 800; font-size: 13px; }
    
    .tabs-pill-nav { display: flex; background: #f1f3f5; border-radius: 20px; padding: 6px; gap: 6px; margin-bottom: 25px; }
    .tab-item {
        flex: 1; border: none; background: none; padding: 15px 0; cursor: pointer;
        border-radius: 15px; color: #888; font-weight: 800;
        display: flex; flex-direction: column; align-items: center; gap: 6px; transition: 0.3s;
    }
    .tab-item i { font-size: 1.2rem; }
    .tab-item.active { background: #fff; color: var(--pink-primary); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

    .vocab-table { width: 100%; border-collapse: collapse; }
    .vocab-table td { padding: 20px 15px; border-bottom: 1px solid #f9f9f9; }
    .txt-ja { font-size: 1.3rem; font-weight: 700; color: #333; }
    .txt-meaning { font-weight: 700; color: #444; text-align: right; }
    
    .quiz-option {
        display: flex; align-items: center; gap: 10px; padding: 12px 15px;
        background: #f8f9fa; border: 1px solid #eee; border-radius: 12px;
        cursor: pointer; transition: 0.3s; font-weight: 600; margin-bottom: 10px;
    }
    .quiz-option:hover { background: var(--pink-primary); color: white; border-color: var(--pink-primary); }

    /* CSS CHO NÚT AUDIO */
    .btn-audio {
        color: var(--pink-primary); 
        margin-left: 10px; 
        cursor: pointer; 
        font-size: 1.1rem;
        transition: 0.2s;
        padding: 5px;
    }
    .btn-audio:hover {
        transform: scale(1.3);
        color: #ff4757;
    }
</style>

<div class="card lesson-banner">
    <div>
        <span class="badge-n5">BÀI <?= $lesson['order_index'] ?></span>
        <h1 style="font-size: 28px; margin: 12px 0 5px; color: #333;" class="ja-font"><?= htmlspecialchars($lesson['title']) ?></h1>
        <p style="color: #888; font-size: 15px;"><?= htmlspecialchars($lesson['description']) ?></p>
    </div>
    <i class="fas fa-seedling" style="font-size: 60px; color: #ffb7c5; opacity: 0.4;"></i>
</div>

<div class="card">
    <div class="tabs-pill-nav">
        <button class="tab-item active" onclick="openTab(event, 'vocab')">
            <i class="fas fa-layer-group"></i>
            <span>Từ vựng</span>
        </button>
        <button class="tab-item" onclick="openTab(event, 'grammar')">
            <i class="fas fa-book-open"></i>
            <span>Ngữ pháp</span>
        </button>
        <button class="tab-item" onclick="openTab(event, 'exercise')">
            <i class="fas fa-pencil-alt"></i>
            <span>Bài tập</span>
        </button>
    </div>

    <div id="vocab" class="tab-panel" style="display: block;">
        <h3 style="margin-bottom: 20px; font-weight: 800;">Danh sách từ vựng</h3>
        <table class="vocab-table">
            <?php if (mysqli_num_rows($vocab_result) > 0): ?>
                <?php while($v = mysqli_fetch_assoc($vocab_result)): ?>
                <?php 
                    // Lấy từ để phát âm (Ưu tiên Kanji, nếu không có thì đọc Hiragana)
                    $textToRead = (!empty($v['kanji']) && trim($v['kanji']) !== '-') ? $v['kanji'] : $v['hiragana'];
                    $safeText = str_replace("'", "\'", $textToRead);
                ?>
                <tr>
                    <td class="ja-font">
                        <span class="txt-ja"><?= htmlspecialchars($v['kanji']) ?></span>
                        <span style="font-size: 14px; color: #888;">(<?= htmlspecialchars($v['hiragana']) ?>)</span>
                        <i class="fas fa-volume-up btn-audio" onclick="playAudio(event, '<?= htmlspecialchars($safeText) ?>')" title="Nghe phát âm"></i>
                    </td>
                    <td class="txt-meaning"><?= htmlspecialchars($v['meaning']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="2" style="text-align: center; color: #999; padding: 20px;">Chưa có dữ liệu từ vựng.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <div id="grammar" class="tab-panel" style="display: none;">
        <h3 style="margin-bottom: 25px; font-weight: 800;">Kiến thức Ngữ pháp</h3>
        <?php if (mysqli_num_rows($grammar_result) > 0): ?>
            <?php while($g = mysqli_fetch_assoc($grammar_result)): ?>
                <div class="grammar-card" style="background: #fff; border-radius: 20px; padding: 25px; margin-bottom: 20px; border: 1px solid #f0f0f0;">
                    <div style="font-weight: 800; color: var(--pink-primary); margin-bottom: 10px;">Cấu trúc:</div>
                    <div class="ja-font" style="background: #fff8f9; padding: 10px; border-radius: 10px; font-size: 18px; margin-bottom: 15px;">
                        <?= htmlspecialchars($g['structure']) ?>
                    </div>
                    <p><b>Ý nghĩa:</b> <?= htmlspecialchars($g['meaning']) ?></p>
                    
                    <?php $safeExample = str_replace("'", "\'", $g['example']); ?>
                    <p style="margin-top: 10px; color: #666; display: flex; align-items: center; gap: 5px;">
                        <b>Ví dụ:</b> <span class="ja-font"><?= htmlspecialchars($g['example']) ?></span>
                        <i class="fas fa-volume-up btn-audio" onclick="playAudio(event, '<?= htmlspecialchars($safeExample) ?>')" title="Nghe câu ví dụ"></i>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; color: #999;">Nội dung đang cập nhật.</p>
        <?php endif; ?>
    </div>

    <div id="exercise" class="tab-panel" style="display: none;">
        <h3 style="margin-bottom: 20px; font-weight: 800;">Luyện tập trắc nghiệm</h3>
        <form id="quizForm">
            <?php if (mysqli_num_rows($quiz_result) > 0): ?>
                <?php $count = 1; while($q = mysqli_fetch_assoc($quiz_result)): ?>
                <?php $safeQuestion = str_replace("'", "\'", $q['question']); ?>
                <div class="quiz-item" style="margin-bottom: 25px;" id="q_cnt_<?= $count ?>">
                    <p style="font-weight: 800; margin-bottom: 15px; display: flex; align-items: center; gap: 5px;">
                        Câu <?= $count ?>: <?= htmlspecialchars($q['question']) ?>
                        <i class="fas fa-volume-up btn-audio" onclick="playAudio(event, '<?= htmlspecialchars($safeQuestion) ?>')" title="Nghe câu hỏi"></i>
                    </p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <?php $correct = trim($q['correct_option']); ?>
                        <?php foreach(['A', 'B', 'C', 'D'] as $opt): ?>
                            <label class="quiz-option" id="label_<?= $count ?>_<?= $opt ?>">
                                <input type="radio" name="q<?= $count ?>" value="<?= $opt ?>" data-ans="<?= ($correct == $opt) ? 'yes' : 'no' ?>">
                                <span><?= $opt ?>. <?= htmlspecialchars($q['option_'.$opt]) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $count++; endwhile; ?>
                <button type="button" onclick="handleCheckQuiz()" class="tab-item active" style="width: 100%; border: none; padding: 15px; font-size: 16px;">Kiểm tra kết quả</button>
                <div id="resultSummary" style="display: none; margin-top: 20px; padding: 20px; border-radius: 15px; text-align: center; border: 2px dashed var(--pink-primary);">
                    <div style="font-size: 24px; font-weight: 900;" id="finalScore"></div>
                    <p id="motivateText"></p>
                </div>
            <?php else: ?>
                <p>Chưa có bài tập.</p>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    let tabPanels = document.getElementsByClassName("tab-panel");
    for (let i = 0; i < tabPanels.length; i++) tabPanels[i].style.display = "none";
   
    let tabItems = document.getElementsByClassName("tab-item");
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

            labelElement.style.background = isCorrect ? "#e6ffed" : (radio.checked ? "#fff5f5" : "#f8f9fa");
            labelElement.style.border = isCorrect ? "2px solid #28a745" : (radio.checked ? "2px solid #dc3545" : "1px solid #eee");
           
            if (radio.checked && isCorrect) score++;
        });
    }

    document.getElementById('resultSummary').style.display = 'block';
    document.getElementById('finalScore').innerText = "Điểm của bạn: " + score + " / " + total;
    document.getElementById('motivateText').innerText = score === total ? "Tuyệt vời! 🎉" : "Cố gắng lên nhé! 🌸";

    // =======================================================
    // GỬI API CẬP NHẬT TIẾN ĐỘ SAU KHI NỘP BÀI TRẮC NGHIỆM
    // =======================================================
    <?php if(isset($_SESSION['user_id'])): ?>
        let formData = new FormData();
        formData.append('id_lesson', <?php echo $id_lesson; ?>);
        
        fetch('api_update_progress.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            console.log("Tiến độ đã được lưu thành công!");
            
            // Tự động cộng 1 vào bộ đếm "Bài đã học" ở Sidebar phải
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