<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['subject'], $data['classLevel'], $data['grades'], $data['subjectID'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);
    exit;
}

$subject = $data['subject'];
$classLevel = $data['classLevel'];
$grades = $data['grades'];
$academicYear = $data['academicYear'] ?? null;
$id = $data['id'] ?? null;
$subjectCode = $data['subjectID'];

try {
    if ($id) {
        // ✅ UPDATE วิชา
        $stmt = $conn->prepare("UPDATE subjects SET subject_id = ?, subject_name = ?, class_level = ?, academic_year = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $subjectCode, $subject, $classLevel, $academicYear, $id);
        $stmt->execute();

        // ลบช่วงคะแนนเก่า
        $stmtDel = $conn->prepare("DELETE FROM grade_ranges WHERE subject_id = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();

        // เพิ่มช่วงคะแนนใหม่
        $stmtAdd = $conn->prepare("INSERT INTO grade_ranges (subject_id, min_score, max_score, grade) VALUES (?, ?, ?, ?)");
        foreach ($grades as $g) {
            $stmtAdd->bind_param("iiid", $id, $g['min'], $g['max'], $g['grade']);
            $stmtAdd->execute();
        }
    } else {
        // ✅ INSERT ใหม่
        $stmt = $conn->prepare("INSERT INTO subjects (subject_id, subject_name, class_level, academic_year) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $subjectCode, $subject, $classLevel, $academicYear);
        $stmt->execute();
        $newId = $stmt->insert_id;

        $stmtAdd = $conn->prepare("INSERT INTO grade_ranges (subject_id, min_score, max_score, grade) VALUES (?, ?, ?, ?)");
        foreach ($grades as $g) {
            $stmtAdd->bind_param("iiid", $newId, $g['min'], $g['max'], $g['grade']);
            $stmtAdd->execute();
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
