<?php
header("Content-Type: application/json");
require 'db.php'; // เชื่อมต่อฐานข้อมูล

// ดึงวิชาทั้งหมด
$stmt = $pdo->query("SELECT * FROM subjects");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];

foreach ($subjects as $subject) {
    // ดึงช่วงคะแนนจาก grade_ranges
    $stmt2 = $pdo->prepare("SELECT min_score, max_score, grade FROM grade_ranges WHERE subject_id = ?");
    $stmt2->execute([$subject['id']]);
    $grades = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // สร้าง object วิชา พร้อมเกรด
    $result[] = [
    "id" => $subject["id"],
    "subject_name" => $subject["subject_name"],
    "class_level" => $subject["class_level"],
    "academic_year" => $subject["academic_year"], // ✅ เพิ่มบรรทัดนี้
    "grades" => $grades
];

}

echo json_encode($result);