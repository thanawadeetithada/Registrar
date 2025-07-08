<?php
require_once 'db.php'; // เชื่อมต่อฐานข้อมูล

$search = $_POST['search'] ?? '';

if ($search) {
    $sql = "
        SELECT * FROM students 
        WHERE student_id LIKE ? 
           OR student_name LIKE ? 
           OR prefix LIKE ? 
           OR CONCAT(prefix, student_name) LIKE ? 
           OR CONCAT(prefix, ' ', student_name) LIKE ? 
           OR SUBSTRING_INDEX(student_name, ' ', 1) LIKE ? 
           OR SUBSTRING_INDEX(student_name, ' ', -1) LIKE ?
    ";

    $stmt = $conn->prepare($sql);
    $searchTerm = "%$search%";
    
    // ผูกพารามิเตอร์เข้ากับ statement
    $stmt->bind_param("sssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    
    // รันคำสั่ง SQL
    $stmt->execute();
    
    // ดึงผลลัพธ์
    $result = $stmt->get_result();
    
    // เช็คว่ามีข้อมูลหรือไม่
    if ($result->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-hover">';
        echo '<thead class="thead-dark"><tr>
                <th style="width: 100px;" class="text-center">ลำดับ</th>
                <th class="text-center">รหัสนักเรียน</th>
                <th class="text-center">เลขบัตรประชาชน</th>
                <th class="text-center">ชื่อ</th>
                <th class="text-center">ระดับชั้น</th>
                <th class="text-center">ห้อง</th>
                <th class="text-center">ปีการศึกษา</th>
                </tr></thead>';
        echo '<tbody>';
        
        // แสดงผลลัพธ์
        $index = 1;
        while ($student = $result->fetch_assoc()) {
            echo '<tr class="clickable-row" data-id="' . htmlspecialchars($student['id']) . '">';
            echo '<td class="text-center">' . $index++ . '</td>';
            echo '<td class="text-center">' . htmlspecialchars($student['student_id']) . '</td>';
            echo '<td>' . htmlspecialchars($student['citizen_id']) . '</td>';
            echo '<td>' . htmlspecialchars($student['prefix'] . ' ' . $student['student_name']) . '</td>';
            echo '<td>' . htmlspecialchars($student['class_level']) . '</td>';
            echo '<td class="text-center">' . htmlspecialchars($student['classroom']) . '</td>';
            echo '<td class="text-center">' . htmlspecialchars($student['academic_year']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning text-center">ไม่พบข้อมูลนักเรียน</div>';
    }
    
    echo '</div>';
}
?>