<?php
require_once __DIR__ . '/../config/database.php';

$message = '';
$error = '';

$action_lesson = $_POST['action_lesson'] ?? '';
$action = $_POST['action_sentence'] ?? '';

// Danh sách các thể loại cố định
$categories_list = ['Hoạt hình', 'Podcast', 'Hội thoại', 'Tin tức', 'Lịch sử', 'Nấu ăn'];

// =============================================
// 1. XỬ LÝ THÊM / SỬA / XÓA BÀI NGHE (LISTENING_LESSONS)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($action_lesson)) {

    // --- THÊM BÀI NGHE MỚI ---
    if ($action_lesson === 'add_lesson') {
        $title = trim($_POST['title'] ?? '');
        $youtube_url = trim($_POST['youtube_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'Hoạt hình');

        if (empty($title) || empty($youtube_url)) {
            $error = 'Vui lòng nhập đầy đủ tiêu đề và đường dẫn YouTube!';
        } else {
            $stmt = $conn->prepare("INSERT INTO listening_lessons (title, youtube_url, description, category) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $title, $youtube_url, $description, $category);
                if ($stmt->execute()) {
                    $new_id = $stmt->insert_id;
                    $stmt->close();
                    echo "<script>window.location.href='admin.php?page=listening&lesson_id=" . (int)$new_id . "';</script>";
                    exit;
                } else {
                    $error = 'Lỗi thực thi: ' . $stmt->error;
                }
            } else {
                $error = 'Lỗi câu lệnh SQL: ' . $conn->error;
            }
        }
    }

    // --- SỬA THÔNG TIN BÀI NGHE ---
    elseif ($action_lesson === 'edit_lesson') {
        $edit_id = (int)($_POST['lesson_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $youtube_url = trim($_POST['youtube_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'Hoạt hình');

        $stmt = $conn->prepare("UPDATE listening_lessons SET title = ?, youtube_url = ?, description = ?, category = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssi", $title, $youtube_url, $description, $category, $edit_id);
            if ($stmt->execute()) {
                $message = 'Cập nhật thông tin bài nghe thành công!';
            } else {
                $error = 'Cập nhật thất bại!';
            }
            $stmt->close();
        } else {
            $error = 'Lỗi SQL: ' . $conn->error;
        }
    }

    // --- XÓA BÀI NGHE ---
    elseif ($action_lesson === 'delete_lesson') {
        $delete_id = (int)($_POST['lesson_id'] ?? 0);
        
        $stmt_del_sen = $conn->prepare("DELETE FROM listening_sentences WHERE lesson_id = ?");
        if ($stmt_del_sen) {
            $stmt_del_sen->bind_param("i", $delete_id);
            $stmt_del_sen->execute();
            $stmt_del_sen->close();
        }

        $stmt_del_les = $conn->prepare("DELETE FROM listening_lessons WHERE id = ?");
        if ($stmt_del_les) {
            $stmt_del_les->bind_param("i", $delete_id);
            if ($stmt_del_les->execute()) {
                $stmt_del_les->close();
                echo "<script>window.location.href='admin.php?page=listening';</script>";
                exit;
            }
        }
    }
}

// Lấy danh sách tất cả các bài nghe
$lessons = [];
$result = $conn->query("SELECT * FROM listening_lessons ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $lessons[] = $row;
    }
}

// Bài nghe đang được chọn
$lesson_id = isset($_GET['lesson_id'])
    ? (int)$_GET['lesson_id']
    : (!empty($lessons) ? (int)$lessons[0]['id'] : 0);

