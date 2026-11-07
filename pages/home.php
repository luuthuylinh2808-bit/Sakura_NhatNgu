<!-- BANNER CHÍNH SAKURA -->
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

<!-- 4 THỐNG KÊ NHANH -->
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

<!-- TIÊU ĐỀ BÀI HỌC N5 -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h2 style="font-size:20px; color: #444;"><i class="fas fa-star" style="color:#ff6b81; font-size:16px;"></i> Bài học N5 nổi bật</h2>
    <a href="index.php?page=lessons" style="color:#ff6b81; font-weight:800; font-size:14px; text-decoration:none;">Xem tất cả →</a>
</div>

<!-- DANH SÁCH BÀI HỌC N5 -->
<div class="grid-cards" id="latest-lessons" style="margin-bottom: 30px;">
    <?php
    $conn = mysqli_connect("localhost", "root", "", "sakuraweb");
    if (!$conn) {
        die("Lỗi kết nối: " . mysqli_connect_error());
    }

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

<!-- ========================================================= -->
<!-- KHỐI BÀI HỌC LUYỆN NGHE VIDEO CHẠY LƯỚT NGANG LIÊN TỤC    -->
<!-- ========================================================= -->
<style>
    .home-video-wrapper {
        background: #ffffff;
        border: 1px solid #ffe6ea;
        border-radius: 20px;
        padding: 22px 20px;
        margin-top: 10px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(255, 107, 129, 0.08);
        box-sizing: border-box;

        /* KÉO RỘNG BẰNG CHÍNH XÁC MÉP TRÁI VÀ MÉP PHẢI CỦA 2 SIDEBAR */
        width: calc(100% + 630px);
        margin-left: -285px;
        position: relative;
        z-index: 5;
        overflow: hidden;
    }

    @media (max-width: 1100px) {
        .home-video-wrapper {
            width: 100% !important;
            margin-left: 0 !important;
        }
    }

    /* KHUNG CHỨA VÀ HIỆU ỨNG CHẠY LƯỚT NGANG TỰ ĐỘNG */
    .home-video-scroll-container {
        width: 100%;
        overflow: hidden;
        padding: 12px 0 15px 0;
        position: relative;
    }

    .home-video-track {
        display: flex;
        gap: 20px;
        width: max-content;
        animation: sakuraMarquee 30s linear infinite;
    }

    /* TẠM DỪNG HIỆU ỨNG CHẠY KHI RÊ CHUỘT VÀO VÙNG VIDEO */
    .home-video-wrapper:hover .home-video-track {
        animation-play-state: paused;
    }

    /* TẠO VÒNG LẶP CHẠY LƯỚT VÔ TẬN */
    @keyframes sakuraMarquee {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    /* CARD VIDEO VỚI HIỆU ỨNG NHÔ LÊN + MÀU HỒNG HOA ANH ĐÀO */
    .video-card-box {
        min-width: 230px;
        max-width: 230px;
        background: #ffffff;
        border: 1.5px solid #ffe6ea;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.06);
        transition: all 0.35s cubic-bezier(0.25, 1, 0.5, 1);
        flex-shrink: 0;
        position: relative;
    }

    /* TÁC VỤ HOVER: NHÔ LÊN + PHÁT SÁNG MÀU HỒNG HOA ANH ĐÀO (SAKURA GLOW) */
    .video-card-box:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: #ff8fa3;
        background: #fff9fa;
        box-shadow: 0 14px 28px rgba(255, 107, 129, 0.25), 
                    0 0 20px rgba(255, 182, 193, 0.6);
        z-index: 10;
    }

    .video-img-box {
        position: relative;
        width: 100%;
        height: 130px;
        background: #fff0f3;
        overflow: hidden;
    }

    .video-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .video-card-box:hover .video-img-box img {
        transform: scale(1.08);
    }

    .video-btn-play {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 42px;
        height: 42px;
        background: #ff6b81;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.4);
        transition: all 0.3s ease;
    }

    .video-card-box:hover .video-btn-play {
        background: #ff4757;
        transform: translate(-50%, -50%) scale(1.1);
        box-shadow: 0 6px 16px rgba(255, 71, 87, 0.5);
    }

    .video-info-box {
        padding: 14px 12px 12px 12px;
    }

    .video-info-box h4 {
        font-size: 13.5px;
        font-weight: 800;
        color: #2d3436;
        margin: 0 0 10px 0;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn-watch-action {
        display: block;
        width: 100%;
        text-align: center;
        background: #fff0f3;
        color: #ff4757;
        border: 1px solid #ffccd5;
        border-radius: 12px;
        padding: 8px 0;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        box-sizing: border-box;
        transition: all 0.25s ease;
    }

    .video-card-box:hover .btn-watch-action {
        background: #ff6b81;
        color: #ffffff;
        border-color: #ff6b81;
        box-shadow: 0 4px 12px rgba(255, 107, 129, 0.3);
    }
</style>

<div class="home-video-wrapper">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="font-size:18px; color: #2d3436; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-headphones-alt" style="color:#ff6b81;"></i> Luyện nghe qua Video
        </h2>
        <a href="index.php?page=listening&level=N3" style="color:#ff6b81; font-weight:800; font-size:13px; text-decoration: none;">Xem tất cả →</a>
    </div>

    <div class="home-video-scroll-container">
        <div class="home-video-track">
            <?php
            $v_sql = "SELECT * FROM listening_lessons ORDER BY id DESC";
            $v_result = mysqli_query($conn, $v_sql);

            $videos = [];
            if ($v_result && mysqli_num_rows($v_result) > 0) {
                while ($v_row = mysqli_fetch_assoc($v_result)) {
                    $yt_id = 'dQw4w9WgXcQ';
                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $v_row['youtube_url'], $matches)) {
                        $yt_id = $matches[1];
                    }
                    $videos[] = [
                        'id' => $v_row['id'],
                        'title' => $v_row['title'],
                        'thumb' => "https://img.youtube.com/vi/{$yt_id}/hqdefault.jpg"
                    ];
                }
            }

            // Nhân bản danh sách để tạo hiệu ứng cuộn nối đuôi lặp lại vô tận
            $display_videos = array_merge($videos, $videos);
            if (count($display_videos) < 8) {
                $display_videos = array_merge($display_videos, $display_videos);
            }

            foreach ($display_videos as $v):
            ?>
                <div class="video-card-box">
                    <div class="video-img-box">
                        <img src="<?= htmlspecialchars($v['thumb']) ?>" alt="Thumbnail">
                        <a href="index.php?page=listening&level=N3&id=<?= (int)$v['id'] ?>" class="video-btn-play">
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                    <div class="video-info-box">
                        <h4><?= htmlspecialchars($v['title']) ?></h4>
                        <a href="index.php?page=listening&level=N3&id=<?= (int)$v['id'] ?>" class="btn-watch-action">
                            <i class="fas fa-play-circle"></i> Nghe ngay
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>