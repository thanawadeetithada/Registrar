<?php
require_once 'db.php';
header('Content-Type: application/json');

$student_id = $_POST['student_id'] ?? '';

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'ไม่มี student_id']);
    exit;
}

try {
    // เริ่ม transaction
    $conn->begin_transaction();

    // ลบคะแนนของนักเรียนนี้
    $deleteScores = $conn->prepare("DELETE FROM student_scores WHERE student_id = ?");
    $deleteScores->bind_param("s", $student_id);
    $deleteScores->execute();

    // ลบนักเรียนออกจากระบบ
    $deleteStudent = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $deleteStudent->bind_param("s", $student_id);
    $deleteStudent->execute();

    // ยืนยันการทำงานใน transaction
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // ยกเลิกการทำงานในกรณีที่เกิดข้อผิดพลาด
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
