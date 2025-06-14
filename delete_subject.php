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
    // ลบเกรดก่อน (ถ้ามี foreign key)
    $stmt1 = $pdo->prepare("DELETE FROM grade_ranges WHERE subject_id = ?");
    $stmt1->execute([$id]);

    // ลบวิชา
    $stmt2 = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt2->execute([$id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
