<div class="banner" style="background: url('https://i.postimg.cc/DZJXF7b6/Banner-png.png') right center / cover no-repeat; overflow: hidden; display: block; background-color: #ffb6c1; position: relative; border-radius: 25px; padding: 40px; margin-bottom: 30px;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to right, rgba(255, 241, 243, 0.95) 0%, rgba(255, 241, 243, 0.5) 50%, transparent 100%); z-index: 0;"></div>
    
    <div style="position: relative; z-index: 1; max-width: 60%; color: #444;">
         <h1 style="font-size: 26px; color: #b33939; margin-bottom: 10px;">Học tiếng Nhật theo cách của bạn 🌸</h1>
         <p style="margin-bottom: 20px; font-weight: 600; color: #555;">Lộ trình học bài bản từ N5 đến N4 - Bài học dễ hiểu, bài tập thực tế, giúp bạn tự tin chinh phục JLPT.</p>
         <div style="display: flex; gap: 15px;">
             <a href="index.php?page=hoc&id_lesson=1" style="text-decoration: none;">
                <button class="btn-main" style="background: #ff6b81; color: white; margin-top: 0; padding: 12px 30px; border-radius: 30px; font-weight: 800; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"><i class="fas fa-play"></i> Học ngay bài đầu tiên</button>
             </a>
             <a href="index.php?page=lessons" style="text-decoration: none;">
                <button class="btn-main" style="background: white; border: 1px solid #ff6b81; color: #ff6b81; margin-top: 0; padding: 12px 30px; border-radius: 30px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"><i class="fas fa-list"></i> Xem lộ trình học</button>
             </a>
         </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
    <div style="background: white; padding: 15px; border-radius: 15px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #fdf2f4;">
        <div style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: #ffeaa7; color: #d35400;"><i class="fas fa-book-open"></i></div>
        <div>
            <h4 style="font-size: 18px; color: #444; line-height: 1.2; margin-bottom: 3px;">50</h4>
            <p style="font-size: 12px; color: #888; font-weight: 600;">Tổng bài học</p>
        </div>
    </div>
    <div style="background: white; padding: 15px; border-radius: 15px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #fdf2f4;">
        <div style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: #dff9fb; color: #22a6b3;"><i class="fas fa-book"></i></div>
        <div>
            <h4 style="font-size: 18px; color: #444; line-height: 1.2; margin-bottom: 3px;">~800</h4>
            <p style="font-size: 12px; color: #888; font-weight: 600;">Từ vựng N5</p>
        </div>
    </div>
    <div style="background: white; padding: 15px; border-radius: 15px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #fdf2f4;">
        <div style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: #e0e7ff; color: #4834d4;"><i class="fas fa-file-alt"></i></div>
        <div>
            <h4 style="font-size: 18px; color: #444; line-height: 1.2; margin-bottom: 3px;">~80</h4>
            <p style="font-size: 12px; color: #888; font-weight: 600;">Ngữ pháp trọng tâm</p>
        </div>
    </div>
    <div style="background: white; padding: 15px; border-radius: 15px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #fdf2f4;">
        <div style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: #f8c291; color: #e55039;"><i class="fas fa-bullseye"></i></div>
        <div>
            <h4 style="font-size: 18px; color: #444; line-height: 1.2; margin-bottom: 3px;">200+</h4>
            <p style="font-size: 12px; color: #888; font-weight: 600;">Bài tập luyện tập</p>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 style="font-size:20px; color: #444;"><i class="fas fa-star" style="color:#ff6b81; font-size:16px;"></i> Bài học N5 nổi bật</h2>
    <a href="index.php?page=lessons" style="color:#ff6b81; font-weight:800; font-size:14px;">Xem tất cả →</a>
</div>

<div class="grid-cards" id="latest-lessons">
    <?php
    // 1. Kết nối CSDL (Đảm bảo thông số đúng với DB của bạn)
    $conn = mysqli_connect("localhost", "root", "", "sakuraweb");
    if (!$conn) {
        die("Lỗi kết nối: " . mysqli_connect_error());
    }

    // 2. Lấy 4 bài đầu tiên của N5 dựa theo order_index
    $sql = "SELECT * FROM lessons WHERE level = 'N5' ORDER BY order_index ASC LIMIT 4";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $id_lesson = $row['id_lesson'];
            $title = $row['title'];
            $description = $row['description'];
            $order_index = $row['order_index'];
            $level = $row['level'];
    ?>
            <div class="lesson-card">
                <span class="badge"><?php echo $level; ?> - Bài <?php echo $order_index; ?></span>
                <h3 style="margin:12px 0 5px;"><?php echo htmlspecialchars($title); ?></h3>
                <p style="color:#888; font-size:14px;"><?php echo htmlspecialchars($description); ?></p>
                <div class="progress-container">
                    <small>Hoàn thành: 0%</small>
                    <div class="p-bar"><div class="p-fill" style="width:0%;"></div></div>
                </div>
                <a href="index.php?page=hoc&id_lesson=<?php echo $id_lesson; ?>" style="text-decoration: none;">
                    <button class="btn-lvl active" style="width:100%;">Học tiếp</button>
                </a>
            </div>
    <?php
        }
    } else {
        echo "<p style='grid-column: 1/-1; text-align:center; color:#999;'>Đang cập nhật bài học...</p>";
    }
    ?>

</div>