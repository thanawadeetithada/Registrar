<?php
require_once 'db.php';

header('Content-Type: application/json');

$academic_year = $_GET['academic_year'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$class_level = $_GET['class_level'] ?? '';
$classroom = $_GET['classroom'] ?? '';
$subject_name = $_GET['subject_name'] ?? '';

// รับค่าคะแนนจาก POST
$semester1_scores = $_POST['semester1_score'] ?? [];
$semester2_scores = $_POST['semester2_score'] ?? [];

// ดึงเกณฑ์การให้เกรดจากฐานข้อมูล
$grade_query = $conn->prepare("SELECT * FROM grade_ranges WHERE subject_id = ? ORDER BY min_score DESC");
$grade_query->bind_param("i", $subject_id);
$grade_query->execute();
$grade_result = $grade_query->get_result();
$grade_ranges = $grade_result->fetch_all(MYSQLI_ASSOC);

$success_count = 0;

foreach ($semester1_scores as $student_id => $semester1_score) {
    $semester1_score = floatval($semester1_score);
    $semester2_score = floatval($semester2_scores[$student_id] ?? 0);

    $total_score = $semester1_score + $semester2_score;
    $grade = 'F';

    foreach ($grade_ranges as $range) {
        if ($total_score >= $range['min_score'] && $total_score <= $range['max_score']) {
            $grade = $range['grade'];
            break;
        }
    }

    // ตรวจสอบว่ามีข้อมูลเดิมอยู่หรือไม่
    $check = $conn->prepare("SELECT 1 FROM student_scores WHERE student_id = ? AND subject_id = ? AND academic_year = ?");
    $check->bind_param("sii", $student_id, $subject_id, $academic_year);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // อัปเดตข้อมูล
        $update = $conn->prepare("UPDATE student_scores SET semester1_score = ?, semester2_score = ?, grade = ? WHERE student_id = ? AND subject_id = ? AND academic_year = ?");
        $update->bind_param("ddssii", $semester1_score, $semester2_score, $grade, $student_id, $subject_id, $academic_year);
        if ($update->execute()) {
            $success_count++;
        }
    } else {
        // แทรกข้อมูลใหม่
        $insert = $conn->prepare("INSERT INTO student_scores (subject_id, academic_year, semester1_score, semester2_score, grade, student_id) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("iiddss", $subject_id, $academic_year, $semester1_score, $semester2_score, $grade, $student_id);
        if ($insert->execute()) {
            $success_count++;
        }
    }
}

if ($success_count > 0) {
    echo json_encode(['success' => true, 'message' => "บันทึกข้อมูล $success_count รายการ", 'redirect' => 'record_score.php']);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลถูกบันทึก']);
}
exit;

?>