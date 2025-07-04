<?php
require_once 'db.php';

$student_id = $_GET['student_id'] ?? '';
$academic_year = $_GET['academic_year'] ?? '';
$class_level = '';
$student_name = '';

if ($student_id) {
    // ดึงข้อมูลนักเรียน
    $stmt = $conn->prepare("SELECT student_name, class_level FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student) {
        $class_level = $student['class_level'];
        $student_name = $student['student_name'];
    }
}

$subjects = [];
$added_subject_ids = [];

if ($class_level && $academic_year && $student_id) {
    // ดึงวิชาทั้งหมดของปี + ระดับ
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE academic_year = ? AND class_level = ?");
    $stmt->bind_param("ss", $academic_year, $class_level);
    $stmt->execute();
    $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // ดึงวิชาที่เคยเพิ่มไว้แล้ว
    $stmt2 = $conn->prepare("SELECT subject_id FROM student_scores WHERE student_id = ? AND academic_year = ?");
    $stmt2->bind_param("ss", $student_id, $academic_year);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row = $result2->fetch_assoc()) {
        $added_subject_ids[] = $row['subject_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มรายวิชา</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .center-container {
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    h5 {
        margin-top: 8px;
    }
    </style>
</head>

<body class="bg-light">
    <div class="center-container">
        <div class="container mt-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>เพิ่มรายวิชาให้นักเรียน: <?= htmlspecialchars($student_name) ?></h5>
                </div>
                <br>
                <div class="card-body" style="margin-left: 2rem; margin-right: 2rem;">
                    <?php if (!empty($subjects)): ?>
                    <form method="POST" action="insert_subject_student.php" id="subjectForm">
                        <?php foreach ($subjects as $subject): 
        $subject_id = $subject['id'];
        $isChecked = in_array($subject_id, $added_subject_ids);
    ?>
                        <div class="form-check form-switch" style="font-size: 1.3rem;">
                            <input class="form-check-input subject-switch" type="checkbox"
                                data-subject-name="<?= htmlspecialchars($subject['subject_name']) ?>"
                                data-subject-id="<?= $subject_id ?>" name="subject_ids[]" value="<?= $subject_id ?>"
                                id="subject<?= $subject_id ?>" <?= $isChecked ? 'checked' : '' ?>
                                <?= $isChecked ? 'data-default-checked="true"' : '' ?>>
                            <label class="form-check-label" for="subject<?= $subject_id ?>">
                                <?= htmlspecialchars($subject['subject_name']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>

                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">
                        <input type="hidden" name="academic_year" value="<?= htmlspecialchars($academic_year) ?>">
                        <input type="hidden" id="removed_subject_id" name="removed_subject_id">
                        <input type="hidden" id="removed_subject_name" name="removed_subject_name">

                        <!-- เพิ่มบรรทัดนี้ -->
                        <input type="hidden" id="removed_subject_ids" name="removed_subject_ids">

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-success me-2" id="saveBtn">บันทึกรายวิชา</button>
                            <a href="report_student.php?student_id=<?= urlencode($student_id) ?>"
                                class="btn btn-secondary">ยกเลิก</a>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info">ไม่มีรายวิชาในระบบสำหรับปี <?= htmlspecialchars($academic_year) ?>
                        และระดับ <?= htmlspecialchars($class_level) ?></div>
                    <a href="report_student.php?student_id=<?= urlencode($student_id) ?>"
                        class="btn btn-secondary">ย้อนกลับ</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL ยืนยันลบ -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h5 class="mb-3">คุณแน่ใจหรือไม่?</h5>
                    <p>ต้องการลบรายวิชา <span id="modalSubjectName"></span> นี้ออกใช่ไหม?</p>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ตกลง</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    let removedSubjectIds = [];
    let uncheckedSubject = null;

    document.querySelectorAll('.subject-switch').forEach(sw => {
        sw.addEventListener('change', function() {
            const wasCheckedInitially = this.hasAttribute('data-default-checked');
            if (!this.checked && wasCheckedInitially) {
                const name = this.dataset.subjectName;
                const id = this.dataset.subjectId;
                document.getElementById('modalSubjectName').textContent = name;
                uncheckedSubject = this;
                $('#confirmDeleteModal').modal('show');
            }
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (uncheckedSubject) {
            removedSubjectIds.push(uncheckedSubject.dataset.subjectId);
            document.getElementById('removed_subject_ids').value = removedSubjectIds.join(',');
            $('#confirmDeleteModal').modal('hide');
        }
    });

    function redirectToStudentReport() {
        window.location.href = 'report_student.php?student_id=<?= urlencode($student_id) ?>';

    }
    </script>

</body>

</html>