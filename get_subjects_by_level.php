<?php
require 'db.php';

header('Content-Type: application/json');

$class_level = $_GET['class_level'] ?? '';

if (!$class_level) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, subject_name, subject_id FROM subjects WHERE class_level = ? ORDER BY subject_name ASC");
$stmt->bind_param("s", $class_level);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

echo json_encode($subjects);
