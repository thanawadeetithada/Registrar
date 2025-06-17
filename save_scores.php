<?php
require_once 'db.php';

$academic_year = $_GET['academic_year'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$class_level = $_GET['class_level'] ?? '';
$classroom = $_GET['classroom'] ?? '';
$subject_name = $_GET['subject_name'] ?? '';

// รับค่าคะแนนจาก POST
$semester1_scores = $_POST['semester1_score'] ?? [];
$semester2_scores = $_POST['semester2_score'] ?? [];
$grade_query = $pdo->prepare("SELECT * FROM grade_ranges WHERE subject_id = :subject_id ORDER BY min_score DESC");
$grade_query->execute(['subject_id' => $subject_id]);
$grade_ranges = $grade_query->fetchAll(PDO::FETCH_ASSOC);

// รับค่าคะแนนจาก POST
foreach ($semester1_scores as $student_id => $semester1_score) {
    $semester1_score = floatval($semester1_score);
    $semester2_score = floatval($semester2_scores[$student_id] ?? 0);

    // คำนวณคะแนนรวม
    $total_score = $semester1_score + $semester2_score;

    // คำนวณเกรดจากฐานข้อมูล
    $grade = 'F'; // กำหนดเกรดเริ่มต้นเป็น F
    foreach ($grade_ranges as $range) {
        if ($total_score >= $range['min_score'] && $total_score <= $range['max_score']) {
            $grade = $range['grade'];
            break;
        }
    }

    // ตรวจสอบว่ามีคะแนนเก่าแล้วหรือไม่
    $check = $pdo->prepare("SELECT * FROM student_scores WHERE student_id = :student_id AND subject_id = :subject_id AND academic_year = :academic_year");
    $check->execute([
        'student_id' => $student_id,
        'subject_id' => $subject_id,
        'academic_year' => $academic_year
    ]);
    $existing = $check->fetch();

    if ($existing) {
        // อัปเดตข้อมูล
        $update = $pdo->prepare("UPDATE student_scores SET semester1_score = :s1, semester2_score = :s2, total_score = :total, grade = :grade WHERE student_id = :student_id AND subject_id = :subject_id AND academic_year = :academic_year");
        $update->execute([
            's1' => $semester1_score,
            's2' => $semester2_score,
            'total' => $total_score,
            'grade' => $grade,
            'student_id' => $student_id,
            'subject_id' => $subject_id,
            'academic_year' => $academic_year
        ]);
    } else {
        // แทรกข้อมูลใหม่
        $insert = $pdo->prepare("INSERT INTO student_scores (subject_id, academic_year, semester1_score, semester2_score, total_score, grade, student_id) VALUES (:subject_id, :academic_year, :s1, :s2, :total, :grade, :student_id)");
        $insert->execute([
            'subject_id' => $subject_id,
            'academic_year' => $academic_year,
            's1' => $semester1_score,
            's2' => $semester2_score,
            'total' => $total_score,
            'grade' => $grade,
            'student_id' => $student_id
        ]);
    }
}

// กลับไปยังหน้าเดิมหลังจากบันทึก
header("Location: record_score.php");
exit;
?>