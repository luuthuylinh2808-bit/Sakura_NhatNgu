<div class="white-card"> 
    <?php 
    // 1. Kết nối cơ sở dữ liệu 
    $conn = mysqli_connect("localhost", "root", "", "sakuraweb"); 
    if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error()); 
 // [THÊM MỚI - FIX] TỰ ĐỘNG KÍCH HOẠT PREMIUM NẾU ĐÃ THANH TOÁN NHƯNG CHƯA MỞ KHÓA
if (isset($_SESSION['user_id'])) {
    $uid_fix = (int)$_SESSION['user_id'];
    
    // Kiểm tra xem user đã mở khóa N3 chưa
    $chk_fix = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $uid_fix AND level = 'N3'");
    $has_n3 = ($chk_fix && $chk_fix->num_rows > 0);
    
    if (!$has_n3) {
        // Nếu chưa, tìm đơn hàng đã completed gần nhất
        $completed_order = $conn->query("SELECT * FROM orders WHERE id_user = $uid_fix AND status = 'completed' ORDER BY id_order DESC LIMIT 1")->fetch_assoc();
        
        if ($completed_order) {
            $expiry_fix = null;
            switch ($completed_order['package_type']) {
                case 'N3_MONTH':    $expiry_fix = date('Y-m-d H:i:s', strtotime('+1 month')); break;
                case 'N3_6MONTH':   $expiry_fix = date('Y-m-d H:i:s', strtotime('+6 months')); break;
                case 'N3_LIFETIME': $expiry_fix = '2099-12-31 23:59:59'; break;
            }
            
            if ($expiry_fix) {
                $conn->query("UPDATE users SET premium_expiry = '$expiry_fix' WHERE id = $uid_fix");
                $conn->query("INSERT INTO user_course_unlocks (id_user, level) VALUES ($uid_fix, 'N3')");
            }
        }
    }
}
// [KẾT THÚC FIX]
    // 2. Lấy trình độ (level) và trang hiện tại 
    $current_level = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : 'N5'; 
 
    // [THÊM MỚI] Cho phép N3
    if (!in_array($current_level, ['N3', 'N4', 'N5'])) { 
        $current_level = 'N5'; 
    } 
 
    // [THÊM MỚI] Kiểm tra quyền Premium của User
    $has_premium = false; 
    $uid = 0;
    if (isset($_SESSION['user_id'])) { 
        $uid = (int)$_SESSION['user_id']; 
        // Kiểm tra hạn premium
        $prem = $conn->query("SELECT premium_expiry FROM users WHERE id = $uid")->fetch_assoc(); 
        if (!empty($prem['premium_expiry']) && strtotime($prem['premium_expiry']) > time()) { 
            $has_premium = true; 
        } 
        // Hoặc kiểm tra trong bảng user_course_unlocks
        if (!$has_premium) { 
            $chk = $conn->query("SELECT id FROM user_course_unlocks WHERE id_user = $uid AND level = '$current_level'"); 
            if ($chk && $chk->num_rows > 0) $has_premium = true; 
        } 
    } 
 
    $limit = 12; // Số bài học trên mỗi trang 
    $p = isset($_GET['p']) ? (int)$_GET['p'] : 1; 
    if ($p < 1) $p = 1; 
    $offset = ($p - 1) * $limit; 
 
    // 3. Lấy danh sách ID bài học đã yêu thích VÀ BÀI ĐÃ HỌC 
    $fav_lessons = []; 
    $completed_lessons = []; // Mảng chứa ID các bài đã hoàn thành 
 
    if (isset($_SESSION['user_id'])) { 
        $uid = $_SESSION['user_id']; 
 
        // Lấy bài yêu thích
        $res_fav = $conn->query("SELECT id_lesson FROM favourite_lesson WHERE id = $uid"); 
        while ($r = $res_fav->fetch_assoc()) { 
            $fav_lessons[] = $r['id_lesson']; 
        } 
 
        // Lấy các bài đã học (để set 100%) 
        $res_prog = $conn->query("SELECT id_lesson FROM user_progress WHERE id = $uid"); 
        while ($r = $res_prog->fetch_assoc()) { 
            $completed_lessons[] = $r['id_lesson']; 
        } 
    }
    
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : ''; 
 
    // ======================================= 
    // LOGIC TÌM KIẾM THÔNG MINH 
    // ======================================= 
    $where_sql = ""; 
    if ($search !== '') { 
        $search_number = preg_replace('/[^0-9]/', '', $search); 
        if (!empty($search_number)) { 
            $where_sql = "title LIKE '%$search%' OR order_index = '$search_number'"; 
        } else { 
            $where_sql = "title LIKE '%$search%'"; 
        } 
    } 
 
    // 4. Tính toán tổng số bài để chia trang 
    if ($search !== '') { 
        $count_sql = "SELECT COUNT(*) FROM lessons WHERE $where_sql"; 
    } else { 
        $count_sql = "SELECT COUNT(*) FROM lessons WHERE level = '$current_level'"; 
    } 
    $total_rows = mysqli_query($conn, $count_sql)->fetch_row()[0]; 
    $total_pages = ceil($total_rows / $limit); 
    ?> 
 
    <?php if ($search === ''): ?> 
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;"> 
        <a href="index.php?page=lessons&level=N5&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N5' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>">Tiếng Nhật N5</a> 
 
        <a href="index.php?page=lessons&level=N4&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N4' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>">Tiếng Nhật N4</a> 

        <!-- [THÊM MỚI] Nút chọn N3 -->
        <a href="index.php?page=lessons&level=N3&p=1" class="btn" style="padding: 10px 25px; border-radius: 20px; text-decoration: none; font-weight: bold; transition: 0.3s; <?= $current_level == 'N3' ? 'background: var(--pink-primary); color: white; box-shadow: 0 4px 10px rgba(255,107,129,0.3);' : 'background: #f1f3f5; color: #888;' ?>"><i class="fas fa-crown" style="color: #f39c12;"></i> Tiếng Nhật N3</a> 
    </div> 
    <?php endif; ?> 
 
    <?php 
    if ($search !== '') { 
        $sql = "SELECT * FROM lessons WHERE $where_sql ORDER BY order_index ASC LIMIT $offset, $limit"; 
        echo "<h2><i class='fas fa-search'></i> Kết quả cho: '" . htmlspecialchars($search) . "'</h2>"; 
    } else { 
        $sql = "SELECT * FROM lessons WHERE level = '$current_level' ORDER BY order_index ASC LIMIT $offset, $limit"; 
        echo "<h2><i class='fas fa-book-open'></i> Danh sách bài học <span id='level-title'>$current_level</span></h2>"; 
    } 
    ?> 

    <!-- [THÊM MỚI] Banner thông báo N3 Premium -->
    <?php if ($current_level === 'N3'): ?>
        <div style="background: linear-gradient(135deg, #fff5f7 0%, #ffe6ea 100%); border-left: 5px solid var(--pink-primary); padding: 15px 20px; border-radius: 10px; margin-bottom: 20px;">
            <i class="fas fa-crown" style="color: #f39c12; font-size: 20px;"></i> 
            <b>Khóa học N3 Premium</b> - Bài 1 học thử miễn phí. Các bài còn lại yêu cầu nâng cấp để mở khóa. 
            <?php if (!$has_premium): ?>
                <a href="index.php?page=pricing" style="color: var(--pink-primary); font-weight: bold; text-decoration: underline; margin-left: 10px;">Nâng cấp ngay →</a>
            <?php else: ?>
                <span style="color: #2ecc71; font-weight: bold; margin-left: 10px;"><i class="fas fa-check-circle"></i> Bạn đã có quyền truy cập</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
 
    <p style="color:#888; margin-bottom:20px;">Trang <?= $p ?> / <?= $total_pages ?> - Lộ trình theo giáo trình Minna no Nihongo.</p> 
 
    <div class="grid-cards" id="lessons-container"> 
        <?php 
        $result = mysqli_query($conn, $sql); 
        if ($result && mysqli_num_rows($result) > 0) { 
            while ($row = $result->fetch_assoc()) { 
                $id_lesson = $row['id_lesson']; 
                $title = htmlspecialchars($row['title']); 
                $description = htmlspecialchars($row['description']); 
                $order_index = $row['order_index']; 
                $level = $row['level']; 
 
                // [THÊM MỚI] Kiểm tra Premium / Khóa bài
                $is_premium = isset($row['is_premium']) ? (int)$row['is_premium'] : 0;
                $is_locked = ($is_premium === 1 && !$has_premium);
 
                $is_fav = in_array($id_lesson, $fav_lessons); 
                $heart_class = $is_fav ? "fas fa-heart" : "far fa-heart"; 
                $heart_color = $is_fav ? "#e74c3c" : "#ccc"; 
 
                // --- KIỂM TRA & HIỂN THỊ 100% NẾU ĐÃ HỌC --- 
                $is_completed = in_array($id_lesson, $completed_lessons); 
                $prog_percent = $is_completed ? 100 : 0; 
                $prog_color = $is_completed ? "#2ecc71" : "var(--pink-primary)"; 
                $btn_text = $is_completed ? "<i class='fas fa-check-circle'></i> Ôn tập lại" : "Học ngay"; 
                $btn_bg = $is_completed ? "background: #f1f3f5; color: #333; border: 1px solid #ddd;" : ""; 
                ?> 
                <div class="lesson-card" style="position: relative; <?= $is_locked ? 'opacity: 0.85;' : '' ?>"> 
                    <i class="<?php echo $heart_class; ?> btn-heart" 
                        data-id="<?php echo $id_lesson; ?>" 
                        style="position: absolute; top: 15px; right: 15px; font-size: 22px; color: <?php echo $heart_color; ?>; cursor: pointer; transition: 0.3s; z-index: 10;"> 
                    </i> 
 
                    <span class="badge"><?php echo $level; ?> - Bài <?php echo $order_index; ?></span> 

                    <!-- [THÊM MỚI] Badge trạng thái Premium -->
                    <?php if ($is_locked): ?>
                        <span class="badge" style="background: #f39c12; margin-left: 5px;"><i class="fas fa-lock"></i> Premium</span>
                    <?php elseif ($is_premium === 1): ?>
                        <span class="badge" style="background: #2ecc71; margin-left: 5px;"><i class="fas fa-unlock"></i> Đã mở</span>
                    <?php endif; ?>

                    <h3 style="margin:12px 0 5px; padding-right: 25px;"><?php echo $title; ?></h3> 
                    <p style="color:#888; font-size:14px;"><?php echo $description; ?></p> 
 
                    <div class="progress-container"> 
                        <small style="<?= $is_completed ? 'color:#2ecc71; font-weight:bold;' : '' ?>">Hoàn thành: <?php echo $prog_percent; ?>%</small> 
                        <div class="p-bar"><div class="p-fill" style="width:<?php echo $prog_percent; ?>%; background: <?php echo $prog_color; ?>;"></div></div> 
                    </div> 

                    <!-- [THÊM MỚI] Phân nhánh nút bấm: nếu khóa thì hiện nút "Mở khóa", nếu mở thì giữ nguyên nút cũ -->
                    <?php if ($is_locked): ?>
                        <a href="index.php?page=pricing" style="text-decoration: none;"> 
                            <button class="btn-lvl active" style="width:100%; background: #f39c12; color: white;"><i class="fas fa-lock"></i> Mở khóa N3</button> 
                        </a> 
                    <?php else: ?>
                        <a href="index.php?page=hoc&id_lesson=<?php echo $id_lesson; ?>" style="text-decoration: none;"> 
                            <button class="btn-lvl active" style="width:100%; <?php echo $btn_bg; ?>"><?php echo $btn_text; ?></button> 
                        </a> 
                    <?php endif; ?>
                </div> 
                <?php 
            } 
        } else { 
            echo "<div style='grid-column: 1 / -1; text-align: center; padding: 40px;'><p style='color:#999;'>Không tìm thấy bài học nào.</p></div>"; 
        } 
        ?> 
    </div> 
 
    <style> 
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 30px; } 
        .page-link { padding: 10px 18px; border-radius: 12px; border: 1px solid #eee; text-decoration: none; color: #666; font-weight: bold; transition: 0.3s; } 
        .page-link:hover { background: var(--pink-light); color: var(--pink-primary); } 
        .page-link.active { background: var(--pink-primary); color: white; border-color: var(--pink-primary); } 
    </style> 
 
    <?php if ($total_pages > 1): ?> 
    <div class="pagination"> 
        <?php 
        $base_url = "index.php?page=lessons" . ($search !== '' ? "&search=$search" : "&level=$current_level"); 
        ?> 
 
        <?php if ($p > 1): ?> 
            <a href="<?= $base_url ?>&p=<?= $p - 1 ?>" class="page-link"><i class="fas fa-chevron-left"></i></a> 
        <?php endif; ?> 
 
        <?php for ($i = 1; $i <= $total_pages; $i++): ?> 
            <a href="<?= $base_url ?>&p=<?= $i ?>" class="page-link <?= ($i == $p) ? 'active' : '' ?>"><?= $i ?></a> 
        <?php endfor; ?> 
 
        <?php if ($p < $total_pages): ?> 
            <a href="<?= $base_url ?>&p=<?= $p + 1 ?>" class="page-link"><i class="fas fa-chevron-right"></i></a> 
        <?php endif; ?> 
    </div> 
    <?php endif; ?> 
</div> 
 
<script> 
// Xử lý trái tim yêu thích
document.querySelectorAll('.btn-heart').forEach(btn => { 
    btn.addEventListener('click', function() { 
        let isLogged = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>; 
        if (!isLogged) { 
            alert("Bạn cần đăng nhập để lưu bài học yêu thích nhé! 🌸"); 
            return; 
        } 
        let lessonId = this.getAttribute('data-id'); 
        let icon = this; 
        let formData = new FormData(); 
        formData.append('id_lesson', lessonId); 
 
        fetch('api_toggle_favorite.php', { method: 'POST', body: formData }) 
            .then(res => res.json()) 
            .then(data => { 
                if (data.status === 'added') { 
                    icon.className = "fas fa-heart btn-heart"; 
                    icon.style.color = '#e74c3c'; 
                } else { 
                    icon.className = "far fa-heart btn-heart"; 
                    icon.style.color = '#ccc'; 
                } 
            }); 
    }); 
}); 
</script>