<?php
header('Content-Type: application/json');
require 'db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$id = intval($_GET['id']);

// ดึงข้อมูลวิชา
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$id]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    echo json_encode(['success' => false, 'message' => 'Subject not found']);
    exit;
}

// ดึงช่วงเกรด
$stmt2 = $pdo->prepare("SELECT * FROM grade_ranges WHERE subject_id = ?");
$stmt2->execute([$id]);
$grades = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$subject['grades'] = $grades;

echo json_encode($subject);
