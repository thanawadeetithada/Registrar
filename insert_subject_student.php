<?php
require_once 'db.php';

$student_id = $_POST['student_id'] ?? '';
$academic_year = $_POST['academic_year'] ?? '';
$selected_subject_ids = $_POST['subject_ids'] ?? []; // วิชาที่ยังติ๊กอยู่
$removed_subject_ids = $_POST['removed_subject_ids'] ?? ''; // ที่เอา checkbox ออก

if (!is_array($selected_subject_ids)) {
    $selected_subject_ids = [];
}

// ลบวิชาที่เอาออก (ถ้ามี)
if (!empty($removed_subject_ids)) {
    $ids_to_remove = explode(',', $removed_subject_ids);

    foreach ($ids_to_remove as $subject_id) {
        $stmt = $conn->prepare("DELETE FROM student_scores WHERE student_id = ? AND subject_id = ? AND academic_year = ?");
        $stmt->bind_param("sss", $student_id, $subject_id, $academic_year);
        $stmt->execute();
    }
}

// เพิ่ม/คงไว้เฉพาะวิชาที่ติ๊กอยู่
foreach ($selected_subject_ids as $subject_id) {
    $stmt = $conn->prepare("
        INSERT INTO student_scores (student_id, subject_id, academic_year)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE subject_id = subject_id
    ");
    $stmt->bind_param("sss", $student_id, $subject_id, $academic_year);
    $stmt->execute();
}

// กลับไปหน้ารายงาน
header("Location: report_student.php?student_id=" . urlencode($student_id) . "&academic_year=" . urlencode($academic_year));
exit;

