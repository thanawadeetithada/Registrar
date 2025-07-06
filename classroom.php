<?php
require_once 'db.php';

// รับค่าจาก URL
$academic_year = $_GET['academic_year'] ?? '';
$subject_name = $_GET['subject_name'] ?? '';
$class_level = $_GET['class_level'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';

$students = [];

if (!empty($subject_id) && !empty($academic_year)) {
  $stmt = $conn->prepare("
    SELECT 
        ss.student_id, 
        ss.semester1_score, 
        ss.semester2_score, 
        ss.total_score, 
        ss.grade,
        s.citizen_id,
        s.prefix,
        s.student_name,
        s.classroom
    FROM student_scores AS ss
    INNER JOIN students AS s 
        ON ss.student_id = s.student_id
        AND ss.academic_year = s.academic_year
    WHERE 
        ss.subject_id = ? 
        AND ss.academic_year = ? 
        AND s.class_level = ? 
 
");

$stmt->bind_param("iis", $subject_id, $academic_year, $class_level);

    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
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
        <h2 class="text-center">กรอกคะแนน</h2>
        <br>
        <div class="button-group">
            <div class="export-button">
                <button class="btn btn-primary" style="padding: 0px !important;">
                    <a href="export_scores.php?subject_id=<?= $subject_id ?>&academic_year=<?= $academic_year ?>&subject_name=<?= urlencode($subject_name ?? '') ?>&class_level=<?= $class_level ?>"
                        class="btn btn-primary">
                        ดาวน์โหลด
                    </a>
                </button>
            </div>
            <div class="import-button">
                <button id="uploadButton" class="btn btn-success"> นำเข้าคะแนนนักเรียน </button>
                <input type="file" id="uploadExcel" accept=".xlsx, .xls" class="d-none">
            </div>

        </div>
        <br>
        <div class="card">
            <div class="card-header">
                วิชา <?php echo htmlspecialchars($subject_name); ?>
                ปีการศึกษา <?php echo htmlspecialchars($academic_year); ?>
            </div>

            <div class="card-body">
                <form method="POST"
                    action="save_scores.php?subject_id=<?= $subject_id ?>&academic_year=<?= $academic_year ?>&subject_name=<?= urlencode($subject_name ?? '') ?>&class_level=<?= $class_level ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รหัสประจำตัวนักเรียน</th>
                                    <th>เลขบัตรประชาชน</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ห้อง</th>
                                    <th>คะแนนภาคเรียนที่ 1</th>
                                    <th>คะแนนภาคเรียนที่ 2</th>
                                    <th>คะแนนรวม</th>
                                    <th>เกรด</th>
                                    <th>ลบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $s): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($s['student_id']) ?></td>
                                    <td><?= htmlspecialchars($s['citizen_id'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(($s['prefix'] ?? '') . ($s['student_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($s['classroom'] ?? '-') ?></td>
                                    <td>
                                        <input type="number" name="semester1_score[<?= $s['student_id'] ?>]"
                                            value="<?= htmlspecialchars($s['semester1_score'] ?? '') ?>"
                                            class="form-control" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" name="semester2_score[<?= $s['student_id'] ?>]"
                                            value="<?= htmlspecialchars($s['semester2_score'] ?? '') ?>"
                                            class="form-control" step="0.01">
                                    </td>
                                    <td><?= number_format($s['total_score'] ?? 0, 2) ?></td>
                                    <td><?= $s['grade'] ?? 'ไม่มี' ?></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm delete-student"
                                            data-student-id="<?= $s['student_id'] ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>
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

    <div class="modal fade" id="importErrorModal" tabindex="-1" role="dialog" aria-labelledby="importErrorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5 class="text-danger">นักเรียนบางคนไม่มีในระบบ</h5>
                    <p id="missing-students" style="white-space: pre-line;"></p>
                    <button type="button" class="btn btn-danger mt-3" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ยืนยันการลบ -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5 class="mb-3">คุณแน่ใจหรือไม่?</h5>
                    <p>ต้องการลบคะแนนนักเรียนคนนี้ออกจากระบบ?</p>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบ</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal แจ้งลบสำเร็จ -->
    <div class="modal fade" id="deleteSuccessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>ลบคะแนนนักเรียนเรียบร้อย</h5>
                    <button type="button" class="btn btn-success mt-3" id="closeDeleteSuccessModal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        $('form').on('submit', function(event) {
            event.preventDefault(); // ✅ บล็อกการ submit ปกติ
            var form = $(this);

            // ส่งข้อมูลฟอร์มไปยังเซิร์ฟเวอร์
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    $('#saveSuccessModal').modal('show');
                    setTimeout(function() {
                        window.location.href = 'record_score.php';
                    }, 1500);
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
            dataType: 'json', // ✅ เพิ่มบรรทัดนี้เพื่อให้รับ res.not_found ได้
            success: function(res) {
                $('#importSuccessModal').modal('show');
                if (res.not_found && res.not_found.length > 0) {
                    const missingList = res.not_found.join('\n');
                    $('#missing-students').text(missingList);
                    $('#importErrorModal').modal('show');
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการนำเข้า');
            }
        });

    });

    let studentIdToDelete = null;

    // เมื่อกดปุ่มไอคอนลบ
    $('.delete-student').on('click', function() {
        studentIdToDelete = $(this).data('student-id');
        $('#confirmDeleteModal').modal('show');
    });

    // เมื่อยืนยันการลบ
    // เมื่อยืนยันการลบ
    $('#confirmDeleteBtn').on('click', function() {
        if (!studentIdToDelete) return;

        const urlParams = new URLSearchParams(window.location.search);
        let subjectId = urlParams.get('subject_id');
        let academicYear = urlParams.get('academic_year');

        $.ajax({
            url: 'delete_student_subject.php',
            type: 'POST',
            data: {
                student_id: studentIdToDelete,
                subject_id: subjectId,
                academic_year: academicYear
            },
            dataType: 'json',
            success: function(response) {
                let res = response;
                if (res.success) {
                    $('#confirmDeleteModal')
                        .one('hidden.bs.modal', function() {
                            $('#deleteSuccessModal').modal('show');
                        })
                        .modal('hide');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + res.message);
                }
            },
            error: function() {
                alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
            }
        });
    });

    // เมื่อกดปุ่มปิดใน modal ลบสำเร็จ → redirect ไป record_score.php
    $('#deleteSuccessModal').on('hidden.bs.modal', function() {
        window.location.href = 'record_score.php';
    });

    $('#closeDeleteSuccessModal').on('click', function() {
        window.location.href = 'record_score.php';
    });
    </script>

</body>

</html>