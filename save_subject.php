<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['subject'], $data['classLevel'], $data['grades'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);
    exit;
}

$subject = $data['subject'];
$classLevel = $data['classLevel'];
$grades = $data['grades'];
$id = $data['id'] ?? null;

try {
    if ($id) {
        // ✅ UPDATE วิชา
        $stmt = $pdo->prepare("UPDATE subjects SET subject_name = ?, class_level = ? WHERE id = ?");
        $stmt->execute([$subject, $classLevel, $id]);

        // ลบช่วงคะแนนเก่า
        $stmtDel = $pdo->prepare("DELETE FROM grade_ranges WHERE subject_id = ?");
        $stmtDel->execute([$id]);

        // เพิ่มช่วงคะแนนใหม่
        $stmtAdd = $pdo->prepare("INSERT INTO grade_ranges (subject_id, min_score, max_score, grade) VALUES (?, ?, ?, ?)");
        foreach ($grades as $g) {
            $stmtAdd->execute([$id, $g['min'], $g['max'], $g['grade']]);
        }
    } else {
        // ✅ INSERT ใหม่
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, class_level) VALUES (?, ?)");
        $stmt->execute([$subject, $classLevel]);
        $subjectId = $pdo->lastInsertId();

        $stmtAdd = $pdo->prepare("INSERT INTO grade_ranges (subject_id, min_score, max_score, grade) VALUES (?, ?, ?, ?)");
        foreach ($grades as $g) {
            $stmtAdd->execute([$subjectId, $g['min'], $g['max'], $g['grade']]);
        }
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
