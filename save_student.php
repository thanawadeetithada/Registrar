<?php
require_once 'db.php';

// Get POST data from AJAX request
$academic_year = $_POST['academic_year'];
$class_level = $_POST['class_level'];
$classroom = $_POST['classroom'];
$citizen_id = $_POST['citizen_id'];
$student_id = $_POST['student_id'];
$prefix = $_POST['prefix'];
$student_name = $_POST['student_name'];
$birth_date = $_POST['birth_date'];

// Check if the academic_year already exists with the same citizen_id
$stmtCheckCitizen = $pdo->prepare("SELECT * FROM students WHERE academic_year = :academic_year AND citizen_id = :citizen_id");
$stmtCheckCitizen->execute(['academic_year' => $academic_year, 'citizen_id' => $citizen_id]);
$existingCitizen = $stmtCheckCitizen->fetch(PDO::FETCH_ASSOC);

// Check if the academic_year already exists with the same student_id
$stmtCheckStudent = $pdo->prepare("SELECT * FROM students WHERE academic_year = :academic_year AND student_id = :student_id");
$stmtCheckStudent->execute(['academic_year' => $academic_year, 'student_id' => $student_id]);
$existingStudent = $stmtCheckStudent->fetch(PDO::FETCH_ASSOC);

// If citizen_id or student_id already exists in the same academic year, return an error
if ($existingCitizen) {
    echo json_encode(['success' => false, 'message' => 'เลขบัตรประชาชนซ้ำในปีการศึกษานี้']);
    exit; // Stop the script
}

if ($existingStudent) {
    echo json_encode(['success' => false, 'message' => 'รหัสประจำตัวนักเรียนซ้ำในปีการศึกษานี้']);
    exit; // Stop the script
}

// If the data does not exist, proceed with insert or update
$stmtInsert = $pdo->prepare("
    INSERT INTO students (academic_year, class_level, classroom, citizen_id, student_id, prefix, student_name, birth_date)
    VALUES (:academic_year, :class_level, :classroom, :citizen_id, :student_id, :prefix, :student_name, :birth_date)
");
$stmtInsert->execute([
    'academic_year' => $academic_year,
    'class_level' => $class_level,
    'classroom' => $classroom,
    'citizen_id' => $citizen_id,
    'student_id' => $student_id,
    'prefix' => $prefix,
    'student_name' => $student_name,
    'birth_date' => $birth_date
]);
echo json_encode(['success' => true]);

?>
