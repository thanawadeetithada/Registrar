<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// สร้าง spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// หัวตาราง
$headers = [
    'ปีการศึกษา', 'ระดับชั้น', 'ห้อง', 'รหัสนักเรียน', 'ชื่อนักเรียน',
    'ชื่อวิชา', 'คะแนนภาค 1', 'คะแนนภาค 2', 'รวมคะแนน', 'เกรด'
];

$sheet->fromArray($headers, NULL, 'A1');

// ดึงข้อมูลจาก student_scores ร่วมกับ students และ subjects
$sql = "
SELECT 
    s.academic_year,
    s.class_level,
    s.classroom,
    s.student_id,
    s.student_name,
    sub.subject_name,
    sc.semester1_score,
    sc.semester2_score,
    sc.total_score,
    sc.grade
FROM student_scores sc
JOIN students s ON s.student_id = sc.student_id
JOIN subjects sub ON sub.id = sc.subject_id
ORDER BY s.academic_year DESC, s.class_level, s.classroom, sub.subject_name
";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// เติมข้อมูลลง Excel
$row = 2;
foreach ($data as $record) {
    $sheet->fromArray(array_values($record), NULL, "A{$row}");
    $row++;
}

// ตั้งชื่อไฟล์
$filename = 'export_scores_' . date('Ymd_His') . '.xlsx';

// ตั้ง header สำหรับดาวน์โหลด
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>