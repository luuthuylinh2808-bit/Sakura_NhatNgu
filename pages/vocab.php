<div class="white-card">
    <?php
    // 1. Kết nối cơ sở dữ liệu
    $conn = mysqli_connect("localhost", "root", "", "sakuraweb");
    if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

    // 2. Lấy level (mặc định N5) và trang hiện tại (mặc định trang 1)
    $current_level = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : 'N5';
    if (!in_array($current_level, ['N4', 'N5'])) $current_level = 'N5';

    $limit = 20; // Số lượng từ vựng trên mỗi trang
    $p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($p < 1) $p = 1;
    $offset = ($p - 1) * $limit;

    // 3. Tính tổng số từ vựng để chia trang
    $count_sql = "SELECT COUNT(*) FROM vocabulary v JOIN lessons l ON v.id_lesson = l.id_lesson WHERE l.level = '$current_level'";
    $total_rows = mysqli_query($conn, $count_sql)->fetch_row()[0];
    $total_pages = ceil($total_rows / $limit);
    ?>

    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <a href="index.php?page=vocab&level=N5&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N5' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>">Từ vựng N5</a>
        
        <a href="index.php?page=vocab&level=N4&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N4' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>">Từ vựng N4</a>
    </div>

    <h2><i class="fas fa-spell-check" style="color: var(--pink-primary);"></i> Sổ tay Từ vựng <span style="color: var(--pink-primary);"><?= $current_level ?></span></h2>
    <p style="color: #888; font-size: 14px; margin-bottom: 20px;">Trang <?= $p ?> / <?= $total_pages ?> - Tổng số <?= $total_rows ?> từ vựng.</p>

    <style>
        .vocab-table { width: 100%; border-collapse: collapse; }
        .vocab-table th { padding: 15px; text-align: left; background: #fdf2f4; color: var(--pink-primary); border-bottom: 2px solid #f8e1e5; }
        .vocab-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        .ja-font { font-family: 'Noto Sans JP', sans-serif; font-size: 16px; font-weight: bold; color: #333; }
        .lesson-divider { font-weight: 800; color: #555; text-align: center; font-size: 13px; background: #f8f9fa; }
        
        /* Pagination Styling */
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 25px; }
        .page-link { padding: 8px 16px; border-radius: 10px; border: 1px solid #eee; text-decoration: none; color: #666; font-weight: bold; transition: 0.3s; }
        .page-link:hover { background: var(--pink-light); color: var(--pink-primary); }
        .page-link.active { background: var(--pink-primary); color: white; border-color: var(--pink-primary); }
    </style>

    <table class="vocab-table">
        <thead>
            <tr><th>Từ vựng</th><th>Hán tự</th><th>Ý nghĩa</th><th style="text-align: center;">Nghe</th></tr>
        </thead>
        <tbody>
            <?php
            // 4. Lấy dữ liệu theo trang (LIMIT $offset, $limit)
            $sql = "SELECT v.*, l.order_index 
                    FROM vocabulary v 
                    JOIN lessons l ON v.id_lesson = l.id_lesson 
                    WHERE l.level = '$current_level' 
                    ORDER BY l.order_index ASC, v.id_vocab ASC
                    LIMIT $offset, $limit";
            $result = mysqli_query($conn, $sql);

            $current_lesson = 0;
            if ($result && mysqli_num_rows($result) > 0) {
                while ($r = mysqli_fetch_assoc($result)) {
                    if ($current_lesson != $r['order_index']) {
                        $current_lesson = $r['order_index'];
                        echo "<tr><td colspan='4' class='lesson-divider'>BÀI {$current_lesson}</td></tr>";
                    }
                    $text_to_read = !empty(trim($r['kanji'])) ? trim($r['kanji']) : trim($r['hiragana']);
                    $safe_text = htmlspecialchars(addslashes($text_to_read)); //
                    
                    echo "<tr>
                            <td class='ja-font'>{$r['hiragana']}</td>
                            <td class='ja-font'>{$r['kanji']}</td>
                            <td style='color: #555;'>{$r['meaning']}</td>
                            <td style='text-align: center;'>
                                <i class='fas fa-volume-up' onclick=\"playAudio(event, '{$safe_text}')\" style='cursor:pointer; color: var(--pink-primary); transition: 0.2s;'></i>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; padding: 20px; color: #999;'>Chưa có dữ liệu.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($p > 1): ?>
            <a href="index.php?page=vocab&level=<?= $current_level ?>&p=<?= $p - 1 ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=vocab&level=<?= $current_level ?>&p=<?= $i ?>" class="page-link <?= ($i == $p) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($p < $total_pages): ?>
            <a href="index.php?page=vocab&level=<?= $current_level ?>&p=<?= $p + 1 ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Sử dụng hàm playAudio từ main.js hoặc định nghĩa trực tiếp
function playAudio(event, text) {
    if (event) event.stopPropagation();
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        let msg = new SpeechSynthesisUtterance();
        msg.text = text;
        msg.lang = 'ja-JP';
        msg.rate = 0.9;
        window.speechSynthesis.speak(msg);
    }
}
</script>