<?php 
require_once '../config/database.php'; 
$action = $_GET['action'] ?? 'list'; 

// ===============================================
// 1. XỬ LÝ CÁC YÊU CẦU XÓA (DELETE)
// ===============================================
if (isset($_GET['delete_id'])) { 
    $del_id = (int)$_GET['delete_id']; 
    $conn->query("DELETE FROM lessons WHERE id_lesson = $del_id"); 
    $conn->query("DELETE FROM vocabulary WHERE id_lesson = $del_id"); 
    $conn->query("DELETE FROM grammars WHERE id_lesson = $del_id"); 
    $conn->query("DELETE FROM exercise WHERE id_lesson = $del_id"); 
    echo "<script>window.location='admin.php?page=lessons';</script>"; 
} 

if (isset($_GET['del_vocab'])) { 
    $id = (int)$_GET['del_vocab']; 
    $lesson_id = (int)$_GET['lesson_id']; 
    $conn->query("DELETE FROM vocabulary WHERE id_vocab = $id"); 
    echo "<script>window.location='admin.php?page=lessons&action=edit&id=$lesson_id';</script>"; 
} 

if (isset($_GET['del_grammar'])) { 
    $id = (int)$_GET['del_grammar']; 
    $lesson_id = (int)$_GET['lesson_id']; 
    $conn->query("DELETE FROM grammars WHERE id_grammar = $id"); 
    echo "<script>window.location='admin.php?page=lessons&action=edit&id=$lesson_id';</script>"; 
} 

if (isset($_GET['del_ex'])) { 
    $id = (int)$_GET['del_ex']; 
    $lesson_id = (int)$_GET['lesson_id']; 
    $conn->query("DELETE FROM exercise WHERE id_ex = $id"); 
    echo "<script>window.location='admin.php?page=lessons&action=edit&id=$lesson_id';</script>"; 
} 

