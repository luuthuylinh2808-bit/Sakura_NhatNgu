<div class="white-card">
    <h2><i class="fas fa-heart" style="color: #e74c3c;"></i> Bài học yêu thích của bạn</h2>
    <p style="color:#888; margin-bottom:20px;">Những bài học bạn đã đánh dấu để ôn tập lại.</p>

    <?php
    if (!isset($_SESSION['user_id'])) {
        echo "<div style='text-align: center; padding: 40px;'>
                <i class='fas fa-user-lock' style='font-size: 40px; color: #ddd; margin-bottom: 15px;'></i>
                <p style='color:#999;'>Vui lòng đăng nhập để xem danh sách yêu thích.</p>
                <a href='index.php?page=login' style='color: var(--pink-primary); font-weight: bold; text-decoration: none;'>Đăng nhập ngay</a>
              </div></div>";
        return;
    }

    $conn = mysqli_connect("localhost", "root", "", "sakuraweb");
    $user_id = $_SESSION['user_id'];

    // Lấy bài học kết nối với bảng favourite_lesson
    $sql = "SELECT l.* FROM lessons l 
            JOIN favourite_lesson f ON l.id_lesson = f.id_lesson 
            WHERE f.id = $user_id 
            ORDER BY f.id_lesson DESC";
    
    $result = mysqli_query($conn, $sql);
    ?>

    <div class="grid-cards">
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $id_lesson = $row['id_lesson'];
                $title = htmlspecialchars($row['title']);
                $description = htmlspecialchars($row['description']);
                $order_index = $row['order_index'];
                $level = $row['level'];
        ?>
                <div class="lesson-card" style="position: relative; border-top: 3px solid #e74c3c;">
                    <span class="badge" style="background: #e74c3c; color: white;"><?php echo $level; ?> - Bài <?php echo $order_index; ?></span>
                    <h3 style="margin:12px 0 5px;"><?php echo $title; ?></h3>
                    <p style="color:#888; font-size:14px;"><?php echo $description; ?></p>
                    
                    <a href="index.php?page=hoc&id_lesson=<?php echo $id_lesson; ?>" style="text-decoration: none; display: block; margin-top: 15px;">
                        <button class="btn-lvl active" style="width:100%; background: #e74c3c; border-color: #e74c3c;">Ôn tập ngay</button>
                    </a>
                </div>
        <?php
            }
        } else {
            echo "<div style='grid-column: 1 / -1; text-align: center; padding: 40px;'>
                    <i class='far fa-folder-open' style='font-size: 40px; color: #ddd; margin-bottom: 15px;'></i>
                    <p style='color:#999;'>Bạn chưa có bài học yêu thích nào. Hãy ra danh sách và 'thả tim' nhé!</p>
                  </div>";
        }
        ?>
    </div>
</div>