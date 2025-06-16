<?php
require 'db.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$citizenId = $data['citizen_id'];
$classLevel = $data['class_level'];
$academicYear = $data['academic_year'];
$semester = 1; // กำหนดให้เป็นภาคเรียนที่ 1 เพราะต้องการดึงข้อมูลจากภาคเรียนที่ 1

// ดึงข้อมูลนักเรียนในภาคเรียนที่ 1
$stmt = $pdo->prepare("SELECT * FROM students 
                       WHERE citizen_id = ? AND class_level = ? AND academic_year = ? AND semester = ?
                       ORDER BY id DESC LIMIT 1");
$stmt->execute([$citizenId, $classLevel, $academicYear, $semester]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo json_encode(["success" => false, "message" => "ไม่พบนักเรียน"]);
    exit;
}

// ดึงคะแนนจากภาคเรียนที่ 1
$stmtScores = $pdo->prepare("SELECT subject_id, semester1_score FROM student_scores WHERE student_id = ?");
$stmtScores->execute([$student['id']]);
$scores = [];
while ($row = $stmtScores->fetch(PDO::FETCH_ASSOC)) {
    $scores[$row['subject_id']] = $row['semester1_score'];
}

echo json_encode([
    "success" => true,
    "student" => [
        "student_id" => $student['student_id'],
        "prefix" => $student['prefix'],
        "student_name" => $student['student_name'],
        "birth_date" => $student['birth_date']
    ],
    "scores" => $scores
]);