// ===============================================
// 2. XỬ LÝ THÊM/SỬA (POST FORM)
// ===============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    // --- LƯU BÀI HỌC CHÍNH --- 
    if (isset($_POST['save_lesson'])) { 
        $title = $conn->real_escape_string($_POST['title']); 
        $level = $conn->real_escape_string($_POST['level']); 
        $description = $conn->real_escape_string($_POST['description']); 
        $order_index = (int)$_POST['order_index'];
        $video_url = $conn->real_escape_string($_POST['video_url'] ?? '');
        $is_premium = isset($_POST['is_premium']) ? 1 : 0;

        if ($_POST['action_type'] == 'add') { 
            $sql = "INSERT INTO lessons (title, level, description, order_index, video_url, is_premium) 
                    VALUES ('$title', '$level', '$description', $order_index, '$video_url', $is_premium)"; 
            if ($conn->query($sql)) { 
                $new_id = $conn->insert_id; 
                echo "<script>window.location='admin.php?page=lessons&action=edit&id=$new_id';</script>"; 
            } 
        } else { 
            $id = (int)$_POST['id_lesson']; 
            $sql = "UPDATE lessons SET title='$title', level='$level', description='$description', 
                    order_index=$order_index, video_url='$video_url', is_premium=$is_premium 
                    WHERE id_lesson=$id"; 
            if ($conn->query($sql)) { 
                echo "<script>window.location='admin.php?page=lessons&action=edit&id=$id';</script>"; 
            } 
        } 
    } 

    // --- THÊM TỪ VỰNG --- 
    if (isset($_POST['add_vocab'])) { 
        $id_lesson = (int)$_POST['id_lesson']; 
        $kanji = $conn->real_escape_string($_POST['kanji']); 
        $hiragana = $conn->real_escape_string($_POST['hiragana']); 
        $meaning = $conn->real_escape_string($_POST['meaning']); 
        $example = $conn->real_escape_string($_POST['example']); 
        $example_vi = $conn->real_escape_string($_POST['example_vi'] ?? '');
        
        $conn->query("INSERT INTO vocabulary (id_lesson, kanji, hiragana, meaning, example, example_vi) 
                      VALUES ($id_lesson, '$kanji', '$hiragana', '$meaning', '$example', '$example_vi')"); 
        echo "<script>window.location='admin.php?page=lessons&action=edit&id=$id_lesson';</script>"; 
    } 

    // --- THÊM NGỮ PHÁP --- 
    if (isset($_POST['add_grammar'])) { 
        $id_lesson = (int)$_POST['id_lesson']; 
        $structure = $conn->real_escape_string($_POST['structure']); 
        $meaning = $conn->real_escape_string($_POST['meaning']); 
        $example = $conn->real_escape_string($_POST['example']); 
        $example_vi = $conn->real_escape_string($_POST['example_vi'] ?? '');
        $romaji = $conn->real_escape_string($_POST['romaji'] ?? '');
        
        $conn->query("INSERT INTO grammars (id_lesson, structure, meaning, example, example_vi, romaji) 
                      VALUES ($id_lesson, '$structure', '$meaning', '$example', '$example_vi', '$romaji')"); 
        echo "<script>window.location='admin.php?page=lessons&action=edit&id=$id_lesson';</script>"; 
    } 

    // --- THÊM BÀI TẬP --- 
    if (isset($_POST['add_ex'])) { 
        $id_lesson = (int)$_POST['id_lesson']; 
        $question = $conn->real_escape_string($_POST['question']); 
        $optA = $conn->real_escape_string($_POST['option_A']); 
        $optB = $conn->real_escape_string($_POST['option_B']); 
        $optC = $conn->real_escape_string($_POST['option_C']); 
        $optD = $conn->real_escape_string($_POST['option_D']); 
        $correct = $conn->real_escape_string($_POST['correct_option']); 
        
        $conn->query("INSERT INTO exercise (id_lesson, question, option_A, option_B, option_C, option_D, correct_option) 
                      VALUES ($id_lesson, '$question', '$optA', '$optB', '$optC', '$optD', '$correct')"); 
        echo "<script>window.location='admin.php?page=lessons&action=edit&id=$id_lesson';</script>"; 
    } 
} 

// ===============================================
// 3. GIAO DIỆN HIỂN THỊ
// ===============================================
if ($action == 'list') { 
    echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'> 
            <h2 class='page-title' style='margin: 0;'><i class='fas fa-book-open' style='color: var(--pink-primary);'></i> Quản lý Bài học</h2> 
            <a href='admin.php?page=lessons&action=add' class='btn'><i class='fas fa-plus'></i> Thêm bài mới</a> 
          </div>"; 

    echo "<div class='card' style='padding: 0; overflow: hidden;'>"; 
    echo "<table>"; 
    echo "<tr><th style='padding-left: 25px;'>Cấp độ</th><th>Thứ tự</th><th>Tiêu đề</th><th>Mô tả</th><th>Trạng thái</th><th style='text-align: right; padding-right: 25px;'>Thao tác</th></tr>"; 
    
    $result = $conn->query("SELECT * FROM lessons ORDER BY level ASC, order_index ASC"); 
    if ($result->num_rows > 0) { 
        while ($row = $result->fetch_assoc()) { 
            $levelColor = ($row['level'] == 'N5') ? '#f39c12' : (($row['level'] == 'N4') ? '#3498db' : '#9b59b6'); 
            $levelBg = ($row['level'] == 'N5') ? '#fff3e0' : (($row['level'] == 'N4') ? '#ebf5fb' : '#f4ecf7'); 
            $statusBadge = ($row['is_premium'] == 1) ? "<span style='color: #e74c3c; font-weight: bold;'><i class='fas fa-lock'></i> Premium</span>" : "<span style='color: #2ecc71; font-weight: bold;'><i class='fas fa-unlock'></i> Miễn phí</span>";

            echo "<tr> 
                    <td style='padding-left: 25px;'><span style='background: $levelBg; color: $levelColor; padding: 5px 12px; border-radius: 8px; font-weight: 800; font-size: 12px;'>{$row['level']}</span></td> 
                    <td><b>Bài {$row['order_index']}</b></td> 
                    <td style='font-weight: 600; color: #444;'>{$row['title']}</td> 
                    <td style='color: #666; font-size: 14px;'>{$row['description']}</td> 
                    <td>$statusBadge</td>
                    <td style='white-space: nowrap; text-align: right; padding-right: 25px;'>
                        <a href='admin.php?page=lessons&action=edit&id={$row['id_lesson']}' class='btn-action btn-edit'><i class='fas fa-pen'></i></a> 
                        <a href='admin.php?page=lessons&delete_id={$row['id_lesson']}' class='btn-action btn-delete' onclick=\"return confirm('Xóa bài học này?');\"><i class='fas fa-trash'></i></a>
                    </td> 
                  </tr>"; 
        } 
    } 
    echo "</table></div>"; 

} else { 
    // --- TRANG THÊM / SỬA CHI TIẾT BÀI HỌC ---
    $lesson = ['title'=>'', 'level'=>'N5', 'description'=>'', 'order_index'=>'1', 'id_lesson'=>'', 'video_url'=>'', 'is_premium'=>0]; 
    $is_edit = false; 
    
    if ($action == 'edit' && isset($_GET['id'])) { 
        $id = (int)$_GET['id']; 
        $res = $conn->query("SELECT * FROM lessons WHERE id_lesson = $id"); 
        if($res->num_rows > 0) { 
            $lesson = $res->fetch_assoc(); 
            $is_edit = true; 
        } 
    } 
    ?> 
    
    <div style="display: flex; justify-content: space-between; align-items: center;"> 
        <h2 class='page-title'> 
            <i class="fas fa-edit" style="color: var(--pink-primary);"></i> 
            <?= $is_edit ? "Quản lý bài: " . htmlspecialchars($lesson['title']) : 'Thêm bài học mới' ?> 
        </h2> 
        <a href="admin.php?page=lessons" class="btn btn-outline" style="padding: 6px 15px;"><i class="fas fa-arrow-left"></i> Quay lại</a> 
    </div> 

    <div class="card"> 
        <h3 style="margin-top: 0; color: var(--pink-primary); border-bottom: 2px dashed #eee; padding-bottom: 10px;">1. Thông tin chung</h3> 
        <form method="POST"> 
            <input type='hidden' name='action_type' value='<?= $action ?>'> 
            <?php if($is_edit) echo "<input type='hidden' name='id_lesson' value='{$lesson['id_lesson']}'>"; ?> 
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px;"> 
                <div class="form-group"> 
                    <label>Tiêu đề bài học</label> 
                    <input type="text" name="title" value="<?= htmlspecialchars($lesson['title']) ?>" required> 
                </div> 
                <div class="form-group"> 
                    <label>Cấp độ JLPT</label> 
                    <select name="level"> 
                        <option value="N5" <?= $lesson['level']=='N5' ? 'selected' : '' ?>>Trình độ N5</option> 
                        <option value="N4" <?= $lesson['level']=='N4' ? 'selected' : '' ?>>Trình độ N4</option>
                        <option value="N3" <?= $lesson['level']=='N3' ? 'selected' : '' ?>>Trình độ N3</option> 
                    </select> 
                </div> 
                <div class="form-group"> 
                    <label>Thứ tự bài</label> 
                    <input type="number" name="order_index" value="<?= $lesson['order_index'] ?>" required> 
                </div> 
            </div>
            
            <div class="form-group"> 
                <label>Mô tả ngắn</label> 
                <textarea name="description" rows="2"><?= htmlspecialchars($lesson['description']) ?></textarea> 
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <div class="form-group"> 
                    <label>Link Video bài giảng (Youtube/MP4)</label> 
                    <input type="text" name="video_url" value="<?= htmlspecialchars($lesson['video_url'] ?? '') ?>" placeholder="VD: https://www.youtube.com/watch?v=..."> 
                </div>
                <div class="form-group" style="display: flex; align-items: center; margin-top: 15px;"> 
                    <label style="margin-right: 15px; margin-bottom: 0;">Khóa Premium (Trả phí):</label>
                    <input type="checkbox" name="is_premium" value="1" <?= (isset($lesson['is_premium']) && $lesson['is_premium'] == 1) ? 'checked' : '' ?> style="width: 20px; height: 20px; accent-color: var(--pink-primary); cursor: pointer;">
                </div>
            </div>

            <button type="submit" name="save_lesson" class="btn"><i class="fas fa-save"></i> Lưu thông tin chung</button> 
        </form> 
    </div> 

    <?php 
    // CHỈ HIỂN THỊ PHẦN TỪ VỰNG/NGỮ PHÁP/BÀI TẬP KHI ĐÃ TẠO BÀI HỌC
    if ($is_edit): 
    ?> 
    
    <!-- PHẦN TỪ VỰNG -->
    <div class="card"> 
        <h3 style="margin-top: 0; color: #3498db; border-bottom: 2px dashed #eee; padding-bottom: 10px;"><i class="fas fa-spell-check"></i> 2. Từ vựng</h3> 
        
        <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 10px; margin-bottom: 15px;"> 
            <input type='hidden' name='id_lesson' value='<?= $lesson['id_lesson'] ?>'> 
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;"> 
                <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="kanji" placeholder="Kanji (Nếu có)"></div> 
                <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="hiragana" placeholder="Hiragana (*)" required></div> 
            </div> 
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="meaning" placeholder="Nghĩa tiếng Việt (*)" required></div> 
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="example" placeholder="Ví dụ (Tiếng Nhật)"></div>
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="example_vi" placeholder="Dịch nghĩa câu ví dụ (Tiếng Việt)"></div>
            <button type="submit" name="add_vocab" class="btn" style="background: #3498db; width: 100%;"><i class="fas fa-plus"></i> Thêm từ vựng</button> 
        </form> 

        <div style="max-height: 300px; overflow-y: auto;"> 
            <table style="font-size: 13px;"> 
                <?php 
                $vocab_res = $conn->query("SELECT * FROM vocabulary WHERE id_lesson = {$lesson['id_lesson']} ORDER BY id_vocab DESC"); 
                while($v = $vocab_res->fetch_assoc()){ 
                    echo "<tr> 
                            <td><b style='color:#3498db'>{$v['hiragana']}</b> ({$v['kanji']})<br><span style='color:#666'>{$v['meaning']}</span></td> 
                            <td style='text-align:right; width: 30px;'><a href='admin.php?page=lessons&del_vocab={$v['id_vocab']}&lesson_id={$lesson['id_lesson']}' class='btn-action btn-delete' style='padding: 3px 6px;'><i class='fas fa-times'></i></a></td> 
                          </tr>"; 
                } 
                ?> 
            </table> 
        </div> 
    </div> 

    <!-- PHẦN NGỮ PHÁP -->
    <div class="card"> 
        <h3 style="margin-top: 0; color: #9b59b6; border-bottom: 2px dashed #eee; padding-bottom: 10px;"><i class="fas fa-scroll"></i> 3. Ngữ pháp</h3> 
        
        <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 10px; margin-bottom: 15px;"> 
            <input type='hidden' name='id_lesson' value='<?= $lesson['id_lesson'] ?>'> 
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="structure" placeholder="Cấu trúc ngữ pháp (*)" required></div> 
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="meaning" placeholder="Ý nghĩa / Cách dùng (*)" required></div> 
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="romaji" placeholder="Phiên âm Romaji (Nếu có)"></div>
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="example" placeholder="Ví dụ (Tiếng Nhật)"></div>
            <div class="form-group" style="margin-bottom: 10px;"><input type="text" name="example_vi" placeholder="Dịch nghĩa câu ví dụ (Tiếng Việt)"></div>
            <button type="submit" name="add_grammar" class="btn" style="background: #9b59b6; width: 100%;"><i class="fas fa-plus"></i> Thêm ngữ pháp</button> 
        </form> 

        <div style="max-height: 300px; overflow-y: auto;"> 
            <table style="font-size: 13px;"> 
                <?php 
                $gram_res = $conn->query("SELECT * FROM grammars WHERE id_lesson = {$lesson['id_lesson']} ORDER BY id_grammar DESC"); 
                while($g = $gram_res->fetch_assoc()){ 
                    echo "<tr> 
                            <td><b style='color:#9b59b6'>{$g['structure']}</b><br><span style='color:#666'>{$g['meaning']}</span></td> 
                            <td style='text-align:right; width: 30px;'><a href='admin.php?page=lessons&del_grammar={$g['id_grammar']}&lesson_id={$lesson['id_lesson']}' class='btn-action btn-delete' style='padding: 3px 6px;'><i class='fas fa-times'></i></a></td> 
                          </tr>"; 
                } 
                ?> 
            </table> 
        </div> 
    </div> 

    <!-- PHẦN BÀI TẬP -->
    <div class="card"> 
        <h3 style="margin-top: 0; color: #f39c12; border-bottom: 2px dashed #eee; padding-bottom: 10px;"><i class="fas fa-tasks"></i> 4. Bài tập trắc nghiệm</h3> 
        
        <form method="POST" style="background: #fffcf5; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fdebd0;"> 
            <input type='hidden' name='id_lesson' value='<?= $lesson['id_lesson'] ?>'> 
            <div class="form-group"> 
                <label>Câu hỏi:</label> 
                <input type="text" name="question" required placeholder="VD: 私は学生 ______。"> 
            </div> 
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px;"> 
                <div class="form-group" style="margin-bottom: 0;"><input type="text" name="option_A" required placeholder="Đáp án A"></div> 
                <div class="form-group" style="margin-bottom: 0;"><input type="text" name="option_B" required placeholder="Đáp án B"></div> 
                <div class="form-group" style="margin-bottom: 0;"><input type="text" name="option_C" required placeholder="Đáp án C"></div> 
                <div class="form-group" style="margin-bottom: 0;"><input type="text" name="option_D" required placeholder="Đáp án D"></div> 
            </div> 
            <div style="display: flex; gap: 15px; align-items: center; margin-top: 15px;"> 
                <label style="font-weight: bold; margin-bottom: 0;">Đáp án đúng:</label> 
                <select name="correct_option" style="width: 100px; padding: 8px;"> 
                    <option value="A">A</option> 
                    <option value="B">B</option> 
                    <option value="C">C</option> 
                    <option value="D">D</option> 
                </select> 
                <button type="submit" name="add_ex" class="btn" style="background: #f39c12; margin-left: auto;"><i class="fas fa-plus"></i> Thêm câu hỏi</button> 
            </div> 
        </form> 

        <table style="font-size: 14px;"> 
            <tr><th>Câu hỏi</th><th>Đáp án</th><th>Đúng</th><th style="width: 50px;">Xóa</th></tr> 
            <?php 
            $ex_res = $conn->query("SELECT * FROM exercise WHERE id_lesson = {$lesson['id_lesson']} ORDER BY id_ex DESC"); 
            while($e = $ex_res->fetch_assoc()){ 
                echo "<tr> 
                        <td><b>{$e['question']}</b></td> 
                        <td style='color: #666;'>A. {$e['option_A']} | B. {$e['option_B']}<br>C. {$e['option_C']} | D. {$e['option_D']}</td> 
                        <td><span style='background: #e6ffed; color: #2ecc71; padding: 3px 8px; border-radius: 4px; font-weight: bold;'>{$e['correct_option']}</span></td> 
                        <td style='text-align: center;'><a href='admin.php?page=lessons&del_ex={$e['id_ex']}&lesson_id={$lesson['id_lesson']}' class='btn-action btn-delete' style='padding: 5px 8px;'><i class='fas fa-trash'></i></a></td> 
                      </tr>"; 
            } 
            ?> 
        </table> 
    </div> 

    <?php endif; ?> 
    <?php 
    } 
    ?>