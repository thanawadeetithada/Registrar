<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกข้อมูลนักเรียน</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
    }

    .card {
        padding: 1rem;
        border-radius: 10px;
    }

    h3 {
        text-align: center;
    }

    .button-group {
        text-align: end;
        width: 95%;
    }

    .btn-form {
        text-align: center;
    }

    .over-card {
        display: flex;
        justify-content: center;
    }

    form {
        margin: 0 2%;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000 !important;
        /* เปลี่ยนเป็นสีดำ */
    }

    .table-bordered {
        border: 1px solid #000 !important;
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
                    <a class="nav-link active" href="index.php">บันทึกข้อมูลนักเรียน <span
                            class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student.php">ข้อมูลนักเรียน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="subject.php">เกรดแต่ละรายวิชา</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="card">
        <h3>บันทึกข้อมูลนักเรียน</h3>
        <div class="card-body">
            <div class="button-group">
                <div class="import-button">
                    <input type="file" id="uploadExcel" accept=".xlsx, .xls" class="d-none">
                    <button id="uploadButton" class="btn btn-success">
                        <i class="fa-regular fa-file-excel"></i><br>
                        นำเข้าข้อมูลนักเรียน
                    </button>
                </div>
            </div>
            <br>
            <div class="over-card">
                <div class="card" style="background: #cfd8e5; width: 90%;">
                    <div class="card-body">
                        <form id="studentForm">
                            <div class="form-group">
                                <label>ปีการศึกษา</label>
                                <input type="number" class="form-control" name="academic_year" required>
                            </div>
                            <div class="form-group">
                                <label>ภาคเรียนที่</label>
                                <select class="form-control" name="semester" required>
                                    <option value="1">ภาคเรียนที่ 1</option>
                                    <option value="2">ภาคเรียนที่ 2</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>ระดับชั้น</label>
                                <select class="form-control" name="class_level" id="classLevelSelect"
                                    onchange="loadSubjectsForClass()" required>
                                    <option disabled selected>-- เลือกระดับชั้น --</option>
                                    <option value="ป.1">ป.1</option>
                                    <option value="ป.2">ป.2</option>
                                    <option value="ป.3">ป.3</option>
                                    <option value="ป.4">ป.4</option>
                                    <option value="ป.5">ป.5</option>
                                    <option value="ป.6">ป.6</option>
                                    <option value="ม.1">ม.1</option>
                                    <option value="ม.2">ม.2</option>
                                    <option value="ม.3">ม.3</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>เลขบัตรประชาชน</label>
                                <input type="text" class="form-control" name="citizen_id" required>
                            </div>
                            <div class="form-group">
                                <label>รหัสประจำตัวนักเรียน</label>
                                <input type="text" class="form-control" name="student_id" required>
                            </div>
                            <div class="form-group">
                                <label>คำนำหน้า</label>
                                <select class="form-control" name="prefix" required>
                                    <option disabled selected>-- เลือกคำนำหน้า --</option>
                                    <option value="เด็กชาย">เด็กชาย</option>
                                    <option value="เด็กหญิง">เด็กหญิง</option>
                                    <option value="นาย">นาย</option>
                                    <option value="นางสาว">นางสาว</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>ชื่อ-นามสกุล</label>
                                <input type="text" class="form-control" name="student_name" required>
                            </div>

                            <div class="form-group">
                                <label>วันเดือนปีเกิด</label>
                                <input type="date" class="form-control" name="birth_date" required>
                            </div>
                            <div class="form-group" id="scoreSection" style="display: none;">
                                <h5>คะแนนแต่ละรายวิชา</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ชื่อวิชา</th>
                                            <th>คะแนนภาคเรียนที่ 1</th>
                                            <th>คะแนนภาคเรียนที่ 2</th>
                                        </tr>
                                    </thead>
                                    <tbody id="subjectScoreTable">
                                        <!-- เติมจาก JS -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="btn-form">
                                <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                                <button type="reset" class="btn btn-danger">ยกเลิก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saveSuccessModal" tabindex="-1" role="dialog" aria-labelledby="saveSuccessModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>บันทึกสำเร็จ</h5>
                    <button type="button" class="btn btn-success mt-3" data-dismiss="modal"
                        onclick="location.reload()">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelector('input[name="citizen_id"]').addEventListener("blur", function() {
        const citizenId = this.value;
        const academicYear = document.querySelector('input[name="academic_year"]').value;
        const semester = document.querySelector('select[name="semester"]').value; // เลือกภาคเรียนที่ 2
        const classLevel = document.querySelector('select[name="class_level"]').value;

        // ตรวจสอบข้อมูลให้ครบถ้วนก่อนส่ง
        if (!citizenId) {
            alert("กรุณากรอกเลขบัตรประชาชน");
            return; // หยุดการทำงาน
        }
        if (!academicYear) {
            alert("กรุณากรอกปีการศึกษา");
            return;
        }
        if (!classLevel) {
            alert("กรุณาเลือกระดับชั้น");
            return;
        }
        if (!semester) {
            alert("กรุณาเลือกภาคเรียน");
            return;
        }

        fetch("fetch_student_data.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    citizen_id: citizenId,
                    class_level: classLevel,
                    academic_year: academicYear,
                    semester: 1 // ดึงข้อมูลจากภาคเรียนที่ 1
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // แสดงข้อมูลนักเรียนในฟอร์ม
                    document.querySelector('input[name="student_id"]').value = data.student.student_id;
                    document.querySelector('select[name="prefix"]').value = data.student.prefix;
                    document.querySelector('input[name="student_name"]').value = data.student.student_name;
                    document.querySelector('input[name="birth_date"]').value = data.student.birth_date;

                    // เติมคะแนนภาคเรียนที่ 1 ในตาราง
                    const sem1Inputs = document.querySelectorAll('input[name="semester1_scores[]"]');
                    const subjectIds = document.querySelectorAll('input[name="subject_ids[]"]');
                    for (let i = 0; i < subjectIds.length; i++) {
                        const sid = subjectIds[i].value;
                        if (data.scores[sid]) {
                            sem1Inputs[i].value = data.scores[sid];
                        }
                    }

                    toggleSemester2Input(); // จัดการฟิลด์คะแนนภาคเรียนที่ 2
                } else {
                    alert(data.message); // แสดงข้อความว่าไม่พบข้อมูล
                }
            })
            .catch(err => {
                console.error("ไม่สามารถโหลดข้อมูลได้:", err);
            });
    });


    function fetchPreviousStudentData() {
        const citizenId = document.querySelector('input[name="citizen_id"]').value;
        const academicYear = document.querySelector('input[name="academic_year"]').value;
        const semester = document.querySelector('select[name="semester"]').value;
        const classLevel = document.querySelector('select[name="class_level"]').value;

        if (semester === "2" && citizenId && classLevel && academicYear) {
            fetch("fetch_student_data.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        citizen_id: citizenId,
                        class_level: classLevel,
                        academic_year: academicYear
                    })
                })
                .then(res => res.json())
                .then(data => {
                    console.log("data : ", data);
                    if (data.success) {
                        console.log("ข้อมูลที่ได้จาก PHP:", data);

                        // เติมข้อมูลนักเรียน
                        document.querySelector('input[name="student_id"]').value = data.student.student_id;
                        document.querySelector('select[name="prefix"]').value = data.student.prefix;
                        document.querySelector('input[name="student_name"]').value = data.student.student_name;
                        document.querySelector('input[name="birth_date"]').value = data.student.birth_date;

                        // เติมคะแนน
                        const sem1Inputs = document.querySelectorAll('input[name="semester1_scores[]"]');
                        const subjectIds = document.querySelectorAll('input[name="subject_ids[]"]');
                        for (let i = 0; i < subjectIds.length; i++) {
                            const sid = subjectIds[i].value;
                            if (data.scores[sid]) {
                                sem1Inputs[i].value = data.scores[sid];
                            }
                        }

                        toggleSemester2Input();
                    }
                })
                .catch(err => console.error("❌ โหลดข้อมูลล้มเหลว", err));
        }
    }

    function toggleSemester2Input() {
        const sem1Inputs = document.querySelectorAll('input[name="semester1_scores[]"]');
        const sem2Inputs = document.querySelectorAll('input[name="semester2_scores[]"]');

        for (let i = 0; i < sem1Inputs.length; i++) {
            if (!sem1Inputs[i].value) {
                sem2Inputs[i].disabled = true;
            } else {
                sem2Inputs[i].disabled = false;
            }
        }
    }

    function loadSubjectsForClass() {
        const selectedClass = document.getElementById("classLevelSelect").value;
        document.getElementById("scoreSection").style.display = "block";

        fetch("load_subjects.php")
            .then(res => res.json())
            .then(data => {
                const tableBody = document.getElementById("subjectScoreTable");
                tableBody.innerHTML = "";

                const level = selectedClass.startsWith("ป.") ? "ชั้นประถมศึกษา" : "ชั้นมัธยมศึกษา";
                const filteredSubjects = data.filter(sub => sub.class_level === level);

                if (filteredSubjects.length === 0) {
                    tableBody.innerHTML = `
                    <tr><td colspan="3" class="text-center text-muted">ยังไม่มีรายวิชาในระดับชั้นนี้</td></tr>
                `;
                    return;
                }

                filteredSubjects.forEach(subject => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                    <td>
                        <input type="hidden" name="subject_ids[]" value="${subject.id}">
                        ${subject.subject_name}
                    </td>
                    <td>
                        <input type="number" name="semester1_scores[]" class="form-control" min="0" max="100">
                    </td>
                    <td>
                        <input type="number" name="semester2_scores[]" class="form-control" min="0" max="100">
                    </td>
                `;
                    tableBody.appendChild(row);
                });
                toggleSemester2Input();

                document.querySelector('input[name="citizen_id"]').addEventListener("blur",
                    fetchPreviousStudentData);
            })
            .catch(err => {
                console.error("ไม่สามารถโหลดรายวิชาได้:", err);
            });
    }

    document.getElementById("studentForm").addEventListener("submit", function(e) {
        e.preventDefault(); // ป้องกันการโหลดหน้าใหม่

        const form = e.target;

        const data = {
            academic_year: form.academic_year.value,
            semester: form.semester.value,
            class_level: form.class_level.value,
            citizen_id: form.citizen_id.value,
            student_id: form.student_id.value,
            prefix: form.prefix.value,
            student_name: form.student_name.value,
            birth_date: form.birth_date.value,
            scores: []
        };

        const subjectIds = form.querySelectorAll('input[name="subject_ids[]"]');
        const sem1Scores = form.querySelectorAll('input[name="semester1_scores[]"]');
        const sem2Scores = form.querySelectorAll('input[name="semester2_scores[]"]');

        for (let i = 0; i < subjectIds.length; i++) {
            data.scores.push({
                subject_id: subjectIds[i].value,
                semester1_score: sem1Scores[i].value || null,
                semester2_score: sem2Scores[i].value || null
            });
        }

        fetch("save_student.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    $('#saveSuccessModal').modal('show'); // ✅ แสดง Modal
                    form.reset();
                    document.getElementById("subjectScoreTable").innerHTML = "";
                    document.getElementById("scoreSection").style.display = "none";
                } else {
                    alert("❌ บันทึกข้อมูลไม่สำเร็จ: " + result.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("❌ ข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์");
            });
    });
    </script>

</body>

</html>