<?php
header('Content-Type: application/json');
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$not_found = [];

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $subject_id = intval($_GET['subject_id'] ?? 0);
    $academic_year = intval($_GET['academic_year'] ?? 0);

    // เตรียม insert
    $insert = $conn->prepare("
        INSERT INTO student_scores (subject_id, academic_year, semester1_score, semester2_score, grade, student_id)
        VALUES (?, ?, ?, ?, NULL, ?)
        ON DUPLICATE KEY UPDATE
            semester1_score = VALUES(semester1_score),
            semester2_score = VALUES(semester2_score)
    ");

    foreach ($rows as $i => $row) {
        if ($i === 0) continue; // ข้าม header

        $student_id = trim($row[1]);
        $s1 = is_numeric($row[4]) ? floatval($row[4]) : 0;
        $s2 = is_numeric($row[5]) ? floatval($row[5]) : 0;

        if (!$student_id) continue;

        // ตรวจสอบว่านักเรียนมีอยู่หรือไม่
        $check = $conn->prepare("SELECT prefix, student_name FROM students WHERE student_id = ? AND academic_year = ?");
        $check->bind_param("si", $student_id, $academic_year);
        $check->execute();
        $result = $check->get_result();
        $student = $result->fetch_assoc();

        if ($student) {
            $insert->bind_param("iidds", $subject_id, $academic_year, $s1, $s2, $student_id);
            $success = $insert->execute();

            if (!$success) {
                $not_found[] = "❌ บันทึกไม่สำเร็จ ($student_id): " . $insert->error;
            }
        } else {
            $not_found[] = "❌ ไม่พบรหัสนักเรียน: $student_id";
        }
    }

    echo json_encode([
        'success' => true,
        'not_found' => $not_found
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Upload failed'
    ]);
}
?>
