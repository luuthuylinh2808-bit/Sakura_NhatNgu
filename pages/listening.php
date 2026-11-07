<?php
// pages/listening.php

require_once __DIR__ . '/../config/database.php';

// 1. Xác định cấp độ (giữ chuẩn N3)
$current_level = isset($_GET['level']) ? strtoupper($_GET['level']) : 'N3';
if (!in_array($current_level, ['N3', 'N4', 'N5'])) {
    $current_level = 'N3';
}
$_SESSION['selected_level'] = $current_level;

// 2. Danh sách thể loại & Thể loại đang chọn
$categories_list = ['Tất cả', 'Hoạt hình', 'Podcast', 'Nấu ăn', 'Tin tức', 'Khác'];
$current_category = isset($_GET['category']) ? $_GET['category'] : 'Tất cả';

// 3. Truy vấn danh sách bài nghe theo Thể loại
if ($current_category !== 'Tất cả') {
    $stmt = $conn->prepare("SELECT * FROM listening_lessons WHERE category = ? ORDER BY id DESC");
    $stmt->bind_param("s", $current_category);
    $stmt->execute();
    $lessons_result = $stmt->get_result();
} else {
    $lessons_result = $conn->query("SELECT * FROM listening_lessons ORDER BY id DESC");
}

$listening_lessons = [];
if ($lessons_result) {
    while ($row = $lessons_result->fetch_assoc()) {
        $listening_lessons[] = $row;
    }
}

// 4. Lấy bài nghe được chọn
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_lesson = null;

if ($lesson_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM listening_lessons WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $current_lesson = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Nếu chưa chọn bài hoặc bài chọn không nằm trong thể loại hiện tại, lấy bài đầu tiên của danh sách
if (!$current_lesson && !empty($listening_lessons)) {
    $current_lesson = $listening_lessons[0];
    $lesson_id = (int)$current_lesson['id'];
} elseif ($current_lesson) {
    $lesson_id = (int)$current_lesson['id'];
}

// 5. Lấy câu thoại của bài nghe
$sentences = [];
if ($current_lesson) {
    $stmt = $conn->prepare(
        "SELECT * FROM listening_sentences
         WHERE lesson_id = ?
         ORDER BY sentence_number ASC"
    );
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sentences[] = $row;
    }
    $stmt->close();
}

// 6. Trích xuất ID video YouTube
$youtube_id = '';
if ($current_lesson) {
    $url = $current_lesson['youtube_url'];
    if (preg_match(
        '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
        $url,
        $matches
    )) {
        $youtube_id = $matches[1];
    }
}
?>

<link rel="stylesheet" href="assets/css/listening.css">

<div class="listening-page">

