<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

$academic_years = [];
$academic_year_query = $conn->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC");
if ($academic_year_query) {
    while ($row = $academic_year_query->fetch_assoc()) {
        $academic_years[] = $row;
    }
}

$subjects = [];
$groupedData = [];

$sql = "
    SELECT 
        s.id AS student_id, s.classroom, s.class_level, s.academic_year, 
        s.student_name, 
        sub.id AS subject_id, sub.subject_name, sub.class_level AS subject_class_level
    FROM students s
    INNER JOIN subjects sub ON s.class_level = sub.class_level AND s.academic_year = sub.academic_year
    ORDER BY s.academic_year DESC, s.class_level, s.classroom, sub.subject_name
";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $year = $row['academic_year'];
        $level = $row['subject_class_level'];
        $subjectName = $row['subject_name'];
        $subjectId = $row['subject_id'];

    // แยกแค่ ปี → วิชา
        $groupedData[$year][$subjectId]['subject_name'] = $subjectName;
        $groupedData[$year][$subjectId]['class_level'] = $level;
        $groupedData[$year][$subjectId]['students'][] = [
            'student_id' => $row['student_id'],
            'student_name' => $row['student_name'],
            'classroom' => $row['classroom']
        ];
    }
}

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
        <a class="navbar-brand" href="searchreport_student.php">บันทึกคะแนน</a>
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
                    <a class="nav-link" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน</a>
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
                <a href="export_scoresall.php" class="btn btn-primary">ดาวน์โหลด</a>
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
        <?php if (!empty($groupedData)): ?>
        <?php foreach ($groupedData as $year => $subjects): ?>
        <div class="card">
            <div class="card-header"> ปีการศึกษา <?= htmlspecialchars($year) ?> </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>วิชา</th>
                            <th>ระดับชั้น</th>
                            <th>กรอกคะแนน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subjectId => $data): ?>
                        <tr>
                            <td class="subject-name"><?= htmlspecialchars($data['subject_name']) ?></td>
                            <td><?= htmlspecialchars($data['class_level']) ?></td>
                            <td>
                                <a href="classroom.php?academic_year=<?= urlencode($year) ?>&subject_name=<?= urlencode($data['subject_name']) ?>&subject_id=<?= $subjectId ?>&class_level=<?= urlencode($data['class_level']) ?>"
                                    class="btn btn-primary">กรอกคะแนน</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="card" style="border: none;">
            <div class="card-body text-center text-muted"
                style="padding: 2rem;background: #cfd8e5;border-radius: 10px;">
                <h5><i class="fas fa-info-circle"></i> ไม่พบข้อมูลนักเรียนในระบบ</h5>
            </div>
        </div>
        <?php endif; ?>

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

    <!-- Error Modal -->
    <div class="modal fade" id="importErrorModal" tabindex="-1" role="dialog" aria-labelledby="importErrorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="importErrorModalLabel">พบรหัสวิชาที่ไม่ถูกต้อง</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>มีนักเรียนบางคนที่ไม่สามารถเพิ่มคะแนนได้ เนื่องจากไม่พบรหัสวิชาในระบบ</p>
                    <div id="errorSubjectList" class="pl-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">ปิด</button>
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
                } else if (result.invalid_subjects) {
                    // แสดง modal รายชื่อที่ error
                    const html = result.invalid_subjects.map(s =>
                        `<div>- ${s.student_name} <strong>(รหัสวิชา: ${s.subject_id})</strong></div>`
                    ).join('');
                    document.getElementById('errorSubjectList').innerHTML = html;
                    $('#importErrorModal').modal('show');
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