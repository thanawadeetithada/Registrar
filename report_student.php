<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

$student_id = $_GET['student_id'] ?? null;
$class_level = '';
$classroom = '';
$academic_year = $_GET['academic_year'] ?? null;  // เพิ่มบรรทัดนี้

if ($student_id) {
    $stmt = $conn->prepare("SELECT class_level, classroom, academic_year, student_name, student_id FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);  // ใช้ bind_param() เพื่อป้องกัน SQL Injection
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student) {
        $class_level = $student['class_level'];
        $classroom = $student['classroom'];
        // $academic_year = $student['academic_year'];
        $student_id = $student['student_id'];
        $student_name = $student['student_name'];
    }
}

$scores = [];

if ($student_id && $academic_year) {
    $stmt = $conn->prepare("
    SELECT 
        subjects.id AS subject_id,
        subjects.subject_name,
        student_scores.semester1_score,
        student_scores.semester2_score,
        student_scores.total_score,
        student_scores.grade
    FROM student_scores
    INNER JOIN subjects ON student_scores.subject_id = subjects.id
    WHERE student_scores.student_id = ? AND student_scores.academic_year = ?
");

    $stmt->bind_param("ss", $student_id, $academic_year);  // ใช้ bind_param() เพื่อป้องกัน SQL Injection
    $stmt->execute();
    $result = $stmt->get_result();
    $scores = $result->fetch_all(MYSQLI_ASSOC);
}

$grade_ranges = [];
$grade_query = $conn->prepare("SELECT * FROM grade_ranges ORDER BY subject_id, min_score DESC");
$grade_query->execute();
$grade_result = $grade_query->get_result();
while ($row = $grade_result->fetch_assoc()) {
    $grade_ranges[$row['subject_id']][] = $row;
}

// คำนวณเกรดใหม่จากคะแนนรวม
foreach ($scores as &$score) {
    $total = ($score['semester1_score'] ?? 0) + ($score['semester2_score'] ?? 0);
    $score['total_score'] = $total;

    $subject_id = $score['subject_id'];
    $score['grade'] = '-'; // Default: ไม่มีเกรด

    if (isset($grade_ranges[$subject_id])) {
        foreach ($grade_ranges[$subject_id] as $range) {
            if ($total >= $range['min_score'] && $total <= $range['max_score']) {
                $score['grade'] = $range['grade'];
                break;
            }
        }
    }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
        <a class="navbar-brand" href="searchreport_student.php">แบบรายงานผลการพัฒนาคุณภาพผู้เรียนรายบุคคล</a>
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
                    <a class="nav-link active" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน<span
                            class="sr-only">(current)</span></a>
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
                <button class="btn btn-primary"
                    onclick="window.location.href='add_subject_student.php?student_id=<?= urlencode($student_id) ?>&academic_year=<?= urlencode($academic_year) ?>'">
                    <i class="fa-solid fa-circle-plus"></i> เพิ่มรายวิชา
                </button>

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
                        <?php foreach ($scores as $index => $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject_name']) ?>
                                <input type="hidden" name="subject_ids[]" value="<?= $row['subject_id'] ?>">
                            <td>
                                <span
                                    class="display-value"><?= htmlspecialchars($row['semester1_score'] ?? '') ?></span>
                                <input type="number" name="semester1_scores[]"
                                    class="form-control form-control-sm d-none editable-input"
                                    value="<?= $row['semester1_score'] ?>">
                            </td>
                            <td>
                                <span
                                    class="display-value"><?= htmlspecialchars($row['semester2_score'] ?? '') ?></span>
                                <input type="number" name="semester2_scores[]"
                                    class="form-control form-control-sm d-none editable-input"
                                    value="<?= $row['semester2_score'] ?>">
                            </td>
                            <td><?= htmlspecialchars($row['total_score']) ?></td>
                            <td><?= htmlspecialchars($row['grade'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($scores)): ?>
                        <tr>
                            <td colspan="5">ไม่พบข้อมูลคะแนน</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
                <div class="text-right mt-3 no-print">
                    <a href="edit_student.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-warning"
                        id="editBtn">
                        <i class="fas fa-edit"></i> แก้ไขข้อมูล
                    </a>
                    <button class="btn btn-danger" onclick="$('#confirmDeleteModal').modal('show')" id="deleteBtn">
                        <i class="fas fa-trash-alt"></i> ลบนักเรียน
                    </button>
                    <button class="btn btn-success d-none" id="saveBtn" onclick="saveScores()">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                    <button class="btn btn-secondary d-none" id="cancelBtn" onclick="location.reload()">
                        <i class="fas fa-times"></i> ยกเลิก
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5 class="mb-3">คุณแน่ใจหรือไม่?</h5>
                    <p>ต้องการลบนักเรียนคนนี้ออกจากระบบ (รวมคะแนน)?</p>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบ</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteSuccessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>ลบนักเรียนเรียบร้อยแล้ว</h5>
                    <button type="button" class="btn btn-success mt-3" onclick="redirectToSearch()">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saveSuccessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>บันทึกคะแนนเรียบร้อยแล้ว</h5>
                    <button type="button" class="btn btn-success mt-3" data-dismiss="modal"
                        onclick="location.reload()">ปิด</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    document.getElementById('editBtn').addEventListener('click', function(e) {
        e.preventDefault();
        // ซ่อนปุ่มเดิม
        document.getElementById('editBtn').classList.add('d-none');
        document.getElementById('deleteBtn').classList.add('d-none');

        // แสดงปุ่มใหม่
        document.getElementById('saveBtn').classList.remove('d-none');
        document.getElementById('cancelBtn').classList.remove('d-none');

        // แสดง input สำหรับแก้ไข
        document.querySelectorAll('.editable-input').forEach(input => input.classList.remove('d-none'));
        document.querySelectorAll('.display-value').forEach(span => span.classList.add('d-none'));
    });

    function saveScores() {
        const subjectIds = Array.from(document.querySelectorAll('input[name="subject_ids[]"]')).map(i => i.value);
        const s1Scores = Array.from(document.getElementsByName('semester1_scores[]')).map(input => input.value);
        const s2Scores = Array.from(document.getElementsByName('semester2_scores[]')).map(input => input.value);

        $.ajax({
            url: 'update_scores.php',
            type: 'POST',
            data: {
                student_id: '<?= $student_id ?>',
                academic_year: '<?= $academic_year ?>',
                subject_ids: subjectIds,
                semester1_scores: s1Scores,
                semester2_scores: s2Scores
            },
            success: function(response) {
                $('#saveSuccessModal').modal('show');

                // เมื่อ modal ปิด ให้รีเซตหน้ากลับสู่โหมดดูอย่างเดียว
                $('#saveSuccessModal').on('hidden.bs.modal', function() {
                    // ซ่อน input
                    document.querySelectorAll('.editable-input').forEach(input => input.classList
                        .add('d-none'));

                    // แสดงค่าคะแนนกลับมา
                    document.querySelectorAll('.display-value').forEach(span => span.classList
                        .remove('d-none'));

                    // ปิดปุ่มบันทึก/ยกเลิก
                    document.getElementById('saveBtn').classList.add('d-none');
                    document.getElementById('cancelBtn').classList.add('d-none');

                    // เปิดปุ่มแก้ไข/ลบ
                    document.getElementById('editBtn').classList.remove('d-none');
                    document.getElementById('deleteBtn').classList.remove('d-none');
                });
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการบันทึก');
            }
        });
    }

    $('#confirmDeleteBtn').click(function() {
        const studentId = '<?= $student_id ?>';

        $.ajax({
            url: 'delete_student.php',
            type: 'POST',
            dataType: 'json',
            data: {
                student_id: studentId
            },
            success: function(res) {
                if (res.success) {
                    $('#confirmDeleteModal').modal('hide');
                    $('#deleteSuccessModal').modal('show');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + res.message);
                }
            },
            error: function() {
                alert('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
            }
        });
    });

    function redirectToSearch() {
        $('#deleteSuccessModal').modal('hide');
        setTimeout(function() {
            window.location.href = 'searchreport_student.php';
        }, 100);
    }

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