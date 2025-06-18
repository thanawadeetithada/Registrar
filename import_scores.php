<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$not_found = [];

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $subject_id = $_GET['subject_id'] ?? 0;
    $academic_year = $_GET['academic_year'] ?? 0;

    $insert = $pdo->prepare("
        INSERT INTO student_scores (subject_id, academic_year, semester1_score, semester2_score, grade, student_id)
        VALUES (:subject_id, :academic_year, :semester1_score, :semester2_score, NULL, :student_id)
        ON DUPLICATE KEY UPDATE
            semester1_score = VALUES(semester1_score),
            semester2_score = VALUES(semester2_score)
    ");

    foreach ($rows as $i => $row) {
        if ($i === 0) continue; // ข้ามหัวตาราง

        $student_id = $row[1];
        $s1 = $row[4];
        $s2 = $row[5];

        if (!$student_id) continue;

        // ตรวจสอบว่านักเรียนมีอยู่ในฐานข้อมูลไหม
        $check = $pdo->prepare("SELECT prefix, student_name FROM students WHERE student_id = :student_id AND academic_year = :academic_year");
        $check->execute([
            'student_id' => $student_id,
            'academic_year' => $academic_year
        ]);
        $student = $check->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            $insert->execute([
                'subject_id' => $subject_id,
                'academic_year' => $academic_year,
                'semester1_score' => $s1 ?: 0,
                'semester2_score' => $s2 ?: 0,
                'student_id' => $student_id
            ]);
        } else {
                $not_found[] = "ไม่พบ" . ($student['prefix'] ?? '') . ($student['student_name'] ?? '') . " (รหัสนักเรียน : $student_id)";
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