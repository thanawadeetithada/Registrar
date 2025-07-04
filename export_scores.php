<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$academic_year = isset($_GET['academic_year']) ? (int) $_GET['academic_year'] : 0;
$subject_id = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;
$subject_name = $_GET['subject_name'] ?? '';
$class_level = $_GET['class_level'] ?? '';
$classroom = $_GET['classroom'] ?? '';

// ดึงข้อมูลนักเรียน
while (ob_get_level()) ob_end_clean();
$students_stmt = $conn->prepare("
    SELECT s.*
    FROM students s
    INNER JOIN student_scores sc 
        ON s.student_id = sc.student_id
    WHERE s.class_level = ? 
      AND s.classroom = ? 
      AND s.academic_year = ? 
      AND sc.subject_id = ?
");
$students_stmt->bind_param("ssii", $class_level, $classroom, $academic_year, $subject_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();
$students = $students_result->fetch_all(MYSQLI_ASSOC);

// ดึงคะแนนนักเรียน
$scores_stmt = $conn->prepare("SELECT * FROM student_scores WHERE academic_year = ? AND subject_id = ?");
$scores_stmt->bind_param("ii", $academic_year, $subject_id);
$scores_stmt->execute();
$scores_result = $scores_stmt->get_result();
$scores = $scores_result->fetch_all(MYSQLI_ASSOC);

// ดึงช่วงเกรด
$grade_stmt = $conn->prepare("SELECT * FROM grade_ranges WHERE subject_id = ? ORDER BY min_score DESC");
$grade_stmt->bind_param("i", $subject_id);
$grade_stmt->execute();
$grade_result = $grade_stmt->get_result();
$grade_ranges = $grade_result->fetch_all(MYSQLI_ASSOC);

// เริ่มสร้าง Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->fromArray(['ลำดับ', 'รหัสนักเรียน', 'เลขบัตรประชาชน', 'ชื่อ', 'คะแนนเทอม 1', 'คะแนนเทอม 2', 'รวม', 'เกรด'], NULL, 'A1');

$row = 2;
$count = 1;

foreach ($students as $student) {
    $student_score = array_filter($scores, function ($score) use ($student) {
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
    $sheet->setCellValueExplicit('C' . $row, $student['citizen_id'], DataType::TYPE_STRING);
    $sheet->setCellValue('D' . $row, $student['prefix'] . $student['student_name']);
    $sheet->setCellValue('E' . $row, $score1);
    $sheet->setCellValue('F' . $row, $score2);
    $sheet->setCellValue('G' . $row, $total);
    $sheet->setCellValue('H' . $row, $grade);

    $row++;
}

$safe_name = "คะแนน_" . $subject_id . "_ปี_" . $academic_year . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$safe_name\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
