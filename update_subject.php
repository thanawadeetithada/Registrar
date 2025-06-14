<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$name = $data['subject'];
$classLevel = $data['classLevel'];
$grades = $data['grades'];

$conn->begin_transaction();

// อัปเดตชื่อวิชาและระดับชั้น
$conn->query("UPDATE subjects SET subject_name = '$name', class_level = '$classLevel' WHERE id = $id");

// ลบช่วงเกรดเก่า
$conn->query("DELETE FROM grade_ranges WHERE subject_id = $id");

// เพิ่มช่วงเกรดใหม่
$stmt = $conn->prepare("INSERT INTO grade_ranges (subject_id, min_score, max_score, grade) VALUES (?, ?, ?, ?)");
foreach ($grades as $g) {
    $stmt->bind_param("iidd", $id, $g['min'], $g['max'], $g['grade']);
    $stmt->execute();
}

$conn->commit();

echo json_encode(["success" => true]);
