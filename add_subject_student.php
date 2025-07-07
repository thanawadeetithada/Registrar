<?php
require_once 'db.php';
$id = $_GET['id'] ?? '';
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

$displayed_levels = []; 
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
                        <?php 
                        $subject_checked_map = []; // [1 => [subject_id, ...], 2 => [...]]
                        $subject_total_map = [];     // นับจำนวนวิชาทั้งหมดในแต่ละชั้น
                        $subject_selected_map = [];

                    foreach ($subjects as $subject): 
    $subject_id = $subject['id'];
    $subject_name = $subject['subject_name'];
    $isChecked = in_array($subject_id, $added_subject_ids);

    if (preg_match('/ป\.(\d)/u', $subject_name, $match)) {
        $grade = $match[1];

        // รวมจำนวนวิชาทั้งหมดของแต่ละชั้น
        if (!isset($subject_total_map[$grade])) $subject_total_map[$grade] = 0;
        $subject_total_map[$grade]++;

        // นับเฉพาะที่ถูกเลือก
        if (in_array($subject_id, $added_subject_ids)) {
            if (!isset($subject_selected_map[$grade])) $subject_selected_map[$grade] = 0;
            $subject_selected_map[$grade]++;
        }
    }
    // === Step 1: ตรวจหา ป.1–ป.6 จากชื่อวิชา ===
    $grade_level_match = [];
    preg_match('/ป\.(\d)/u', $subject_name, $grade_level_match);
    $grade_level = $grade_level_match[1] ?? null;

    // === Step 2: Render switcher ป.1–ป.6 เฉพาะรอบแรกเท่านั้น ===
    if ($grade_level && !in_array($grade_level, $displayed_levels)):
        $displayed_levels[] = $grade_level; // เก็บว่ามีการแสดงแล้ว
        $isGradeFullyChecked = isset($subject_total_map[$grade_level]) &&
                       isset($subject_selected_map[$grade_level]) &&
                       $subject_total_map[$grade_level] === $subject_selected_map[$grade_level];

