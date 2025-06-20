<?php
require_once 'db.php';

$student_id = $_POST['student_id'] ?? '';

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'ไม่มี student_id']);
    exit;
}

try {
    // เริ่ม transaction
    $pdo->beginTransaction();

    // ลบคะแนนของนักเรียนนี้
    $deleteScores = $pdo->prepare("DELETE FROM student_scores WHERE student_id = :student_id");
    $deleteScores->execute(['student_id' => $student_id]);

    // ลบนักเรียนออกจากระบบ
    $deleteStudent = $pdo->prepare("DELETE FROM students WHERE student_id = :student_id");
    $deleteStudent->execute(['student_id' => $student_id]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
