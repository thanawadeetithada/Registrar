<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

try {
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("ไฟล์มีปัญหาในการอัปโหลด");
    }

    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    unset($rows[0]); // ข้ามหัวตาราง

    $inserted = 0;

    foreach ($rows as $row) {
        if (count($row) < 12) continue; // ตรวจสอบว่าข้อมูลครบ

        [$no, $academic_year, $class_level, $classroom, $citizen_id, $student_id,
         $prefix, $student_name, $birth_date, $subject_name, $s1_score, $s2_score] = $row;

        if (!$subject_name || !$class_level) continue;

        // ตรวจว่านักเรียนมีอยู่หรือยัง
        $checkStudent = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
        $checkStudent->execute([$student_id]);
        if (!$checkStudent->fetch()) {
            $insertStudent = $pdo->prepare("INSERT INTO students 
                (academic_year, class_level, classroom, citizen_id, student_id, prefix, student_name, birth_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insertStudent->execute([
                $academic_year, $class_level, $classroom, $citizen_id, $student_id,
                $prefix, $student_name, date('Y-m-d', strtotime($birth_date))
            ]);
        }

        // ตรวจสอบวิชา
        $checkSubject = $pdo->prepare("SELECT id FROM subjects WHERE subject_name = ? AND class_level = ?");
        $checkSubject->execute([$subject_name, $class_level]);
        $subject = $checkSubject->fetch();

        if (!$subject) {
            $insertSubject = $pdo->prepare("INSERT INTO subjects (subject_name, class_level) VALUES (?, ?)");
            $insertSubject->execute([$subject_name, $class_level]);
            $subject_id = $pdo->lastInsertId();
        } else {
            $subject_id = $subject['id'];
        }

        // เพิ่มคะแนน
        $insertScore = $pdo->prepare("
            INSERT INTO student_scores (subject_id, academic_year, semester1_score, semester2_score, grade, student_id)
            VALUES (?, ?, ?, ?, NULL, ?)
            ON DUPLICATE KEY UPDATE 
                semester1_score = VALUES(semester1_score), 
                semester2_score = VALUES(semester2_score)
        ");
        $insertScore->execute([$subject_id, $academic_year, $s1_score, $s2_score, $student_id]);

        $inserted++;
    }

    echo json_encode(["success" => true, "inserted" => $inserted]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

?>