// =============================================
// 2. XỬ LÝ THÊM / SỬA / XÓA CÂU THOẠI
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($action)) {

    if ($action === 'add') {
        $japanese = trim($_POST['japanese_text'] ?? '');
        $vietnamese = trim($_POST['vietnamese_text'] ?? '');
        $start_time = isset($_POST['start_time']) ? (float)$_POST['start_time'] : 0;
        $end_time = isset($_POST['end_time']) ? (float)$_POST['end_time'] : 0;

        $stmt = $conn->prepare("SELECT COALESCE(MAX(sentence_number), 0) + 1 AS next_number FROM listening_sentences WHERE lesson_id = ?");
        $stmt->bind_param("i", $lesson_id);
        $stmt->execute();
        $next_number = $stmt->get_result()->fetch_assoc()['next_number'];
        $stmt->close();

        if ($japanese === '') {
            $error = 'Vui lòng nhập câu tiếng Nhật!';
        } elseif ($start_time < 0 || $end_time <= $start_time) {
            $error = 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu!';
        } else {
            $stmt = $conn->prepare("INSERT INTO listening_sentences (lesson_id, sentence_number, start_time, end_time, japanese_text, vietnamese_text) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iiddss", $lesson_id, $next_number, $start_time, $end_time, $japanese, $vietnamese);
                if ($stmt->execute()) {
                    $message = 'Thêm câu thoại thành công!';
                } else {
                    $error = 'Không thể thêm câu thoại: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }

    elseif ($action === 'edit') {
        $sentence_id = (int)($_POST['sentence_id'] ?? 0);
        $japanese = trim($_POST['japanese_text'] ?? '');
        $vietnamese = trim($_POST['vietnamese_text'] ?? '');
        $start_time = (float)($_POST['start_time'] ?? 0);
        $end_time = (float)($_POST['end_time'] ?? 0);

        if ($japanese === '') {
            $error = 'Câu tiếng Nhật không được để trống!';
        } elseif ($start_time < 0 || $end_time <= $start_time) {
            $error = 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu!';
        } else {
            $stmt = $conn->prepare("UPDATE listening_sentences SET japanese_text = ?, vietnamese_text = ?, start_time = ?, end_time = ? WHERE id = ? AND lesson_id = ?");
            if ($stmt) {
                $stmt->bind_param("ssddii", $japanese, $vietnamese, $start_time, $end_time, $sentence_id, $lesson_id);
                if ($stmt->execute()) {
                    $message = 'Cập nhật câu thoại thành công!';
                } else {
                    $error = 'Cập nhật thất bại!';
                }
                $stmt->close();
            }
        }
    }

    elseif ($action === 'delete') {
        $sentence_id = (int)($_POST['sentence_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM listening_sentences WHERE id = ? AND lesson_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $sentence_id, $lesson_id);
            if ($stmt->execute()) {
                $stmt->close();

                $stmt = $conn->prepare("SELECT id FROM listening_sentences WHERE lesson_id = ? ORDER BY start_time ASC, id ASC");
                $stmt->bind_param("i", $lesson_id);
                $stmt->execute();
                $result = $stmt->get_result();

                $number = 1;
                while ($row = $result->fetch_assoc()) {
                    $update = $conn->prepare("UPDATE listening_sentences SET sentence_number = ? WHERE id = ?");
                    $update->bind_param("ii", $number, $row['id']);
                    $update->execute();
                    $update->close();
                    $number++;
                }
                $message = 'Xóa câu thoại thành công!';
            }
        }
    }
}

// LẤY BÀI NGHE ĐANG QUẢN LÝ
$current_lesson = null;
foreach ($lessons as $lesson) {
    if ((int)$lesson['id'] === $lesson_id) {
        $current_lesson = $lesson;
        break;
    }
}

// LẤY DANH SÁCH CÂU THOẠI
$sentences = [];
if ($current_lesson) {
    $stmt = $conn->prepare("SELECT * FROM listening_sentences WHERE lesson_id = ? ORDER BY sentence_number ASC");
    if ($stmt) {
        $stmt->bind_param("i", $lesson_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $sentences[] = $row;
        }
        $stmt->close();
    }
}

function formatTime($seconds) {
    $seconds = (float)$seconds;
    $minutes = floor($seconds / 60);
    $remaining = $seconds - ($minutes * 60);
    return sprintf("%02d:%05.2f", $minutes, $remaining);
}
?>

<h2 class="page-title">
    <i class="fas fa-headphones" style="color: var(--pink-primary);"></i> Quản lý Luyện nghe & Câu thoại
</h2>

<!-- THÔNG BÁO FLOATING TOAST (DÙNG FONTAWESOME ICON CỦA SAKURA) -->
<?php if ($message || $error): ?>
<div id="sakuraToastAlert" style="position: fixed; top: 25px; right: 25px; z-index: 999999; display: flex; align-items: center; gap: 14px; background: #ffffff; border: 1.5px solid <?= $message ? '#2ecc71' : '#ff4757' ?>; padding: 12px 18px; border-radius: 18px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12); animation: toastSlideIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275); font-family: inherit;">
    
    <!-- ICON NỔI TRONG VÒNG TRÒN -->
    <div style="width: 36px; height: 36px; border-radius: 50%; background: <?= $message ? '#eafaf1' : '#fff0f3' ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <i class="<?= $message ? 'fas fa-check-circle' : 'fas fa-exclamation-circle' ?>" style="font-size: 18px; color: <?= $message ? '#2ecc71' : '#ff4757' ?>;"></i>
    </div>

    <!-- NỘI DUNG THÔNG BÁO -->
    <span style="font-weight: 800; font-size: 14px; color: #2d3436; font-family: inherit;">
        <?= htmlspecialchars($message ?: $error) ?>
    </span>

    <!-- NÚT TẮT -->
    <button type="button" onclick="closeSakuraToast()" style="background: transparent; border: none; font-size: 18px; color: #b2bec3; cursor: pointer; padding: 0 0 0 6px; line-height: 1; font-family: inherit;">
        <i class="fas fa-times"></i>
    </button>
</div>

<style>
@keyframes toastSlideIn {
    from { transform: translateX(50px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<script>
function closeSakuraToast() {
    const toast = document.getElementById('sakuraToastAlert');
    if (toast) toast.style.display = 'none';
}
// Tự động ẩn thông báo sau 3.5 giây
setTimeout(closeSakuraToast, 3500);
</script>
<?php endif; ?>

<!-- KHỐI 1: TẠO BÀI NGHE MỚI -->
<div class="card">
    <h3 style="margin-top: 0; color: var(--text-main); font-size: 18px; margin-bottom: 20px;">
        <i class="fas fa-plus-circle" style="color: var(--pink-primary);"></i> Tạo bài luyện nghe mới
    </h3>
    <form method="POST">
        <input type="hidden" name="action_lesson" value="add_lesson">
        <div class="form-group">
            <label>Tên bài nghe (Tập video)</label>
            <input type="text" name="title" placeholder="Ví dụ: Chiikawa tập 307" required>
        </div>

        <div class="form-group">
            <label>Phân loại thể loại</label>
            <select name="category" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                <?php foreach ($categories_list as $cat): ?>
                    <option value="<?= $cat ?>"><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Link Video YouTube</label>
            <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required>
        </div>
        <div class="form-group">
            <label>Mô tả bài nghe</label>
            <textarea name="description" rows="2" placeholder="Nhập mô tả cho bài nghe..."></textarea>
        </div>
        <button type="submit" class="btn">
            <i class="fas fa-save"></i> Tạo bài nghe mới
        </button>
    </form>
</div>

<!-- KHỐI 2: CHỌN & SỬA / XÓA BÀI NGHE ĐANG CÓ -->
<div class="card">
    <div class="form-group" style="margin-bottom: 0;">
        <label><i class="fas fa-list-ul"></i> Chọn bài nghe để quản lý câu thoại</label>
        <select onchange="window.location.href='admin.php?page=listening&lesson_id='+this.value">
            <?php foreach ($lessons as $lesson): ?>
                <option value="<?= (int)$lesson['id'] ?>" <?= ((int)$lesson['id'] === $lesson_id) ? 'selected' : '' ?>>
                    [<?= htmlspecialchars($lesson['category'] ?? 'Hoạt hình') ?>] <?= htmlspecialchars($lesson['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($current_lesson): ?>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #eee;">
            <h4 style="margin: 0 0 15px 0; color: var(--pink-primary);">✏️ Sửa thông tin bài nghe hiện tại:</h4>
            <form method="POST">
                <input type="hidden" name="action_lesson" value="edit_lesson">
                <input type="hidden" name="lesson_id" value="<?= $current_lesson['id'] ?>">

                <div class="form-group">
                    <label>Tên bài nghe</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($current_lesson['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Phân loại thể loại</label>
                    <select name="category" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        <?php 
                        $cur_cat = $current_lesson['category'] ?? 'Hoạt hình';
                        foreach ($categories_list as $cat): 
                        ?>
                            <option value="<?= $cat ?>" <?= ($cur_cat === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Link YouTube URL</label>
                    <input type="url" name="youtube_url" value="<?= htmlspecialchars($current_lesson['youtube_url']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" rows="2"><?= htmlspecialchars($current_lesson['description'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-action btn-edit" style="border: none; cursor: pointer;">
                        <i class="fas fa-sync-alt"></i> Lưu thông tin bài
                    </button>
                </div>
            </form>

            <!-- XÓA BÀI NGHE DÙNG MODAL XÁC NHẬN SAKURA -->
            <form method="POST" style="margin-top: -38px; display: flex; justify-content: flex-end;" onsubmit="return openSakuraConfirm(event, 'CẢNH BÁO: Xóa bài nghe này sẽ XÓA TOÀN BỘ CÂU THOẠI bên dưới! Bạn chắc chắn chứ?')">
                <input type="hidden" name="action_lesson" value="delete_lesson">
                <input type="hidden" name="lesson_id" value="<?= $current_lesson['id'] ?>">
                <button type="submit" class="btn-action btn-delete" style="border: none; cursor: pointer; background: #e74c3c; color: white; padding: 8px 15px; border-radius: 8px; font-weight: bold;">
                    <i class="fas fa-trash"></i> Xóa toàn bộ bài nghe này
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- KHỐI 3: QUẢN LÝ CÂU THOẠI -->
<?php if ($current_lesson): ?>

<div class="card">
    <h3 style="margin-top: 0; color: var(--text-main); font-size: 18px; margin-bottom: 20px;">
        <i class="fas fa-plus-circle" style="color: var(--pink-primary);"></i> Thêm câu thoại mới cho bài "<?= htmlspecialchars($current_lesson['title']) ?>"
    </h3>

    <form method="POST">
        <input type="hidden" name="action_sentence" value="add">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Thời gian bắt đầu (giây)</label>
                <input type="number" name="start_time" min="0" step="0.01" placeholder="Ví dụ: 6" required>
            </div>

            <div class="form-group">
                <label>Thời gian kết thúc (giây)</label>
                <input type="number" name="end_time" min="0" step="0.01" placeholder="Ví dụ: 9.5" required>
            </div>
        </div>

        <div class="form-group">
            <label>Câu tiếng Nhật</label>
            <textarea name="japanese_text" rows="3" placeholder="Nhập câu thoại tiếng Nhật..." required></textarea>
        </div>

        <div class="form-group">
            <label>Nghĩa tiếng Việt</label>
            <textarea name="vietnamese_text" rows="3" placeholder="Nhập nghĩa tiếng Việt..."></textarea>
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-plus"></i> Thêm câu thoại
        </button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top: 0; color: var(--text-main); font-size: 18px; margin-bottom: 20px;">
        <i class="fas fa-list" style="color: var(--pink-primary);"></i> Danh sách câu thoại (<?= count($sentences) ?> câu)
    </h3>

    <?php if (empty($sentences)): ?>
        <p style="color: var(--text-gray); text-align: center; padding: 20px 0;">Chưa có câu thoại nào. Hãy thêm câu đầu tiên nhé!</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($sentences as $sentence): ?>
                <div style="border: 1px solid #f0f0f0; background: #fafafa; padding: 20px; border-radius: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <span style="font-weight: 800; color: var(--pink-primary); font-size: 16px;">
                            Câu <?= (int)$sentence['sentence_number'] ?>
                        </span>
                        <span style="color: var(--text-gray); font-size: 13px; font-weight: 600;">
                            ⏱ <?= formatTime($sentence['start_time']) ?> → <?= formatTime($sentence['end_time']) ?>
                            (<?= $sentence['start_time'] ?>s → <?= $sentence['end_time'] ?>s)
                        </span>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action_sentence" value="edit">
                        <input type="hidden" name="sentence_id" value="<?= (int)$sentence['id'] ?>">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Bắt đầu (giây)</label>
                                <input type="number" name="start_time" min="0" step="0.01" value="<?= htmlspecialchars($sentence['start_time']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Kết thúc (giây)</label>
                                <input type="number" name="end_time" min="0" step="0.01" value="<?= htmlspecialchars($sentence['end_time']) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Câu tiếng Nhật</label>
                            <textarea name="japanese_text" rows="2" required><?= htmlspecialchars($sentence['japanese_text']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Nghĩa tiếng Việt</label>
                            <textarea name="vietnamese_text" rows="2"><?= htmlspecialchars($sentence['vietnamese_text'] ?? '') ?></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" class="btn btn-action btn-edit" style="border: none; cursor: pointer; font-family: inherit;">
                                <i class="fas fa-save"></i> Lưu chỉnh sửa
                            </button>
                        </div>
                    </form>

                    <!-- XÓA CÂU THOẠI DÙNG MODAL XÁC NHẬN SAKURA -->
                    <form method="POST" style="margin-top: -38px; display: flex; justify-content: flex-end;" onsubmit="return openSakuraConfirm(event, 'Bạn có chắc chắn muốn xóa câu thoại này không?')">
                        <input type="hidden" name="action_sentence" value="delete">
                        <input type="hidden" name="sentence_id" value="<?= (int)$sentence['id'] ?>">
                        <button type="submit" class="btn-action btn-delete" style="border: none; cursor: pointer; font-family: inherit;">
                            <i class="fas fa-trash"></i> Xóa câu
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<!-- ============================================= -->
<!-- SAKURA CONFIRM MODAL (THÔNG BÁO MÀU HỒNG HOA ANH ĐÀO GIỮA MÀN HÌNH) -->
<!-- ============================================= -->
<div id="sakuraConfirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.45); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div style="background: #ffffff; border: 1.5px solid #ffe6ea; border-radius: 24px; padding: 32px 28px; width: 380px; text-align: center; box-shadow: 0 15px 35px rgba(255, 107, 129, 0.25); animation: sakuraModalScale 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div style="width: 62px; height: 62px; background: #fff0f3; color: #ff4757; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 16px auto; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.22);">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 style="margin: 0 0 10px 0; font-size: 19px; font-weight: 800; color: #2d3436;">Xác nhận xóa!</h3>
        <p id="sakuraConfirmMessage" style="margin: 0 0 24px 0; font-size: 13.5px; color: #636e72; font-weight: 600; line-height: 1.5;"></p>
        
        <div style="display: flex; gap: 12px;">
            <!-- Nút Hủy bỏ -->
            <button type="button" onclick="closeSakuraConfirm()" style="flex: 1; background: #f1f2f6; color: #57606f; border: none; padding: 12px; border-radius: 14px; font-size: 14px; font-weight: 700; font-family: inherit; cursor: pointer; transition: 0.2s;">
                Hủy bỏ
            </button>
    
            <!-- Nút Đồng ý xóa (Đã thêm font-family: inherit) -->
            <button type="button" id="sakuraConfirmSubmitBtn" style="flex: 1; background: #ff4757; color: white; border: none; padding: 12px; border-radius: 14px; font-size: 14px; font-weight: 800; font-family: inherit; cursor: pointer; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.35); transition: 0.2s;">
                Đồng ý xóa
            </button>
        </div>
    </div>
</div>

<style>
@keyframes sakuraModalScale {
    from { transform: scale(0.82); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<script>
let currentSakuraForm = null;

function openSakuraConfirm(event, message) {
    event.preventDefault(); // Ngăn gửi form mặc định
    currentSakuraForm = event.target; // Lưu form vừa bấm
    
    document.getElementById('sakuraConfirmMessage').innerText = message;
    document.getElementById('sakuraConfirmModal').style.display = 'flex';
    return false;
}

function closeSakuraConfirm() {
    document.getElementById('sakuraConfirmModal').style.display = 'none';
    currentSakuraForm = null;
}

document.getElementById('sakuraConfirmSubmitBtn').addEventListener('click', function() {
    if (currentSakuraForm) {
        currentSakuraForm.submit(); // Thực hiện submit form khi bấm Đồng ý
    }
});
</script>

<!-- SCRIPT GIỮ VỊ TRÍ CUỘN (XỬ LÝ CẢ WINDOW LẪN KHUNG DIV ADMIN) -->
<script>
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

// 1. Hàm lưu vị trí cuộn của tất cả các khung
function saveSakuraScroll() {
    const scrollData = {
        windowY: window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0,
        containers: []
    };

    // Quét tìm tất cả các div/main có thanh cuộn trong trang Admin
    document.querySelectorAll('div, main, section, article').forEach(function (el, index) {
        if (el.scrollTop > 0) {
            scrollData.containers.push({
                index: index,
                scrollTop: el.scrollTop
            });
        }
    });

    sessionStorage.setItem("sakura_admin_scroll_data", JSON.stringify(scrollData));
}

// 2. Bắt sự kiện lưu trước khi submit Form
document.addEventListener('submit', saveSakuraScroll, true);

// Bắt riêng nút "Đồng ý xóa" trong Modal Sakura
const sakuraConfirmBtn = document.getElementById('sakuraConfirmSubmitBtn');
if (sakuraConfirmBtn) {
    sakuraConfirmBtn.addEventListener('click', function () {
        saveSakuraScroll();
        if (currentSakuraForm) {
            currentSakuraForm.submit();
        }
    });
}

// 3. Hàm khôi phục vị trí cuộn
function restoreSakuraScroll() {
    const rawData = sessionStorage.getItem("sakura_admin_scroll_data");
    if (!rawData) return;

    try {
        const scrollData = JSON.parse(rawData);

        // Khôi phục cuộn cho Window/Document
        if (scrollData.windowY > 0) {
            window.scrollTo(0, scrollData.windowY);
            document.documentElement.scrollTop = scrollData.windowY;
            document.body.scrollTop = scrollData.windowY;
        }

        // Khôi phục cuộn cho khung Div Admin (nếu có)
        if (scrollData.containers && scrollData.containers.length > 0) {
            const allElements = document.querySelectorAll('div, main, section, article');
            scrollData.containers.forEach(function (item) {
                if (allElements[item.index]) {
                    allElements[item.index].scrollTop = item.scrollTop;
                }
            });
        }
    } catch (e) {
        console.error(e);
    }
}

// 4. Thực thi khôi phục liên tục trong các nhịp render để thắng trình duyệt
document.addEventListener("DOMContentLoaded", restoreSakuraScroll);
window.addEventListener("load", function () {
    restoreSakuraScroll();
    setTimeout(restoreSakuraScroll, 50);
    setTimeout(restoreSakuraScroll, 150);
    setTimeout(restoreSakuraScroll, 300);
});
</script>