<?php
require_once 'db.php';
header('Content-Type: application/json');

// รับค่าจาก POST
$student_id = $_POST['student_id'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$academic_year = $_POST['academic_year'] ?? '';

if (!$student_id || !$subject_id || !$academic_year) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);
    exit;
}

try {
    $conn->begin_transaction();

    // ลบเฉพาะคะแนนของนักเรียนนี้ในวิชาและปีที่ระบุ
    $deleteScores = $conn->prepare("
        DELETE FROM student_scores 
        WHERE student_id = ? AND subject_id = ? AND academic_year = ?
    ");
    $deleteScores->bind_param("sii", $student_id, $subject_id, $academic_year);
    $deleteScores->execute();

    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
