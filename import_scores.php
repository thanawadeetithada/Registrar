<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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
        if ($i === 0) continue; // ข้ามแถว header

        // ดึงเฉพาะ index ที่ต้องใช้: [1] = student_id, [4] = s1, [5] = s2
        $student_id = $row[1];
        $s1 = $row[4];
        $s2 = $row[5];

        // ตรวจว่ามี student_id หรือไม่ก่อน insert
        if ($student_id) {
            $insert->execute([
                'subject_id' => $subject_id,
                'academic_year' => $academic_year,
                'semester1_score' => $s1 ?: 0,
                'semester2_score' => $s2 ?: 0,
                'student_id' => $student_id
            ]);
        }
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Upload failed']);
}
?>