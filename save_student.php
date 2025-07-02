<?php
require_once 'db.php';
header('Content-Type: application/json');
ini_set('display_errors', 1); // สำหรับ debug
error_reporting(E_ALL);       // สำหรับ debug

$academic_year = $_POST['academic_year'];
$class_level = $_POST['class_level'];
$classroom = $_POST['classroom'];
$citizen_id = $_POST['citizen_id'];
$student_id = $_POST['student_id'];
$prefix = $_POST['prefix'];
$student_name = $_POST['student_name'];
$birth_date = $_POST['birth_date'];
$selected_subjects = $_POST['subjects'] ?? [];

// ตรวจสอบ citizen_id ซ้ำ
$sqlCheckCitizen = "SELECT * FROM students WHERE academic_year = ? AND citizen_id = ?";
$stmtCheckCitizen = $conn->prepare($sqlCheckCitizen);
$stmtCheckCitizen->bind_param("is", $academic_year, $citizen_id);
$stmtCheckCitizen->execute();
$resultCheckCitizen = $stmtCheckCitizen->get_result();
$existingCitizen = $resultCheckCitizen->fetch_assoc(); // ✅ ขาดบรรทัดนี้ในของเดิม

if ($existingCitizen) {
    echo json_encode(['success' => false, 'message' => 'เลขบัตรประชาชนซ้ำในปีการศึกษานี้']);
    exit;
}

// ตรวจสอบ student_id ซ้ำ
$sqlCheckStudent = "SELECT * FROM students WHERE academic_year = ? AND student_id = ?";
$stmtCheckStudent = $conn->prepare($sqlCheckStudent);
$stmtCheckStudent->bind_param("is", $academic_year, $student_id);
$stmtCheckStudent->execute();
$resultCheckStudent = $stmtCheckStudent->get_result();

if ($resultCheckStudent->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'รหัสประจำตัวนักเรียนซ้ำในปีการศึกษานี้']);
    exit;
}

// บันทึก
$sqlInsert = "INSERT INTO students (academic_year, class_level, classroom, citizen_id, student_id, prefix, student_name, birth_date)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("isssssss", $academic_year, $class_level, $classroom, $citizen_id, $student_id, $prefix, $student_name, $birth_date);

if ($stmtInsert->execute()) {
    if (!empty($selected_subjects)) {
        $insertScore = $conn->prepare("INSERT INTO student_scores (subject_id, academic_year, student_id) VALUES (?, ?, ?)");
        foreach ($selected_subjects as $subjectId) {
            $insertScore->bind_param("iis", $subjectId, $academic_year, $student_id);
            $insertScore->execute();
        }
    }

    echo json_encode(['success' => true]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
    exit;
}
?>
