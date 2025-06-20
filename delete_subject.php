<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$id = intval($data['id']);

try {
    // ลบจาก student_scores ก่อน (เพราะมี foreign key)
    $stmt0 = $pdo->prepare("DELETE FROM student_scores WHERE subject_id = ?");
    $stmt0->execute([$id]);

    // ลบจาก grade_ranges
    $stmt1 = $pdo->prepare("DELETE FROM grade_ranges WHERE subject_id = ?");
    $stmt1->execute([$id]);

    // ลบจาก subjects
    $stmt2 = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt2->execute([$id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

