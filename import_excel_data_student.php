<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

$response = [
    'success' => false,
    'duplicate_citizens' => [],
    'duplicate_students' => [],
    'invalid_subject_ids' => [] // ✅ เพิ่มจุดนี้
];

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];

// ปรับบรรทัดนี้ เพื่อรองรับกรณีที่ไม่มีคอลัมน์ I (ให้ไม่ error)
        [$academic_year, $class_level, $classroom, $citizen_id, $student_id, $prefix, $student_name, $birth_date, $subject_ids_string] = array_pad($row, 9, '');

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

        // บันทึกรายวิชาที่เลือก (ถ้ามี)
        if (!empty($subject_ids_string)) {
            $subject_ids = explode(",", $subject_ids_string);

            foreach ($subject_ids as $subject_id) {
                $subject_id = trim($subject_id);

                // ตรวจสอบว่า subject_id นี้มีอยู่จริงไหม
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
                    // ✅ บันทึก subject_id ที่ไม่พบ
                    $response['invalid_subject_ids'][] = $subject_id;
                }
            }
        }
    }

    // สำเร็จถ้าไม่มีซ้ำ และไม่มีรหัสวิชาผิด
    if (
        empty($response['duplicate_citizens']) &&
        empty($response['duplicate_students']) &&
        empty($response['invalid_subject_ids'])
    ) {
        $response['success'] = true;
    }

    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปโหลดไฟล์ได้']);
}