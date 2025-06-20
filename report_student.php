<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

// $student_id = $_GET['student_id'] ?? null;
$student_id = 'S002';
$class_level = '';
$classroom = '';
$academic_year = '';

if ($student_id) {
    $stmt = $pdo->prepare("SELECT class_level, classroom, academic_year, student_name, student_id FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $class_level = $student['class_level'];
        $classroom = $student['classroom'];
        $academic_year = $student['academic_year'];
        $student_id = $student['student_id'];
        $student_name = $student['student_name'];
    }
}

$scores = [];

if ($student_id && $academic_year) {
    $stmt = $pdo->prepare("
        SELECT 
            subjects.subject_name,
            student_scores.semester1_score,
            student_scores.semester2_score,
            student_scores.total_score,
            student_scores.grade
        FROM student_scores
        INNER JOIN subjects ON student_scores.subject_id = subjects.id
        WHERE student_scores.student_id = ? AND student_scores.academic_year = ?
    ");
    $stmt->execute([$student_id, $academic_year]);
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาข้อมูลนักเรียน</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
        margin: 0;
    }

    .card {
        padding: 1rem;
        border: none;
    }

    .over-card {
        display: flex;
        justify-content: center;
    }

    .center-container {
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004085 !important;padding-left: 2rem;">
        <a class="navbar-brand" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน<span
                            class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">บันทึกข้อมูลนักเรียน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="record_score.php">บันทึกคะแนน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="subject.php">เกรดแต่ละรายวิชา</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="center-container">
        <div class="card shadow" style="max-width: 960px; width: 100%; background: #f7f9fc;">
            <div class="text-right mb-3 no-print">
                <button onclick="printCard()" class="btn btn-primary">
                    <i class="fas fa-print"></i> พิมพ์รายงาน
                </button>
            </div>

            <div class="card-body">
                <h5 class="text-center font-weight-bold">แบบรายงานผลการพัฒนาคุณภาพผู้เรียนรายบุคคล</h5>
                <p class="text-center mb-1">
                    <?php if ($class_level && $classroom && $academic_year): ?>
                    <?= htmlspecialchars($class_level) ?> ปีที่ <?= htmlspecialchars($classroom) ?> ปีการศึกษา
                    <?= htmlspecialchars($academic_year) ?> <br>
                    รหัสประจำตัวนักเรียน <?= htmlspecialchars($student_id) ?> ชื่อ
                    <?= htmlspecialchars($student_name) ?>
                    <?php else: ?>
                    ไม่พบข้อมูลนักเรียน
                    <?php endif; ?>
                </p>

                <br>
                <table class="table table-bordered text-center table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th rowspan="2" style="vertical-align: middle;">กลุ่มสาระการเรียนรู้</th>
                            <th colspan="1">ภาคเรียนที่ 1</th>
                            <th colspan="1">ภาคเรียนที่ 2</th>
                            <th rowspan="2" style="vertical-align: middle;">รวม</th>
                            <th rowspan="2" style="vertical-align: middle;">ระดับ</th>
                        </tr>
                        <?php if ($class_level === 'ชั้นมัธยมศึกษา'): ?>
                        <tr>
                            <th>100 คะแนน</th>
                            <th>100 คะแนน</th>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <th>50 คะแนน</th>
                            <th>50 คะแนน</th>
                        </tr>
                        <?php endif; ?>

                    </thead>
                    <tbody>
                        <?php foreach ($scores as $row): ?>
                        <?php
            $subject_name = $row['subject_name'];
            $s1 = $row['semester1_score'];
            $s2 = $row['semester2_score'];
            $total = $row['total_score'];
            $grade = $row['grade'] ?? '-';
        ?>
                        <tr>
                            <td><?= htmlspecialchars($subject_name) ?></td>
                            <td><?= htmlspecialchars($s1) ?></td>
                            <td><?= htmlspecialchars($s2) ?></td>
                            <td><?= htmlspecialchars($total) ?></td>
                            <td><?= htmlspecialchars($grade) ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($scores)): ?>
                        <tr>
                            <td colspan="5">ไม่พบข้อมูลคะแนน</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    function printCard() {
        const card = document.querySelector('.card').cloneNode(true);

        // ลบปุ่มพิมพ์ (มี class .no-print)
        const elementsToRemove = card.querySelectorAll('.no-print');
        elementsToRemove.forEach(el => el.remove());

        const printWindow = window.open('', '', 'width=900,height=700');
        printWindow.document.write(`
        <html>
        <head>
            <title>พิมพ์รายงานผล</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    padding: 20px;
                }
                table th, table td {
                    border: 1px solid #ccc;
                }
            </style>
        </head>
        <body onload="window.print();window.close();">
            ${card.innerHTML}
        </body>
        </html>
    `);
        printWindow.document.close();
    }
    </script>
</body>

</html>