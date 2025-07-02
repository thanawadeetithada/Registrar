<?php
require_once 'db.php';

$student_id = $_POST['student_id'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$academic_year = $_POST['academic_year'] ?? '';

if (!$student_id || !$subject_id || !$academic_year) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM student_scores WHERE student_id = ? AND subject_id = ? AND academic_year = ?");
$stmt->bind_param("sss", $student_id, $subject_id, $academic_year);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบข้อมูลได้']);
}
$stmt->close();
?>