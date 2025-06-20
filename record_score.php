<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

$academic_year_query = $pdo->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC");
$academic_years = $academic_year_query->fetchAll(PDO::FETCH_ASSOC);

$subjects_query = $pdo->query("SELECT * FROM subjects");
$subjects = $subjects_query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกคะแนนนักเรียน</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
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
    }

    .table td,
    .table th {
        text-align: center;
    }

    .table td.subject-name,
    .table th.subject-name {
        text-align: left;
    }

    .form-group {
        margin-bottom: 10px;
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
        <a class="navbar-brand" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน</a>
                </li>
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
        <h2 class="text-center">บันทึกคะแนนนักเรียน</h2>
        <br>
        <div class="button-group">
            <div class="export-button">
                <div class="export-button">
                    <a href="export_scoresall.php" class="btn btn-primary">ดาวน์โหลด</a>
                </div>

            </div>
            <div class="import-button">
                <button id="uploadButton" class="btn btn-success" style="padding-bottom: 7px;padding-top: 7px;">
                    นำเข้าคะแนนนักเรียน
                </button>
                <input type="file" id="uploadExcel" accept=".xlsx, .xls" class="d-none">
            </div>
        </div>
        <br>
        <?php if (empty($academic_years)): ?>
        <div class="card" style="border: none;">
            <div class="card-body text-center text-muted"
                style="padding: 2rem;background: #cfd8e5;border-radius: 10px;">
                <h5><i class="fas fa-info-circle"></i> ไม่พบข้อมูลนักเรียนในระบบ</h5>
            </div>
        </div>
        <?php else: ?>

        <?php foreach ($academic_years as $academic_year): 
    $filteredSubjects = [];

    foreach ($subjects as $subject) {
    $students_query = $pdo->prepare("
        SELECT DISTINCT s.*
        FROM students s
        WHERE s.class_level = :class_level
          AND s.academic_year = :academic_year
    ");
    $students_query->execute([
        'class_level' => $subject['class_level'],
        'academic_year' => $subject['academic_year']
    ]);
    $students = $students_query->fetchAll(PDO::FETCH_ASSOC);

    if (count($students) > 0) {
        $filteredSubjects[] = [
            'subject' => $subject,
            'students' => $students
        ];
    }
}

    // ✅ ข้ามการแสดง card ถ้าไม่มีวิชาหรือคะแนนเลย
    if (empty($filteredSubjects)) continue;
 
    $previous_subject_name = ''; 
    $previous_class_level = ''; 
    $previous_classroom = ''; 
    $previous_academic_year = ''; 
?>

        <!-- แสดงปีการศึกษา -->
        <div class="card">
            <div class="card-header">
                ปีการศึกษา <?php echo $academic_year['academic_year']; ?>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>วิชา</th>
                            <th>ระดับชั้น</th>
                            <th>ห้อง</th>
                            <th>กรอกคะแนน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
foreach ($filteredSubjects as $entry):
    $subject = $entry['subject'];
    $students = $entry['students'];

    foreach ($students as $student):
        $is_subject_duplicate = (
            $previous_subject_name === $subject['subject_name'] &&
            $previous_class_level === $subject['class_level'] &&
            $previous_academic_year === $academic_year['academic_year']
        );

        if (
            $previous_academic_year !== $academic_year['academic_year'] ||
            $previous_class_level !== $subject['class_level'] ||
            $previous_classroom !== $student['classroom'] ||
            $previous_subject_name !== $subject['subject_name']
        ):
?>
                        <tr>
                            <td class="subject-name">
                                <?php echo $is_subject_duplicate ? '' : $subject['subject_name']; ?></td>
                            <td><?php echo $subject['class_level']; ?></td>
                            <td><?php echo $student['classroom']; ?></td>
                            <td>
                                <a href="classroom.php?academic_year=<?php echo $academic_year['academic_year']; ?>&subject_name=<?php echo urlencode($subject['subject_name']); ?>&subject_id=<?php echo $subject['id']; ?>&class_level=<?php echo $subject['class_level']; ?>&classroom=<?php echo $student['classroom']; ?>"
                                    class="btn btn-primary"
                                    onclick="console.log('subject_id:', <?php echo $subject['id']; ?>)">กรอกคะแนน</a>
                            </td>
                        </tr>
                        <?php
        endif;

        $previous_subject_name = $subject['subject_name'];
        $previous_class_level = $subject['class_level'];
        $previous_classroom = $student['classroom'];
        $previous_academic_year = $academic_year['academic_year'];
    endforeach;
endforeach;
?>
                    </tbody>

                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="importSuccessModal" tabindex="-1" role="dialog" aria-labelledby="saveSuccessModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                    <div id="modalMessage" class="text-center"></div>
                    <button type="button" class="btn btn-success mt-3" data-dismiss="modal"
                        onclick="location.reload()">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    document.getElementById('uploadButton').addEventListener('click', function() {
        document.getElementById('uploadExcel').click();
    });

    document.getElementById('uploadExcel').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        fetch('import_scoresall.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    document.getElementById('modalMessage').innerHTML =
                        `นำเข้าคะแนนสำเร็จแล้ว<br>(${result.inserted} รายการ)`;
                    $('#importSuccessModal').modal('show');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Upload failed:', error);
                alert('ไม่สามารถอัปโหลดไฟล์ได้: ' + error);
            });
    });
    </script>

</body>

</html>