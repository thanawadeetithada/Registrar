<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

// รับข้อมูลจาก POST
$student_id = $_POST['student_id'] ?? '';
$academic_year = $_POST['academic_year'] ?? '';
$subject_ids = $_POST['subject_ids'] ?? [];
$semester1_scores = $_POST['semester1_scores'] ?? [];
$semester2_scores = $_POST['semester2_scores'] ?? [];

// ตรวจสอบว่าได้รับข้อมูลครบหรือไม่
if (!$student_id || !$academic_year || empty($subject_ids)) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    // ใช้ mysqli เพื่อเตรียมคำสั่ง SQL
    $stmt = $conn->prepare("
        INSERT INTO student_scores (student_id, subject_id, academic_year, semester1_score, semester2_score)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            semester1_score = VALUES(semester1_score),
            semester2_score = VALUES(semester2_score)
    ");

    // ทำการเตรียมข้อมูลและบันทึกลงฐานข้อมูล
    foreach ($subject_ids as $index => $subject_id) {
        $s1 = is_numeric($semester1_scores[$index]) ? floatval($semester1_scores[$index]) : null;
        $s2 = is_numeric($semester2_scores[$index]) ? floatval($semester2_scores[$index]) : null;

        // ผูกพารามิเตอร์กับคำสั่ง SQL
        $stmt->bind_param("sssss", $student_id, $subject_id, $academic_year, $s1, $s2);
        
        // Execute SQL statement
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
