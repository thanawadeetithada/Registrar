<?php
header("Content-Type: application/json");
require 'db.php'; // เชื่อมต่อฐานข้อมูล

$result = [];

// ดึงวิชาทั้งหมด
$query = "SELECT * FROM subjects";
$subjectResult = $conn->query($query);

if ($subjectResult) {
    while ($subject = $subjectResult->fetch_assoc()) {
        $grades = [];

        // ดึงช่วงคะแนนจาก grade_ranges
        $stmt2 = $conn->prepare("SELECT min_score, max_score, grade FROM grade_ranges WHERE subject_id = ?");
        $stmt2->bind_param("i", $subject['id']);
        $stmt2->execute();
        $gradeResult = $stmt2->get_result();

        while ($row = $gradeResult->fetch_assoc()) {
            $grades[] = $row;
        }

        $result[] = [
            "id" => $subject["id"],
            "subject_id" => $subject["subject_id"],
            "subject_name" => $subject["subject_name"],
            "class_level" => $subject["class_level"],
            "academic_year" => $subject["academic_year"],
            "grades" => $grades
        ];
    }
}

echo json_encode($result);
