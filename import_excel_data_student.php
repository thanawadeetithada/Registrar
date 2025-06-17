<?php
require 'vendor/autoload.php';  // โหลด PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['file']['tmp_name'];

    // โหลดไฟล์ Excel
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();

    // เชื่อมต่อฐานข้อมูล
    require_once 'db.php';

    $response = [];
    $duplicateCitizens = [];  // เก็บข้อมูลของนักเรียนที่เลขบัตรประชาชนซ้ำ
    $duplicateStudents = [];  // เก็บข้อมูลของนักเรียนที่รหัสนักเรียนซ้ำ
    $errorFound = false;

    foreach ($sheet->getRowIterator(2) as $row) {  // เริ่มจากแถวที่ 2 (ข้ามหัวข้อ)
        $cells = $row->getCellIterator();
        $cells->setIterateOnlyExistingCells(false);

        $data = [];
        foreach ($cells as $cell) {
            $data[] = $cell->getValue();
        }

        // สมมติว่าไฟล์ Excel มีข้อมูลตามลำดับนี้
        list($academic_year, $class_level, $classroom, $citizen_id, $student_id, $prefix, $student_name, $birth_date) = $data;

        // ตรวจสอบว่าเลขบัตรประชาชนซ้ำหรือไม่
        $checkCitizenIdQuery = $pdo->prepare("SELECT * FROM students WHERE citizen_id = :citizen_id AND academic_year = :academic_year");
        $checkCitizenIdQuery->execute(['citizen_id' => $citizen_id, 'academic_year' => $academic_year]);

        if ($checkCitizenIdQuery->rowCount() > 0) {
            // หากพบเลขบัตรประชาชนซ้ำ
            $duplicateCitizens[] = [
                'student_name' => $student_name,
                'row' => $row->getRowIndex()  // เก็บหมายเลขแถวที่พบข้อผิดพลาด
            ];
            $errorFound = true;
            continue;  // ข้ามการบันทึกข้อมูลของนักเรียนที่เลขบัตรประชาชนซ้ำ
        }

        // ตรวจสอบว่ารหัสนักเรียนซ้ำหรือไม่
        $checkStudentIdQuery = $pdo->prepare("SELECT * FROM students WHERE student_id = :student_id AND academic_year = :academic_year");
        $checkStudentIdQuery->execute(['student_id' => $student_id, 'academic_year' => $academic_year]);

        if ($checkStudentIdQuery->rowCount() > 0) {
            // หากพบรหัสนักเรียนซ้ำ
            $duplicateStudents[] = [
                'student_name' => $student_name,
                'row' => $row->getRowIndex()  // เก็บหมายเลขแถวที่พบข้อผิดพลาด
            ];
            $errorFound = true;
            continue;  // ข้ามการบันทึกข้อมูลของนักเรียนที่รหัสนักเรียนซ้ำ
        }

        // บันทึกข้อมูลนักเรียนลงฐานข้อมูล
        $insertQuery = $pdo->prepare("INSERT INTO students (academic_year, class_level, classroom, citizen_id, student_id, prefix, student_name, birth_date) 
                                      VALUES (:academic_year, :class_level, :classroom, :citizen_id, :student_id, :prefix, :student_name, :birth_date)");

        $insertQuery->execute([
            'academic_year' => $academic_year,
            'class_level' => $class_level,
            'classroom' => $classroom,
            'citizen_id' => $citizen_id,
            'student_id' => $student_id,
            'prefix' => $prefix,
            'student_name' => $student_name,
            'birth_date' => $birth_date
        ]);
    }

    // ส่งผลลัพธ์กลับไปยัง JavaScript
    if ($errorFound) {
        // ส่งข้อมูลที่ซ้ำทั้งหมด (ทั้งเลขบัตรประชาชนและรหัสนักเรียน)
        echo json_encode([
            'success' => false,
            'duplicate_citizens' => $duplicateCitizens,
            'duplicate_students' => $duplicateStudents
        ]);
    } else {
        echo json_encode(['success' => true]);  // ทุกอย่างสำเร็จ
    }
} else {
    echo json_encode(['error' => 'file_upload_failed']);
}
?>
