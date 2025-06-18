<?php
require 'vendor/autoload.php'; // ต้องติดตั้ง PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

$academic_year = $_GET['academic_year'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$subject_name = $_GET['subject_name'] ?? '';
$class_level = $_GET['class_level'] ?? '';
$classroom = $_GET['classroom'] ?? '';

// ดึงข้อมูลนักเรียน
$students_query = $pdo->prepare("SELECT * FROM students WHERE class_level = :class_level AND classroom = :classroom AND academic_year = :academic_year");
$students_query->execute([
    'class_level' => $class_level,
    'classroom' => $classroom,
    'academic_year' => $academic_year
]);
$students = $students_query->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลคะแนน
$scores_query = $pdo->prepare("SELECT * FROM student_scores WHERE academic_year = :academic_year AND subject_id = :subject_id");
$scores_query->execute([
    'academic_year' => $academic_year,
    'subject_id' => $subject_id
]);
$scores = $scores_query->fetchAll(PDO::FETCH_ASSOC);

// ดึงช่วงเกรด
$grade_query = $pdo->prepare("SELECT * FROM grade_ranges WHERE subject_id = :subject_id ORDER BY min_score DESC");
$grade_query->execute(['subject_id' => $subject_id]);
$grade_ranges = $grade_query->fetchAll(PDO::FETCH_ASSOC);

// สร้าง Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// หัวตาราง
$sheet->fromArray(['ลำดับ', 'รหัสนักเรียน', 'เลขบัตรประชาชน', 'ชื่อ', 'คะแนนเทอม 1', 'คะแนนเทอม 2', 'รวม', 'เกรด'], NULL, 'A1');

$row = 2;
$count = 1;

foreach ($students as $student) {
    $student_score = array_filter($scores, function($score) use ($student) {
        return $score['student_id'] == $student['student_id'];
    });
    $student_score = reset($student_score);

    $score1 = $student_score['semester1_score'] ?? 0;
    $score2 = $student_score['semester2_score'] ?? 0;
    $total = $score1 + $score2;

    $grade = 'ไม่มีเกรด';
    foreach ($grade_ranges as $range) {
        if ($total >= $range['min_score'] && $total <= $range['max_score']) {
            $grade = $range['grade'];
            break;
        }
    }
    
 $sheet->setCellValue('A' . $row, $count++);
    $sheet->setCellValue('B' . $row, $student['student_id']);
    $sheet->setCellValueExplicit('C' . $row, $student['citizen_id'], DataType::TYPE_STRING); // ✅ ป้องกัน E+12
    $sheet->setCellValue('D' . $row, $student['prefix'] . $student['student_name']);
    $sheet->setCellValue('E' . $row, $score1);
    $sheet->setCellValue('F' . $row, $score2);
    $sheet->setCellValue('G' . $row, $total);
    $sheet->setCellValue('H' . $row, $grade);

    $row++;
}

// ตั้งชื่อไฟล์ให้มีชื่อวิชาและปีการศึกษา
$filename = "คะแนน_" . $subject_name . "_ปี_" . $academic_year . ".xlsx";

// บันทึกเป็นไฟล์ Excel และส่งให้ดาวน์โหลด
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>