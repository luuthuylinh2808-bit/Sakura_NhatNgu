<?php
require_once '../config/database.php';
$action = $_GET['action'] ?? 'list';

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM grammars WHERE id_gammar = $id"); // id_gammar theo CSDL của bạn
    echo "<script>window.location='admin.php?page=grammar';</script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_item'])) {
    $id_lesson = (int)$_POST['id_lesson'];
    $structure = $conn->real_escape_string($_POST['structure']);
    $meaning = $conn->real_escape_string($_POST['meaning']);
    $example = $conn->real_escape_string($_POST['example']);

    if ($_POST['action_type'] == 'add') {
        $conn->query("INSERT INTO grammars (id_lesson, structure, meaning, example) VALUES ($id_lesson, '$structure', '$meaning', '$example')");
    } else {
        $id = (int)$_POST['id_gammar'];
        $conn->query("UPDATE grammars SET id_lesson=$id_lesson, structure='$structure', meaning='$meaning', example='$example' WHERE id_gammar=$id");
    }
    echo "<script>window.location='admin.php?page=grammar';</script>";
}

$lessons = $conn->query("SELECT id_lesson, level, order_index, title FROM lessons ORDER BY level ASC, order_index ASC");

if ($action == 'list') {
    echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
            <h2 class='page-title' style='margin: 0;'><i class='fas fa-scroll' style='color:#9b59b6;'></i> Quản lý Ngữ pháp</h2>
            <a href='admin.php?page=grammar&action=add' class='btn' style='background:#9b59b6;'><i class='fas fa-plus'></i> Thêm ngữ pháp</a>
          </div>";

    echo "<div class='card' style='padding: 0; overflow: hidden;'><table>
            <tr><th style='padding-left:25px;'>Bài học</th><th>Cấu trúc</th><th>Cách dùng</th><th>Ví dụ</th><th style='text-align:right; padding-right:25px;'>Thao tác</th></tr>";
    
    $res = $conn->query("SELECT g.*, l.level, l.order_index FROM grammars g JOIN lessons l ON g.id_lesson = l.id_lesson ORDER BY g.id_gammar DESC");
    while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td style='padding-left:25px;'><span style='background:#f4ecf7; color:#9b59b6; padding:4px 8px; border-radius:6px; font-size:12px; font-weight:bold;'>{$r['level']} - Bài {$r['order_index']}</span></td>
                <td><b style='color:#9b59b6;'>{$r['structure']}</b></td>
                <td style='color:#444;'>{$r['meaning']}</td>
                <td style='color:#888; font-size:13px; max-width:250px;'>{$r['example']}</td>
                <td style='white-space:nowrap; text-align:right; padding-right:25px;'>
                    <a href='admin.php?page=grammar&action=edit&id={$r['id_gammar']}' class='btn-action btn-edit'><i class='fas fa-pen'></i></a>
                    <a href='admin.php?page=grammar&delete_id={$r['id_gammar']}' class='btn-action btn-delete' onclick=\"return confirm('Xóa cấu trúc này?');\"><i class='fas fa-trash'></i></a>
                </td>
              </tr>";
    }
    echo "</table></div>";

} else {
    $item = ['id_lesson'=>'', 'structure'=>'', 'meaning'=>'', 'example'=>''];
    if ($action == 'edit' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $item = $conn->query("SELECT * FROM grammars WHERE id_gammar = $id")->fetch_assoc();
    }
?>
    <h2 class='page-title'><i class="fas <?= $action=='add'?'fa-plus-circle':'fa-edit' ?>" style="color:#9b59b6;"></i> <?= $action=='add'?'Thêm ngữ pháp mới':'Sửa ngữ pháp' ?></h2>
    <div class="card" style="max-width: 600px;">
        <form method="POST">
            <input type="hidden" name="action_type" value="<?= $action ?>">
            <?php if($action=='edit') echo "<input type='hidden' name='id_gammar' value='{$item['id_gammar']}'>"; ?>
            
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
            <div class="form-group"><label>Cấu trúc (*)</label><input type="text" name="structure" value="<?= $item['structure'] ?>" required></div>
            <div class="form-group"><label>Ý nghĩa / Cách dùng (*)</label><input type="text" name="meaning" value="<?= $item['meaning'] ?>" required></div>
            <div class="form-group"><label>Ví dụ minh họa</label><textarea name="example" rows="3"><?= $item['example'] ?></textarea></div>
            
            <div style="margin-top: 20px;">
                <button type="submit" name="save_item" class="btn" style="background:#9b59b6;">Lưu thông tin</button>
                <a href="admin.php?page=grammar" class="btn btn-outline" style="color:#9b59b6; border-color:#9b59b6;">Hủy</a>
            </div>
        </form>
    </div>
<?php } ?>