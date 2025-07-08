<?php
require_once 'db.php';

$academicYear = $_GET['academic_year'] ?? '';
$classGroup = $_GET['class_group'] ?? '';
$classroom = $_GET['classroom'] ?? '';

$where = [];
$params = [];
$types = '';

// เงื่อนไขปีการศึกษา
if (!empty($academicYear)) {
    $where[] = 'academic_year = ?';
    $params[] = $academicYear;
    $types .= 'i';
}

if (!empty($classGroup)) {
    $where[] = "class_level = ?";
    $params[] = $classGroup;
    $types .= 's';
}

if (!empty($classroom)) {
    $where[] = "classroom = ?";
    $params[] = $classroom;
    $types .= 's';
}

$sql = "SELECT id, student_id, student_name, prefix, class_level, classroom, academic_year, citizen_id FROM students";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY class_level, student_name";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-hover">';
    echo '<thead class="thead-dark">';
    echo '<tr>
            <th style="width: 100px;" class="text-center">ลำดับ</th>
            <th class="text-center">รหัสนักเรียน</th>
            <th class="text-center">เลขบัตรประชาชน</th>
            <th class="text-center">ชื่อ-นามสกุล</th>
            <th class="text-center">ระดับชั้น</th>
            <th class="text-center">ห้อง</th>
            <th class="text-center">ปีการศึกษา</th>
          </tr>';
    echo '</thead><tbody>';
    $index = 1;
    while ($row = $result->fetch_assoc()) {
        echo '<tr class="clickable-row" data-id="' . htmlspecialchars($row['id']) . '">';
        echo '<td class="text-center">' . $index++ . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($row['student_id']) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($row['citizen_id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['prefix'] . ' ' . $row['student_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['class_level']) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($row['classroom']) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($row['academic_year']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
} else {
    echo '<div class="alert alert-warning text-center">ไม่พบนักเรียนในระบบ</div>';
}
?>
