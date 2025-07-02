<?php
ob_start();
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

try {
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("ไฟล์มีปัญหาในการอัปโหลด: " . $_FILES['file']['error']);
    }

    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    unset($rows[0]); // ข้ามหัวตาราง

    $inserted = 0;
    $subjectErrors = [];

    foreach ($rows as $row) {
        if (count($row) < 11) continue;

        [$academic_year, $class_level, $classroom, $citizen_id, $student_id,
         $prefix, $student_name, $birth_date, $subject_id_str, $s1_score, $s2_score] = $row;

        if (!$subject_id_str || !$class_level) continue;

        // ตรวจว่านักเรียนมีอยู่หรือยัง
        $checkStudent = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
        $checkStudent->bind_param("s", $student_id);
        $checkStudent->execute();
        $checkStudentResult = $checkStudent->get_result();
        if ($checkStudentResult->num_rows === 0) {
            $insertStudent = $conn->prepare("INSERT INTO students 
                (academic_year, class_level, classroom, citizen_id, student_id, prefix, student_name, birth_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insertStudent->bind_param("ssssssss", $academic_year, $class_level, $classroom, $citizen_id, $student_id,
                                       $prefix, $student_name, date('Y-m-d', strtotime($birth_date)));
            if (!$insertStudent->execute()) {
                throw new Exception("ไม่สามารถบันทึกข้อมูลนักเรียน: " . $insertStudent->error);
            }
        }

        // ตรวจสอบวิชา (จาก subject_id)
        $checkSubject = $conn->prepare("SELECT id FROM subjects WHERE subject_id = ? AND class_level = ? AND academic_year = ?");
        $checkSubject->bind_param("sss", $subject_id_str, $class_level, $academic_year);
        $checkSubject->execute();
        $checkSubjectResult = $checkSubject->get_result();

        if ($checkSubjectResult->num_rows === 0) {
            $subjectErrors[] = [
                'student_name' => $student_name,
                'subject_id' => $subject_id_str
            ];
            continue;
        }

        $subject = $checkSubjectResult->fetch_assoc();
        $subject_id = $subject['id'];

        // เพิ่มคะแนน
        $insertScore = $conn->prepare("
            INSERT INTO student_scores (subject_id, academic_year, semester1_score, semester2_score, grade, student_id)
            VALUES (?, ?, ?, ?, NULL, ?)
            ON DUPLICATE KEY UPDATE 
                semester1_score = VALUES(semester1_score), 
                semester2_score = VALUES(semester2_score)
        ");
        $insertScore->bind_param("isdds", $subject_id, $academic_year, $s1_score, $s2_score, $student_id);
        if (!$insertScore->execute()) {
            throw new Exception("ไม่สามารถบันทึกข้อมูลคะแนน: " . $insertScore->error);
        }

        $inserted++;
    }

    ob_end_clean();

    if (!empty($subjectErrors)) {
        echo json_encode([
            "success" => false,
            "message" => "รหัสวิชาไม่พบในระบบ",
            "invalid_subjects" => $subjectErrors
        ]);
        exit;
    }

    echo json_encode(["success" => true, "inserted" => $inserted]);

} catch (Exception $e) {
    ob_end_clean(); 
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}
?>
