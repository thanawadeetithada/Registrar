<?php
require_once 'db.php';

$search = $_POST['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("
        SELECT * FROM students 
        WHERE student_id LIKE :search 
           OR student_name LIKE :search 
           OR prefix LIKE :search 
           OR CONCAT(prefix, student_name) LIKE :search
           OR CONCAT(prefix, ' ', student_name) LIKE :search
           OR SUBSTRING_INDEX(student_name, ' ', 1) LIKE :search
           OR SUBSTRING_INDEX(student_name, ' ', -1) LIKE :search
    ");
    $stmt->execute(['search' => "%$search%"]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($students) > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>รหัสนักเรียน</th><th>เลขบัตรประชาชน</th><th>ชื่อ</th><th>ระดับชั้น</th><th>ห้อง</th><th>ปีการศึกษา</th></tr></thead>';
        echo '<tbody>';
        foreach ($students as $student) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($student['student_id']) . '</td>';
            echo '<td>' . htmlspecialchars($student['citizen_id']) . '</td>';
            echo '<td>' . htmlspecialchars($student['prefix'] . ' ' . $student['student_name']) . '</td>';
            echo '<td>' . htmlspecialchars($student['class_level']) . '</td>';
            echo '<td>' . htmlspecialchars($student['classroom']) . '</td>';
            echo '<td>' . htmlspecialchars($student['academic_year']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning text-center">ไม่พบข้อมูลนักเรียน</div>';
    }
     echo '</div>';
}
?>