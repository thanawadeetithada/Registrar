<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

require_once 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// หัวตาราง
$headers = [
    'ปีการศึกษา', 'ระดับชั้น', 'ห้อง', 'รหัสนักเรียน', 'ชื่อนักเรียน',
    'ชื่อวิชา', 'คะแนนภาค 1', 'คะแนนภาค 2', 'รวมคะแนน', 'เกรด'
];
$sheet->fromArray($headers, NULL, 'A1');

// SQL query
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

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// เติมข้อมูลลง Excel
$row = 2;
while ($record = $result->fetch_assoc()) {
    $safeRecord = array_map(fn($val) => $val ?? '', array_values($record));
    $sheet->fromArray($safeRecord, NULL, "A$row");
    $row++;
}

// เคลียร์ output buffer ก่อนส่งไฟล์ (สำคัญมาก!)
if (ob_get_length()) {
    ob_end_clean();
}

$filename = 'export_scores_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

try {
    file_put_contents('error_log.txt', "เริ่มบันทึก Excel\n", FILE_APPEND);
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    file_put_contents('error_log.txt', "Export error: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Export failed: " . $e->getMessage());
}