?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch" style="font-size: 1.1rem;">
                                    <input class="form-check-input subject-switch" type="checkbox" name="subject_ids[]"
                                        value="<?= $subject_id ?>" id="subject<?= $subject_id ?>"
                                        data-subject-name="<?= htmlspecialchars($subject_name) ?>"
                                        data-subject-id="<?= $subject_id ?>" data-grade="<?= $grade_level ?>"
                                        <?= $isChecked ? 'checked data-default-checked="true"' : '' ?>>

                                    <label class="form-check-label" for="subject<?= $subject_id ?>">
                                        <?= htmlspecialchars($subject_name) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch d-flex align-items-center"
                                    style="font-size: 1.1rem;">
                                    <input class="form-check-input ms-2" type="checkbox"
                                        name="display_by_level[<?= $grade_level ?>]"
                                        id="displayLevel<?= $grade_level ?>"
                                        <?= $isGradeFullyChecked ? 'checked' : '' ?>>
                                    <label class="form-check-label ms-5" for="displayLevel<?= $grade_level ?>">
                                        ป.<?= $grade_level ?> ทั้งหมด
                                    </label>
                                </div>
                            </div>
                        </div>

                        <?php else: ?>
                        <!-- หากแสดง switcher แล้ว ก็ไม่ต้องแสดงอีก -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch" style="font-size: 1.1rem;">
                                    <input class="form-check-input subject-switch" type="checkbox" name="subject_ids[]"
                                        value="<?= $subject_id ?>" id="subject<?= $subject_id ?>"
                                        data-subject-name="<?= htmlspecialchars($subject_name) ?>"
                                        data-subject-id="<?= $subject_id ?>" data-grade="<?= $grade_level ?>"
                                        <?= $isChecked ? 'checked data-default-checked="true"' : '' ?>>

                                    <label class="form-check-label" for="subject<?= $subject_id ?>">
                                        <?= htmlspecialchars($subject_name) ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>


                        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">
                        <input type="hidden" name="academic_year" value="<?= htmlspecialchars($academic_year) ?>">
                        <input type="hidden" id="removed_subject_id" name="removed_subject_id">
                        <input type="hidden" id="removed_subject_name" name="removed_subject_name">

                        <!-- เพิ่มบรรทัดนี้ -->
                        <input type="hidden" id="removed_subject_ids" name="removed_subject_ids">

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-success me-2" id="saveBtn">บันทึกรายวิชา</button>
                            <a href="report_student.php?id=<?= urlencode($id) ?>" class="btn btn-secondary">ยกเลิก</a>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info">ไม่มีรายวิชาในระบบสำหรับปี <?= htmlspecialchars($academic_year) ?>
                        และระดับ <?= htmlspecialchars($class_level) ?></div>
                    <a href="report_student.php?id=<?= urlencode($id) ?>" class="btn btn-secondary">ย้อนกลับ</a>
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
                    <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button> -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let removedSubjectIds = [];
    let uncheckedSubject = null;
    let uncheckedLevelSwitch = null;

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

            const grade = this.dataset.grade;
            if (grade) {
                updateLevelSwitchState(grade);
            }
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (uncheckedSubject) {
            removedSubjectIds.push(uncheckedSubject.dataset.subjectId);
            document.getElementById('removed_subject_ids').value = removedSubjectIds.join(',');
            $('#confirmDeleteModal').modal('hide');
        } else if (uncheckedLevelSwitch) {
            // กรณียืนยันลบกลุ่มชั้น
            const level = uncheckedLevelSwitch.id.replace('displayLevel', '');
            uncheckedLevelSwitch.checked = false;

            // เอา checkbox วิชาทั้งหมดของชั้นนี้ออก
            document.querySelectorAll('.subject-switch[data-grade="' + level + '"]').forEach(sub => {
                if (sub.hasAttribute('data-default-checked')) {
                    removedSubjectIds.push(sub.dataset.subjectId);
                }
                sub.checked = false;
            });

            document.getElementById('removed_subject_ids').value = removedSubjectIds.join(',');
            $('#confirmDeleteModal').modal('hide');
        }

        // reset ตัวแปร
        uncheckedSubject = null;
        uncheckedLevelSwitch = null;
    });

    function redirectToStudentReport() {
        window.location.href = 'report_student.php?id=<?= urlencode($id) ?>';

    }

    document.querySelectorAll('[id^="displayLevel"]').forEach(levelSwitcher => {
        levelSwitcher.addEventListener('change', function(e) {
            const level = this.id.replace('displayLevel', '');
            const checked = this.checked;

            // ถ้าเปลี่ยนจาก true → false
            if (!checked && this.defaultChecked) {
                uncheckedLevelSwitch = this;
                document.getElementById('modalSubjectName').textContent = 'ทั้งหมดของ ป.' + level;
                $('#confirmDeleteModal').modal('show');

                // ยกเลิก toggle ชั่วคราว (เพื่อรอผู้ใช้ยืนยัน)
                setTimeout(() => {
                    this.checked = true
                }, 0);
                return;
            }

            // เปลี่ยนสถานะของ checkbox รายวิชาในระดับนี้
            document.querySelectorAll('.subject-switch[data-grade="' + level + '"]').forEach(sub => {
                sub.checked = checked;
            });
        });
    });

    function updateLevelSwitchState(grade) {
        const allInGrade = document.querySelectorAll(`.subject-switch[data-grade="${grade}"]`);
        const checkedInGrade = Array.from(allInGrade).filter(el => el.checked);

        const levelSwitch = document.getElementById('displayLevel' + grade);
        if (levelSwitch) {
            levelSwitch.checked = checkedInGrade.length === allInGrade.length;
        }
    }
    </script>

</body>

</html>