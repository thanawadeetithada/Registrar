<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

header('Content-Type: application/json');

$response = [
    'success' => false,
    'duplicate_citizens' => [],
    'duplicate_students' => [],
    'invalid_subject_ids' => []
];

function normalize_date($input_date) {
    if ($input_date instanceof \DateTime) {
        return $input_date->format('Y-m-d');
    }

    if (is_numeric($input_date)) {
        try {
            $dateTime = Date::excelToDateTimeObject($input_date);
            return $dateTime->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    $input_date = trim((string) $input_date);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input_date)) {
        return $input_date;
    }

    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $input_date)) {
        $parts = explode('-', $input_date);
        return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
    }

    if (preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{2}$/', $input_date)) {
        [$day, $month, $year] = preg_split('/[-\/]/', $input_date);
        $year = (intval($year) < 50) ? "20$year" : "19$year";
        return "$year-$month-$day";
    }

    return null;
}

try {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("ไม่พบไฟล์หรือเกิดข้อผิดพลาดในการอัปโหลด");
    }

    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, false, false, false);


    for ($i = 1; $i < count($rows); $i++) {
        $row = array_map(function ($v) {
            return trim((string) ($v ?? ''));
        }, array_pad($rows[$i], 9, ''));

        [
            $academic_year,
            $class_level,
            $classroom,
            $citizen_id,
            $student_id,
            $prefix,
            $student_name,
            $birth_date_raw,
            $subject_ids_string
        ] = $row;

        $birth_date = normalize_date($birth_date_raw);

        // ตรวจสอบ citizen_id ซ้ำ
        $stmtCitizen = $conn->prepare("SELECT student_name FROM students WHERE academic_year = ? AND citizen_id = ?");
        $stmtCitizen->bind_param("is", $academic_year, $citizen_id);
        $stmtCitizen->execute();
        $resultCitizen = $stmtCitizen->get_result();
        if ($resultCitizen->num_rows > 0) {
            $existing = $resultCitizen->fetch_assoc();
            $response['duplicate_citizens'][] = $existing;
            continue;
        }

        // ตรวจสอบ student_id ซ้ำ
        $stmtStudent = $conn->prepare("SELECT student_name FROM students WHERE academic_year = ? AND student_id = ?");
        $stmtStudent->bind_param("is", $academic_year, $student_id);
        $stmtStudent->execute();
        $resultStudent = $stmtStudent->get_result();
        if ($resultStudent->num_rows > 0) {
            $existing = $resultStudent->fetch_assoc();
            $response['duplicate_students'][] = $existing;
            continue;
        }

        // บันทึกนักเรียน
        $stmtInsert = $conn->prepare("INSERT INTO students (academic_year, class_level, classroom, citizen_id, student_id, prefix, student_name, birth_date)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("isssssss", $academic_year, $class_level, $classroom, $citizen_id, $student_id, $prefix, $student_name, $birth_date);
        $stmtInsert->execute();

        // บันทึกรายวิชา
        if (!empty($subject_ids_string)) {
            $subject_ids = explode(",", $subject_ids_string);

            foreach ($subject_ids as $subject_id) {
                $subject_id = trim($subject_id);

                $stmtSub = $conn->prepare("SELECT id FROM subjects WHERE subject_id = ? AND academic_year = ?");
                $stmtSub->bind_param("si", $subject_id, $academic_year);
                $stmtSub->execute();
                $resultSub = $stmtSub->get_result();

                if ($rowSub = $resultSub->fetch_assoc()) {
                    $real_subject_id = $rowSub['id'];

                    $stmtScore = $conn->prepare("INSERT INTO student_scores (subject_id, academic_year, student_id)
                                                 VALUES (?, ?, ?)");
                    $stmtScore->bind_param("iis", $real_subject_id, $academic_year, $student_id);
                    $stmtScore->execute();
                } else {
                    $response['invalid_subject_ids'][] = $subject_id;
                }
            }
        }
    }

    if (
        empty($response['duplicate_citizens']) &&
        empty($response['duplicate_students']) &&
        empty($response['invalid_subject_ids'])
    ) {
        $response['success'] = true;
    }

    ob_clean();
    echo json_encode($response);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}