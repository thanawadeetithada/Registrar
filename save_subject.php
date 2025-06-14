<?php
// save_subject.php
header('Content-Type: application/json');

// เรียกใช้ db.php สำหรับเชื่อมต่อฐานข้อมูล
require_once 'db.php';

// รับข้อมูล JSON ที่ส่งมาจาก JavaScript
$data = json_decode(file_get_contents("php://input"), true);
$subject = $conn->real_escape_string($data['subject']);
$classLevel = $conn->real_escape_string($data['classLevel']);
$grades = $data['grades'];

// บันทึกรายวิชา
$sql = "INSERT INTO subjects (subject_name, class_level) VALUES ('$subject', '$classLevel')";
if ($conn->query($sql)) {
    $subject_id = $conn->insert_id;

    // วนลูปบันทึกช่วงคะแนนแต่ละช่วง
    foreach ($grades as $grade) {
        $min = (float)$grade['min'];
        $max = (float)$grade['max'];
        $g = (float)$grade['grade'];
        $conn->query("INSERT INTO grade_ranges (subject_id, min_score, max_score, grade) 
                      VALUES ($subject_id, $min, $max, $g)");
    }

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "ไม่สามารถบันทึกรายวิชาได้"]);
}

$conn->close();
