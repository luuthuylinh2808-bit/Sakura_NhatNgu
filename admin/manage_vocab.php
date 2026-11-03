<?php
require_once '../config/database.php';
$action = $_GET['action'] ?? 'list';

// 1. XÓA TỪ VỰNG
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM vocabulary WHERE id_vocab = $id");
    echo "<script>window.location='admin.php?page=vocab';</script>";
}

// 2. LƯU (THÊM/SỬA) TỪ VỰNG
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_item'])) {
    $id_lesson = (int)$_POST['id_lesson'];
    $kanji = $conn->real_escape_string($_POST['kanji']);
    $hiragana = $conn->real_escape_string($_POST['hiragana']);
    $meaning = $conn->real_escape_string($_POST['meaning']);
    $example = $conn->real_escape_string($_POST['example']);

    if ($_POST['action_type'] == 'add') {
        $conn->query("INSERT INTO vocabulary (id_lesson, kanji, hiragana, meaning, example) VALUES ($id_lesson, '$kanji', '$hiragana', '$meaning', '$example')");
    } else {
        $id = (int)$_POST['id_vocab'];
        $conn->query("UPDATE vocabulary SET id_lesson=$id_lesson, kanji='$kanji', hiragana='$hiragana', meaning='$meaning', example='$example' WHERE id_vocab=$id");
    }
    echo "<script>window.location='admin.php?page=vocab';</script>";
}

// Lấy danh sách bài học để cho vào thẻ Select
$lessons = $conn->query("SELECT id_lesson, level, order_index, title FROM lessons ORDER BY level ASC, order_index ASC");

// 3. HIỂN THỊ DANH SÁCH
if ($action == 'list') {
    echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
            <h2 class='page-title' style='margin: 0;'><i class='fas fa-spell-check' style='color:#3498db;'></i> Quản lý Từ vựng</h2>
            <a href='admin.php?page=vocab&action=add' class='btn' style='background:#3498db;'><i class='fas fa-plus'></i> Thêm từ vựng</a>
          </div>";

    echo "<div class='card' style='padding: 0; overflow: hidden;'><table>
            <tr><th style='padding-left:25px;'>Bài học</th><th>Hiragana</th><th>Kanji</th><th>Ý nghĩa</th><th>Ví dụ</th><th style='text-align:right; padding-right:25px;'>Thao tác</th></tr>";
    
    $res = $conn->query("SELECT v.*, l.level, l.order_index FROM vocabulary v JOIN lessons l ON v.id_lesson = l.id_lesson ORDER BY v.id_vocab DESC");
    while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td style='padding-left:25px;'><span style='background:#ebf5fb; color:#3498db; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:bold;'>{$r['level']} - Bài {$r['order_index']}</span></td>
                <td><b style='font-size:16px;'>{$r['hiragana']}</b></td>
                <td>{$r['kanji']}</td>
                <td style='color:#444;'>{$r['meaning']}</td>
                <td style='color:#888; font-size:13px;'>{$r['example']}</td>
                <td style='white-space:nowrap; text-align:right; padding-right:25px;'>
                    <a href='admin.php?page=vocab&action=edit&id={$r['id_vocab']}' class='btn-action btn-edit'><i class='fas fa-pen'></i></a>
                    <a href='admin.php?page=vocab&delete_id={$r['id_vocab']}' class='btn-action btn-delete' onclick=\"return confirm('Xóa từ vựng này?');\"><i class='fas fa-trash'></i></a>
                </td>
              </tr>";
    }
    echo "</table></div>";

// 4. HIỂN THỊ FORM THÊM/SỬA
} else {
    $item = ['id_lesson'=>'', 'kanji'=>'', 'hiragana'=>'', 'meaning'=>'', 'example'=>''];
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $item = $conn->query("SELECT * FROM vocabulary WHERE id_vocab = $id")->fetch_assoc();
    }
?>
    <h2 class='page-title'><i class="fas <?= $action=='add'?'fa-plus-circle':'fa-edit' ?>" style="color:#3498db;"></i> <?= $action=='add'?'Thêm từ vựng mới':'Sửa từ vựng' ?></h2>
    <div class="card" style="max-width: 600px;">
        <form method="POST">
            <input type="hidden" name="action_type" value="<?= $action ?>">
            <?php if($action=='edit') echo "<input type='hidden' name='id_vocab' value='{$item['id_vocab']}'>"; ?>
            
            <div class="form-group">
                <label>Thuộc bài học:</label>
                <select name="id_lesson" required>
                    <option value="">-- Chọn bài học --</option>
                    <?php while($l = $lessons->fetch_assoc()): ?>
                        <option value="<?= $l['id_lesson'] ?>" <?= $item['id_lesson']==$l['id_lesson']?'selected':'' ?>>
                            [<?= $l['level'] ?>] Bài <?= $l['order_index'] ?> - <?= $l['title'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group"><label>Hiragana (*)</label><input type="text" name="hiragana" value="<?= $item['hiragana'] ?>" required></div>
                <div class="form-group"><label>Kanji</label><input type="text" name="kanji" value="<?= $item['kanji'] ?>"></div>
            </div>
            <div class="form-group"><label>Ý nghĩa (*)</label><input type="text" name="meaning" value="<?= $item['meaning'] ?>" required></div>
            <div class="form-group"><label>Ví dụ</label><input type="text" name="example" value="<?= $item['example'] ?>"></div>
            
            <div style="margin-top: 20px;">
                <button type="submit" name="save_item" class="btn" style="background:#3498db;">Lưu thông tin</button>
                <a href="admin.php?page=vocab" class="btn btn-outline" style="color:#3498db; border-color:#3498db;">Hủy</a>
            </div>
        </form>
    </div>
<?php } ?>