<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$subject_id = $_GET['subject_id'] ?? '';
$academic_year = $_GET['academic_year'] ?? '';

$stmt = $conn->prepare("
    SELECT 
        ss.student_id,
        ss.semester1_score,
        ss.semester2_score,
        ss.total_score,
        ss.grade,
        s.citizen_id,
        s.prefix,
        s.student_name,
        s.classroom
    FROM student_scores AS ss
    INNER JOIN students AS s 
        ON ss.student_id = s.student_id
        AND ss.academic_year = s.academic_year
    WHERE ss.subject_id = ? AND ss.academic_year = ?
");
$stmt->bind_param("ii", $subject_id, $academic_year);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

// สร้างไฟล์ Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// หัวตาราง
$sheet->fromArray([
    'ลำดับ',
    'รหัสนักเรียน',
    'เลขบัตรประชาชน',
    'ชื่อ-นามสกุล',
    'ห้อง',
    'คะแนนภาคเรียนที่ 1',
    'คะแนนภาคเรียนที่ 2',
    'คะแนนรวม',
    'เกรด'
], null, 'A1');

// เติมข้อมูลนักเรียน
$rowNum = 2;
foreach ($students as $index => $s) {
    $sheet->setCellValue("A$rowNum", $index + 1);
    $sheet->setCellValue("B$rowNum", $s['student_id']);
    $sheet->setCellValue("C$rowNum", $s['citizen_id']);
    $sheet->setCellValue("D$rowNum", ($s['prefix'] ?? '') . ($s['student_name'] ?? ''));
    $sheet->setCellValue("E$rowNum", $s['classroom']);
    $sheet->setCellValue("F$rowNum", $s['semester1_score']);
    $sheet->setCellValue("G$rowNum", $s['semester2_score']);
    $sheet->setCellValue("H$rowNum", $s['total_score']);
    $sheet->setCellValue("I$rowNum", $s['grade']);
    $rowNum++;
}

$subject_name = $_GET['subject_name'] ?? 'ไม่ทราบชื่อวิชา';

// ลบอักขระพิเศษและเว้นวรรค → _
$sanitized_subject_name = preg_replace('/[^\wก-๙]/u', '', $subject_name);

$today = date('dmY');
$filename = "คะแนน_" . $sanitized_subject_name . "_ปี" . $academic_year . "_" . $today . ".xlsx";


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
