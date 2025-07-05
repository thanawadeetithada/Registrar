<?php
require_once 'db.php';
header('Content-Type: application/json');

$student_id = $_POST['student_id'] ?? '';

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'ไม่มี student_id']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. ลบจาก student_scores
    $deleteScores = $conn->prepare("DELETE FROM student_scores WHERE student_id = ?");
    $deleteScores->bind_param("s", $student_id);
    $deleteScores->execute();

    // 2. ลบจาก students
    $deleteStudent = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $deleteStudent->bind_param("s", $student_id);
    $deleteStudent->execute();

    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
