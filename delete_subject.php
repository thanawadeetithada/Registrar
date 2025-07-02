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
    // เริ่ม transaction (optional แต่แนะนำ)
    $conn->begin_transaction();

    // ลบจาก student_scores (เพราะมี foreign key)
    $stmt0 = $conn->prepare("DELETE FROM student_scores WHERE subject_id = ?");
    $stmt0->bind_param("i", $id);
    $stmt0->execute();

    // ลบจาก grade_ranges
    $stmt1 = $conn->prepare("DELETE FROM grade_ranges WHERE subject_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // ลบจาก subjects
    $stmt2 = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    // commit การลบทั้งหมด
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback(); // ยกเลิกการลบถ้า error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
