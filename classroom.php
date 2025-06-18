<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลจาก URL
$academic_year = $_GET['academic_year'] ?? ''; // ตรวจสอบค่า academic_year
$subject_name = $_GET['subject_name'] ?? '';  // ตรวจสอบค่า subject_name
$class_level = $_GET['class_level'] ?? '';  // ตรวจสอบค่า class_level
$classroom = $_GET['classroom'] ?? '';  // ตรวจสอบค่า classroom

// ดึงข้อมูลนักเรียนและวิชาตามค่าที่ส่งมา
$students_query = $pdo->prepare("SELECT * FROM students WHERE class_level = :class_level AND classroom = :classroom AND academic_year = :academic_year");
$students_query->execute([
    'class_level' => $class_level,
    'classroom' => $classroom,
    'academic_year' => $academic_year
]);
$students = $students_query->fetchAll(PDO::FETCH_ASSOC);

$subject_query = $pdo->prepare("SELECT * FROM subjects WHERE subject_name = :subject_name");
$subject_query->execute(['subject_name' => $subject_name]);
$subject = $subject_query->fetch(PDO::FETCH_ASSOC);

$scores_query = $pdo->prepare("
    SELECT * FROM student_scores
    WHERE academic_year = :academic_year AND subject_id = :subject_id
");
$scores_query->execute([
    'academic_year' => $academic_year,
    'subject_id' => $subject['id'] // สมมติว่า $subject ถูกดึงข้อมูลและมี id
]);
$scores = $scores_query->fetchAll(PDO::FETCH_ASSOC);


$grade_query = $pdo->prepare("SELECT * FROM grade_ranges WHERE subject_id = :subject_id ORDER BY min_score DESC");
$grade_query->execute(['subject_id' => $subject['id']]);
$grade_ranges = $grade_query->fetchAll(PDO::FETCH_ASSOC);

// คำนวณคะแนนรวมและเกรด
foreach ($students as $student) {
    // กรองข้อมูลคะแนนของนักเรียนแต่ละคน
    $student_score = array_filter($scores, function($score) use ($student) {
        return $score['student_id'] == $student['student_id'];
    });
    $student_score = reset($student_score); // เอาคะแนนที่ตรงกับนักเรียนมาใช้

    $total_score = ($student_score['semester1_score'] ?? 0) + ($student_score['semester2_score'] ?? 0);

    // คำนวณเกรดจากฐานข้อมูล
    $grade = 'F'; // กำหนดเกรดเริ่มต้นเป็น F
    foreach ($grade_ranges as $range) {
        if ($total_score >= $range['min_score'] && $total_score <= $range['max_score']) {
            $grade = $range['grade'];
            break;
        }
    }
    // ... (โค้ดที่ใช้แสดงผลคะแนนและเกรด)
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กรอกคะแนนนักเรียน</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
    .card {
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #004085;
        color: white;
        font-size: 18px;
    }

    .table th,
    .table td {
        vertical-align: middle;
        text-align: center;
        white-space: nowrap;
    }

    .form-group {
        margin-bottom: 10px;
    }

    .btn-record {
        text-align: end;
    }

    .button-group {
        display: flex;
        justify-content: flex-end;
    }

    .export-button {
        margin-right: 15px;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004085 !important;padding-left: 2rem;">
        <a class="navbar-brand" href="index.php">ระบบจัดการนักเรียน</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">บันทึกข้อมูลนักเรียน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="record_score.php">บันทึกคะแนน<span
                            class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="subject.php">เกรดแต่ละรายวิชา</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center">กรอกคะแนน</h2>
        <br>
        <div class="button-group">
            <div class="export-button">
                <button class="btn btn-primary" style="padding: 0px !important;">
                    <a href="export_scores.php?subject_id=<?php echo $subject['id']; ?>&academic_year=<?php echo $academic_year; ?>&subject_name=<?php echo urlencode($subject['subject_name']); ?>&class_level=<?php echo $class_level; ?>&classroom=<?php echo $classroom; ?>"
                        class="btn btn-primary">
                        ดาวน์โหลด
                    </a>
                </button>
            </div>
            <div class="import-button">
                <button id="uploadButton" class="btn btn-success" style="padding-bottom: 7px;padding-top: 7px;">
                    นำเข้าคะแนนนักเรียน
                </button>
                <input type="file" id="uploadExcel" accept=".xlsx, .xls" class="d-none">

            </div>
        </div>
        <br>
        <div class="card">
            <div class="card-header">
                ห้อง <?php echo $classroom; ?> วิชา <?php echo $subject['subject_name']; ?> ปีการศึกษา
                <?php echo $academic_year; ?>
            </div>
            <div class="card-body">
                <form method="POST"
                    action="save_scores.php?subject_id=<?php echo $subject['id']; ?>&academic_year=<?php echo $academic_year; ?>&subject_name=<?php echo urlencode($subject['subject_name']); ?>&class_level=<?php echo $class_level; ?>&classroom=<?php echo $classroom; ?>">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>รหัสประจำตัวนักเรียน</th>
                                <th>เลขบัตรประชาชน</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>คะแนนภาคเรียนที่ 1</th>
                                <th>คะแนนภาคเรียนที่ 2</th>
                                <th>คะแนนรวม</th>
                                <th>เกรด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
    $count = 1;
    foreach ($students as $student):
        // กรองข้อมูลคะแนนของนักเรียนแต่ละคน
        $student_score = array_filter($scores, function($score) use ($student) {
            return $score['student_id'] == $student['student_id'];
        });
        $student_score = reset($student_score); // เอาคะแนนที่ตรงกับนักเรียนมาใช้

        $total_score = ($student_score['semester1_score'] ?? 0) + ($student_score['semester2_score'] ?? 0);

        // คำนวณเกรดจากฐานข้อมูล
        $grade = 'ไม่มีเกรด'; // กำหนดเกรดเริ่มต้นเป็น F
        foreach ($grade_ranges as $range) {
            if ($total_score >= $range['min_score'] && $total_score <= $range['max_score']) {
                $grade = $range['grade'];
                break;
            }
        }
    ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td><?php echo $student['student_id']; ?></td>
                                <td><?php echo $student['citizen_id']; ?></td>
                                <td><?php echo $student['prefix'] . $student['student_name']; ?></td>
                                <td>
                                    <input type="number" name="semester1_score[<?php echo $student['student_id']; ?>]"
                                        value="<?php echo $student_score ? $student_score['semester1_score'] : ''; ?>"
                                        class="form-control" step="0.01">
                                </td>
                                <td>
                                    <input type="number" name="semester2_score[<?php echo $student['student_id']; ?>]"
                                        value="<?php echo $student_score ? $student_score['semester2_score'] : ''; ?>"
                                        class="form-control" step="0.01">
                                </td>
                                <td>
                                    <?php echo $total_score; ?>
                                </td>
                                <td>
                                    <?php echo $grade; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                    <div class="btn-record">
                        <button type="submit" class="btn btn-primary" data-toggle="modal"
                            data-target="#saveSuccessModal">บันทึกคะแนน</button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saveSuccessModal" tabindex="-1" role="dialog" aria-labelledby="saveSuccessModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="text-success mb-3"></i>
                    <h5>บันทึกสำเร็จ</h5>
                    <button type="button" class="btn btn-success mt-3" data-dismiss="modal"
                        onclick="window.location.href='record_score.php'">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importSuccessModal" tabindex="-1" role="dialog"
        aria-labelledby="importSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5>นำเข้าคะแนนสำเร็จ</h5>
                    <button type="button" class="btn btn-success mt-3" data-dismiss="modal"
                        onclick="location.reload()">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        $('form').on('submit', function(event) {
            event.preventDefault(); // ป้องกันการรีเฟรชหน้าเว็บโดยอัตโนมัติ
            var form = $(this);

            // ส่งข้อมูลฟอร์มไปยังเซิร์ฟเวอร์
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    // เมื่อบันทึกสำเร็จ แสดง Modal
                    $('#saveSuccessModal').modal('show');
                },
                error: function() {
                    alert('เกิดข้อผิดพลาด!');
                }
            });
        });
    });

    $('#uploadButton').click(function() {
        $('#uploadExcel').click();
    });

    $('#uploadExcel').change(function() {
        let file = this.files[0];
        let formData = new FormData();
        formData.append('file', file);

        const urlParams = new URLSearchParams(window.location.search);
        let subjectId = urlParams.get('subject_id');
        let academicYear = urlParams.get('academic_year');

        $.ajax({
            url: 'import_scores.php?subject_id=' + subjectId + '&academic_year=' + academicYear,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                $('#importSuccessModal').modal('show');

            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการนำเข้า');
            }
        });
    });


    var urlParams = new URLSearchParams(window.location.search);
    var subjectId = urlParams.get('subject_id');

    // แสดงค่า subject_id ใน console
    console.log('subject_id from URL:', subjectId);
    </script>

</body>

</html>