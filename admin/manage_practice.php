<?php
require_once '../config/database.php';
$action = $_GET['action'] ?? 'list';

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM exercise WHERE id_ex = $id");
    echo "<script>window.location='admin.php?page=practice';</script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_item'])) {
    $id_lesson = (int)$_POST['id_lesson'];
    $question = $conn->real_escape_string($_POST['question']);
    $optA = $conn->real_escape_string($_POST['option_A']);
    $optB = $conn->real_escape_string($_POST['option_B']);
    $optC = $conn->real_escape_string($_POST['option_C']);
    $optD = $conn->real_escape_string($_POST['option_D']);
    $correct = $conn->real_escape_string($_POST['correct_option']);

    if ($_POST['action_type'] == 'add') {
        $conn->query("INSERT INTO exercise (id_lesson, question, option_A, option_B, option_C, option_D, correct_option) VALUES ($id_lesson, '$question', '$optA', '$optB', '$optC', '$optD', '$correct')");
    } else {
        $id = (int)$_POST['id_ex'];
        $conn->query("UPDATE exercise SET id_lesson=$id_lesson, question='$question', option_A='$optA', option_B='$optB', option_C='$optC', option_D='$optD', correct_option='$correct' WHERE id_ex=$id");
    }
    echo "<script>window.location='admin.php?page=practice';</script>";
}

$lessons = $conn->query("SELECT id_lesson, level, order_index, title FROM lessons ORDER BY level ASC, order_index ASC");

if ($action == 'list') {
    echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
            <h2 class='page-title' style='margin: 0;'><i class='fas fa-tasks' style='color:#f39c12;'></i> Quản lý Câu hỏi Trắc nghiệm</h2>
            <a href='admin.php?page=practice&action=add' class='btn' style='background:#f39c12;'><i class='fas fa-plus'></i> Thêm câu hỏi</a>
          </div>";

    echo "<div class='card' style='padding: 0; overflow: hidden;'><table>
            <tr><th style='padding-left:25px;'>Bài học</th><th>Câu hỏi</th><th>Các đáp án</th><th>Đúng</th><th style='text-align:right; padding-right:25px;'>Thao tác</th></tr>";
    
    $res = $conn->query("SELECT e.*, l.level, l.order_index FROM exercise e JOIN lessons l ON e.id_lesson = l.id_lesson ORDER BY e.id_ex DESC");
    while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td style='padding-left:25px;'><span style='background:#fef5e7; color:#f39c12; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:bold;'>{$r['level']} - Bài {$r['order_index']}</span></td>
                <td><b>{$r['question']}</b></td>
                <td style='color:#666; font-size:13px;'>A. {$r['option_A']} | B. {$r['option_B']} | C. {$r['option_C']} | D. {$r['option_D']}</td>
                <td><span style='background:#e6ffed; color:#2ecc71; padding:3px 8px; border-radius:4px; font-weight:bold;'>{$r['correct_option']}</span></td>
                <td style='white-space:nowrap; text-align:right; padding-right:25px;'>
                    <a href='admin.php?page=practice&action=edit&id={$r['id_ex']}' class='btn-action btn-edit'><i class='fas fa-pen'></i></a>
                    <a href='admin.php?page=practice&delete_id={$r['id_ex']}' class='btn-action btn-delete' onclick=\"return confirm('Xóa câu hỏi này?');\"><i class='fas fa-trash'></i></a>
                </td>
              </tr>";
    }
    echo "</table></div>";

} else {
    $item = ['id_lesson'=>'', 'question'=>'', 'option_A'=>'', 'option_B'=>'', 'option_C'=>'', 'option_D'=>'', 'correct_option'=>'A'];
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $item = $conn->query("SELECT * FROM exercise WHERE id_ex = $id")->fetch_assoc();
    }
?>
    <h2 class='page-title'><i class="fas <?= $action=='add'?'fa-plus-circle':'fa-edit' ?>" style="color:#f39c12;"></i> <?= $action=='add'?'Thêm câu hỏi mới':'Sửa câu hỏi' ?></h2>
    <div class="card" style="max-width: 600px;">
        <form method="POST">
            <input type="hidden" name="action_type" value="<?= $action ?>">
            <?php if($action=='edit') echo "<input type='hidden' name='id_ex' value='{$item['id_ex']}'>"; ?>
            
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
            
            <div class="form-group"><label>Nội dung câu hỏi (*)</label><input type="text" name="question" value="<?= htmlspecialchars($item['question']) ?>" required></div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; background:#fafafa; padding:15px; border-radius:10px; border:1px solid #eee; margin-bottom:15px;">
                <div class="form-group" style="margin:0;"><label>Đáp án A</label><input type="text" name="option_A" value="<?= $item['option_A'] ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Đáp án B</label><input type="text" name="option_B" value="<?= $item['option_B'] ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Đáp án C</label><input type="text" name="option_C" value="<?= $item['option_C'] ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Đáp án D</label><input type="text" name="option_D" value="<?= $item['option_D'] ?>" required></div>
            </div>
            
            <div class="form-group">
                <label>Đáp án đúng:</label>
                <select name="correct_option" style="width: 150px;">
                    <option value="A" <?= $item['correct_option']=='A'?'selected':'' ?>>A</option>
                    <option value="B" <?= $item['correct_option']=='B'?'selected':'' ?>>B</option>
                    <option value="C" <?= $item['correct_option']=='C'?'selected':'' ?>>C</option>
                    <option value="D" <?= $item['correct_option']=='D'?'selected':'' ?>>D</option>
                </select>
            </div>
            
            <div style="margin-top: 25px;">
                <button type="submit" name="save_item" class="btn" style="background:#f39c12;">Lưu thông tin</button>
                <a href="admin.php?page=practice" class="btn btn-outline" style="color:#f39c12; border-color:#f39c12;">Hủy</a>
            </div>
        </form>
    </div>
<?php } ?>