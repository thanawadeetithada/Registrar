<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// เปิด error ตอนพัฒนา
ini_set('display_errors', 1);
error_reporting(E_ALL);

$subject_id = $_GET['subject_id'] ?? null;
$academic_year = $_GET['academic_year'] ?? null;

if (!$subject_id || !$academic_year) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$not_found = [];

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($tmp);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // ข้าม header แถวแรก
    foreach ($rows as $i => $row) {
        if ($i === 0) continue;

        $student_id = trim($row[0] ?? '');
        $score1 = trim($row[1] ?? '');
        $score2 = trim($row[2] ?? '');

        // ตรวจสอบว่านักเรียนมีจริง
        $check = $conn->prepare("SELECT id FROM students WHERE student_id = ? LIMIT 1");
        $check->bind_param("s", $student_id);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows === 0) {
            $not_found[] = $student_id;
            continue;
        }

        // บันทึกคะแนน
        $insert = $conn->prepare("
            INSERT INTO student_scores (subject_id, academic_year, semester1_score, semester2_score, student_id)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE semester1_score = VALUES(semester1_score), semester2_score = VALUES(semester2_score)
        ");
        $insert->bind_param("iidds", $subject_id, $academic_year, $score1, $score2, $student_id);
        $insert->execute();
    }

    echo json_encode(['success' => true, 'not_found' => $not_found]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Upload failed', 'detail' => $_FILES['file']['error']]);
}
