<?php
header('Content-Type: application/json');
require 'db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$id = intval($_GET['id']);

// ดึงข้อมูลวิชา
$stmt = $conn->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    echo json_encode(['success' => false, 'message' => 'Subject not found']);
    exit;
}

// ดึงช่วงเกรด
$stmt2 = $conn->prepare("SELECT * FROM grade_ranges WHERE subject_id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$grades = [];
while ($row = $result2->fetch_assoc()) {
    $grades[] = $row;
}

$subject['grades'] = $grades;

echo json_encode($subject);
