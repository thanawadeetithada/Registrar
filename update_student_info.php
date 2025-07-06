<?php
require_once 'db.php';

$id = $_POST['id'] ?? null;  // รับค่า id แทน student_id
$prefix = $_POST['prefix'] ?? null;
$student_name = $_POST['student_name'] ?? null;
$citizen_id = $_POST['citizen_id'] ?? null;
$birth_date = $_POST['birth_date'] ?? null;

if ($id && $prefix && $student_name && $citizen_id && $birth_date) {
    $stmt = $conn->prepare("UPDATE students SET prefix = ?, student_name = ?, citizen_id = ?, birth_date = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $prefix, $student_name, $citizen_id, $birth_date, $id);  // id เป็น integer

    if ($stmt->execute()) {
        echo 'success';
    } else {
        http_response_code(500);
        echo 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
    }
} else {
    http_response_code(400);
    echo 'ข้อมูลไม่ครบ';
}
?>