<style>
    /* CARD 1: HEADER BANNER */
    .listening-header-card {
        background: #ffffff;
        border: 1px solid #ffe6ea;
        border-radius: 20px;
        padding: 22px 28px;
        margin-bottom: 16px;
        box-shadow: 0 8px 25px rgba(255, 107, 129, 0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .listening-title-box {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .listening-header-icon {
        width: 52px;
        height: 52px;
        background: #fff0f3;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #ff6b81;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.2);
        flex-shrink: 0;
    }
    .badge-level-n3 {
        background: #ff6b81;
        color: #ffffff;
        font-size: 12px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 20px;
        margin-left: 8px;
        box-shadow: 0 3px 8px rgba(255, 107, 129, 0.3);
    }

    /* CARD 2: BỘ LỌC THỂ LOẠI & CHỌN BÀI */
    .listening-filter-card {
        background: #ffffff;
        border: 1px solid #ffe6ea;
        border-radius: 20px;
        padding: 16px 24px;
        margin-bottom: 25px;
        box-shadow: 0 6px 20px rgba(255, 107, 129, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .category-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tab-item {
        padding: 8px 18px;
        border-radius: 12px;
        background: #fff0f3;
        color: #636e72;
        font-weight: 700;
        font-size: 13.5px;
        text-decoration: none;
        border: 1px solid #ffccd5;
        transition: all 0.25s ease;
    }
    .tab-item:hover {
        background: #ffe6ea;
        color: #ff4757;
    }
    .tab-item.active {
        background: #ff6b81;
        color: #ffffff;
        border-color: #ff6b81;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.3);
    }
    .custom-select-box {
        position: relative;
        min-width: 240px;
    }
    .custom-select-pink {
        width: 100%;
        background-color: #fff0f3;
        color: #ff4757;
        font-weight: 700;
        font-size: 14px;
        padding: 10px 40px 10px 18px;
        border: 1.5px solid #ffccd5;
        border-radius: 14px;
        outline: none;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.1);
        transition: all 0.25s ease;
    }
    .custom-select-pink:hover, .custom-select-pink:focus {
        background-color: #ffe6ea;
        border-color: #ff6b81;
        box-shadow: 0 6px 16px rgba(255, 107, 129, 0.2);
    }
    .select-arrow-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #ff4757;
        pointer-events: none;
        font-size: 12px;
    }

    /* BẢNG NỘI DUNG */
    .listening-panel {
        background: #ffffff !important;
        border: 1px solid #ffe6ea !important;
        border-radius: 20px !important;
        padding: 20px !important;
        box-shadow: 0 8px 25px rgba(255, 107, 129, 0.12) !important;
        transition: all 0.25s ease;
    }
    .listening-panel:hover {
        box-shadow: 0 12px 30px rgba(255, 107, 129, 0.18) !important;
        border-color: #ffccd5 !important;
    }
    .listening-panel .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 14px;
        margin-bottom: 16px;
        border-bottom: 2px dashed #ffe0e6;
    }
    .listening-panel .panel-heading h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #2d3436;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .panel-head-icon {
        width: 34px;
        height: 34px;
        background: #fff0f3;
        color: #ff4757;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        box-shadow: 0 3px 8px rgba(255, 107, 129, 0.15);
    }
    .badge-sentence-count {
        background: #fff0f3;
        color: #ff4757;
        font-weight: 800;
        font-size: 12px;
        padding: 4px 12px;
        border-radius: 12px;
        border: 1px solid #ffccd5;
    }
    .btn-toggle-transcript {
        background: #fff0f3;
        color: #ff4757;
        border: 1px solid #ffccd5;
        border-radius: 10px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-toggle-transcript:hover {
        background: #ff4757;
        color: #ffffff;
    }
</style>

<!-- CARD 1: BANNER HEADER -->
<div class="listening-header-card">
    <div class="listening-title-box">
        <div class="listening-header-icon">
            <i class="fas fa-headphones-alt"></i>
        </div>
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 22px; font-weight: 800; color: #2d3436; display: flex; align-items: center;">
                Luyện nghe Tiếng Nhật <span class="badge-level-n3">👑 Trình độ <?= htmlspecialchars($current_level) ?></span>
            </h2>
            <p style="margin: 0; font-size: 13.5px; color: #636e72; font-weight: 600;">
                Luyện nghe và chép lại những gì bạn nghe được
            </p>
        </div>
    </div>
</div>

<!-- CARD 2: TAB THỂ LOẠI VÀ BỘ CHỌN BÀI NGHE -->
<div class="listening-filter-card">
    <!-- TABS THỂ LOẠI -->
    <div class="category-tabs">
        <?php foreach ($categories_list as $cat): ?>
            <?php $isActive = ($current_category === $cat); ?>
            <a href="index.php?page=listening&level=<?= $current_level ?>&category=<?= urlencode($cat) ?>" 
               class="tab-item <?= $isActive ? 'active' : '' ?>">
                <?= htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- DROPDOWN CHỌN BÀI NGHE -->
    <div class="custom-select-box">
        <select id="lessonSelector" class="custom-select-pink" onchange="window.location.href='index.php?page=listening&level=<?= $current_level ?>&category=<?= urlencode($current_category) ?>&id='+this.value">
            <?php if (empty($listening_lessons)): ?>
                <option value="">(Không có bài nghe thuộc thể loại này)</option>
            <?php else: ?>
                <?php foreach ($listening_lessons as $lesson): ?>
                    <option
                        value="<?= (int)$lesson['id'] ?>"
                        <?= ((int)$lesson['id'] === $lesson_id) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($lesson['title']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <i class="fas fa-chevron-down select-arrow-icon"></i>
    </div>
</div>

<?php if (!$current_lesson): ?>
    <div class="listening-empty" style="text-align: center; padding: 40px; background: #fff; border-radius: 20px;">
        <h3>Chưa có bài luyện nghe</h3>
        <p>Không tìm thấy bài nghe thuộc thể loại "<?= htmlspecialchars($current_category) ?>".</p>
    </div>
<?php else: ?>

    <!-- GIAO DIỆN 3 CỘT -->
    <div class="listening-layout">

        <!-- CỘT 1: VIDEO YOUTUBE -->
        <section class="listening-panel video-panel">
            <div class="panel-heading">
                <h3>
                    <span class="panel-head-icon"><i class="fas fa-play-circle"></i></span>
                    Video bài nghe
                </h3>
            </div>
            <div class="youtube-wrapper">
                <div id="youtube-player"></div>
            </div>
            <div class="video-controls">
                <button id="playVideoBtn" class="btn-primary">▶ Bắt đầu</button>
                <button id="replayVideoBtn" class="btn-secondary">↻ Phát lại</button>
            </div>
            <div class="video-description">
                <?= htmlspecialchars($current_lesson['description'] ?? '') ?>
            </div>
        </section>

        <!-- CỘT 2: LUYỆN NGHE -->
        <section class="listening-panel practice-panel">
            <div class="panel-heading">
                <h3>
                    <span class="panel-head-icon"><i class="fas fa-edit"></i></span>
                    Luyện chép chính tả
                </h3>
                <span id="currentSentenceNumber" class="badge-sentence-count">
                    Câu 1 / <?= count($sentences) ?>
                </span>
            </div>

            <div class="player-controls">
                <button id="previousBtn" title="Câu trước">◀</button>
                <button id="replaySentenceBtn" title="Nghe lại câu">↻</button>
                <button id="pauseSentenceBtn" title="Tạm dừng">⏸</button>
                <button id="nextSentenceBtn" title="Câu tiếp theo">▶</button>
                <select id="playbackSpeed">
                    <option value="0.5">0.5x</option>
                    <option value="0.75">0.75x</option>
                    <option value="1" selected>1x</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                </select>
            </div>

            <div class="answer-area">
                <label for="userAnswer">GÕ NHỮNG GÌ BẠN NGHE ĐƯỢC:</label>
                <textarea id="userAnswer" placeholder="Gõ câu trả lời của bạn ở đây..."></textarea>
            </div>

            <div class="answer-actions">
                <button id="checkAnswerBtn" class="btn-primary">Kiểm tra đáp án</button>
                <button id="showAnswerBtn" class="btn-secondary">Hiện đáp án</button>
            </div>

            <div id="answerFeedback"></div>

            <div class="score-area">
                Điểm: <span id="scoreDisplay">0</span> / <?= count($sentences) ?>
            </div>
        </section>

        <!-- CỘT 3: DANH SÁCH CÂU THOẠI -->
        <section class="listening-panel transcript-panel">
            <div class="panel-heading">
                <h3>
                    <span class="panel-head-icon"><i class="fas fa-list-alt"></i></span>
                    Danh sách câu thoại
                </h3>
                <button id="toggleTranscriptBtn" class="btn-toggle-transcript">
                    <i class="fas fa-eye-slash"></i> Ẩn lời thoại
                </button>
            </div>

            <div class="transcript-list" id="transcriptList">
                <?php foreach ($sentences as $index => $sentence): ?>
                    <div class="transcript-item" data-index="<?= $index ?>">
                        <div class="transcript-number">#<?= (int)$sentence['sentence_number'] ?></div>
                        <div class="transcript-content">
                            <div class="transcript-japanese">
                                <?= htmlspecialchars($sentence['japanese_text']) ?>
                            </div>
                            <div class="transcript-vietnamese">
                                <?= htmlspecialchars($sentence['vietnamese_text'] ?? '') ?>
                            </div>
                        </div>
                        <button class="select-sentence-btn" data-index="<?= $index ?>">Nghe câu này</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </div>

<?php endif; ?>

</div>

<!-- SAKURA CUSTOM ALERT MODAL -->
<div id="sakuraResultModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border: 1px solid #ffe6ea; border-radius: 24px; padding: 35px 30px; width: 380px; text-align: center; box-shadow: 0 15px 35px rgba(255, 107, 129, 0.2); animation: modalScaleUp 0.3s ease;">
        <div style="width: 65px; height: 65px; background: #fff0f3; color: #ff4757; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 15px auto; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.2);">
            <i class="fas fa-award"></i>
        </div>
        <h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 800; color: #2d3436;">Hoàn thành bài luyện nghe! 🎉</h3>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #636e72; font-weight: 600;">Bạn đã hoàn thành xuất sắc các câu hỏi của bài tập này.</p>
        
        <div style="background: #fff0f3; border: 1.5px dashed #ffccd5; border-radius: 16px; padding: 15px; margin-bottom: 25px;">
            <span style="font-size: 13px; color: #636e72; font-weight: 700; display: block; margin-bottom: 4px;">Số câu trả lời đúng của bạn:</span>
            <span id="modalScoreText" style="font-size: 26px; font-weight: 900; color: #ff4757;">0 / 0</span>
        </div>

        <button onclick="closeSakuraModal()" style="background: #ff6b81; color: white; border: none; width: 100%; padding: 12px; border-radius: 14px; font-size: 15px; font-weight: 800; cursor: pointer; box-shadow: 0 6px 15px rgba(255, 107, 129, 0.35); transition: 0.2s;">
            Tuyệt vời, tiếp tục học 🌸
        </button>
    </div>
</div>

<style>
@keyframes modalScaleUp {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<script>
function closeSakuraModal() {
    document.getElementById('sakuraResultModal').style.display = 'none';
}
</script>

<?php if ($current_lesson): ?>
<script>
    window.listeningSentences = <?= json_encode(
        $sentences,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;
    window.youtubeVideoId = <?= json_encode($youtube_id) ?>;
</script>
<script src="https://www.youtube.com/iframe_api"></script>
<script src="assets/js/listening.js?v=<?= time() ?>"></script>
<?php endif; ?>