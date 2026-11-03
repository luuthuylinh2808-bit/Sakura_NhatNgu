<?php
require_once '../config/database.php';

// Xử lý nút xóa tin nhắn
if (isset($_GET['del_contact_id'])) {
    $del_id = (int)$_GET['del_contact_id'];
    $conn->query("DELETE FROM contacts WHERE id_contact = $del_id");
    echo "<script>window.location='admin.php?page=contacts';</script>";
}
?>

<h2 class='page-title'>
    <i class="fas fa-envelope" style="color: var(--pink-primary);"></i> Hộp thư từ học viên
</h2>
<p style="color: #666; margin-bottom: 20px;">Danh sách phản hồi, góp ý hoặc cần hỗ trợ từ học viên.</p>

<div class="card" style="padding: 0; overflow: hidden;">
    <table>
        <tr>
            <th style="padding-left: 25px; width: 60px;">ID</th>
            <th style="width: 200px;">Người gửi</th>
            <th>Nội dung</th>
            <th style="width: 150px;">Thời gian</th>
            <th style="width: 80px; text-align: right; padding-right: 25px;">Thao tác</th>
        </tr>
        
        <?php
        // Lấy dữ liệu từ bảng contacts
        $result = $conn->query("SELECT * FROM contacts ORDER BY submitted_at DESC");
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $date = date("d/m/Y H:i", strtotime($row['submitted_at']));
                echo "<tr>
                        <td style='padding-left: 25px; color: #999;'>#{$row['id_contact']}</td>
                        <td>
                            <b style='color: var(--pink-primary); display: block; margin-bottom: 3px;'>{$row['name']}</b>
                            <a href='mailto:{$row['email']}' style='font-size: 13px; color: #666; text-decoration: none;'><i class='fas fa-reply'></i> {$row['email']}</a>
                        </td>
                        <td style='line-height: 1.5; color: #444;'>" . nl2br(htmlspecialchars($row['message'])) . "</td>
                        <td style='font-size: 13px; color: #888;'>{$date}</td>
                        <td style='text-align: right; padding-right: 25px;'>
                            <a href='admin.php?page=contacts&del_contact_id={$row['id_contact']}' class='btn btn-action btn-delete' title='Xóa' onclick=\"return confirm('Xóa tin nhắn này?');\">
                                <i class='fas fa-trash'></i>
                            </a>
                        </td>
                      </tr>";
            }
        } else {
            // Nếu chưa có ai gửi tin nhắn thì báo trống
            echo "<tr><td colspan='5' style='text-align: center; padding: 60px 20px; color: #999;'>
                    <i class='far fa-envelope-open fa-3x' style='margin-bottom:15px; color:#eee;'></i><br>
                    Chưa có tin nhắn liên hệ nào.
                  </td></tr>";
        }
        ?>
    </table>
</div>