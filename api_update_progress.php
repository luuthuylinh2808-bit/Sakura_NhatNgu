<?php
session_start();
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['id_lesson'])) {
    echo json_encode(['status' => 'error']); exit;
}

$user_id = $_SESSION['user_id'];
$id_lesson = $_POST['id_lesson'];
$today = date('Y-m-d');

// 1. Ghi nhận tiến độ (nếu chưa có)
$check = $conn->prepare("SELECT id_progress FROM user_progress WHERE id = ? AND id_lesson = ?");
$check->bind_param("ii", $user_id, $id_lesson);
$check->execute();
if ($check->get_result()->num_rows == 0) {
    $ins = $conn->prepare("INSERT INTO user_progress (id, id_lesson, completed_at) VALUES (?, ?, NOW())");
    $ins->bind_param("ii", $user_id, $id_lesson);
    $ins->execute();
}

// 2. Cập nhật Streak
$u = $conn->prepare("SELECT streak, last_study_date FROM users WHERE id = ?");
$u->bind_param("i", $user_id);
$u->execute();
$user_data = $u->get_result()->fetch_assoc();

$streak = $user_data['streak'];
$last_date = $user_data['last_study_date'];
$yesterday = date('Y-m-d', strtotime('-1 day'));

if ($last_date != $today) {
    if ($last_date == $yesterday) $streak++;
    else $streak = 1;
    
    $upd = $conn->prepare("UPDATE users SET streak = ?, last_study_date = ? WHERE id = ?");
    $upd->bind_param("isi", $streak, $today, $user_id);
    $upd->execute();
}

echo json_encode(['status' => 'success', 'streak' => $streak]);