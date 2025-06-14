<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>เกรดแต่ละรายวิชา</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
    }

    .card {
        padding-top: 30px;
        padding: 1rem;
        border-radius: 10px;
    }

    h3 {
        text-align: center;
    }

    .table td,
    .table th {
        text-align: center;
        vertical-align: middle;
    }

    .bg-infos {
        background-color: #004085 !important;
    }

    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style=" background-color: #004085 !important;padding-left: 2rem;">
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
                    <a class="nav-link" href="student.php">ข้อมูลนักเรียน</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="subject.php">เกรดแต่ละรายวิชา <span class="sr-only">(current)</span></a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="card" style="border: 0px;">
        <h3>เกรดแต่ละรายวิชา</h3>
        <div class="card-body">
            <div class="button-group mb-2 text-right" style="display: flex;justify-content: flex-end;">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        ระดับชั้น
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#" onclick="filterSubjects('ทั้งหมด')">ทั้งหมด</a>
                        <a class="dropdown-item" href="#" onclick="filterSubjects('ชั้นประถมศึกษา')">ชั้นประถมศึกษา</a>
                        <a class="dropdown-item" href="#" onclick="filterSubjects('ชั้นมัธยมศึกษา')">ชั้นมัธยมศึกษา</a>
                    </div>
                </div>

                <button class="btn btn-success ml-3" data-toggle="modal" data-target="#addSubjectModal"
                    onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> เพิ่มรายวิชา
                </button>
            </div>

            <div class="card" style="background: #cfd8e5;">
                <div class="card-body" style="padding-bottom: 0px;">
                    <div id="subjectCardContainer" class="row"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" role="dialog" aria-labelledby="addSubjectLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">เพิ่มรายวิชาและช่วงเกรด</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- ช่องกรอกชื่อวิชา -->
                    <div class="form-group">
                        <label>ชื่อวิชา</label>
                        <input type="text" id="subjectName" class="form-control" placeholder="เช่น คณิตศาสตร์"
                            autocomplete="off" required />
                    </div>

                    <!-- เลือกระดับชั้น -->
                    <div class="form-group">
                        <label>ระดับชั้น</label>
                        <select id="classLevel" class="form-control" required>
                            <option disabled selected>-- เลือกระดับชั้น --</option>
                            <option>ชั้นประถมศึกษา</option>
                            <option>ชั้นมัธยมศึกษา</option>
                        </select>
                    </div>

                    <!-- ตารางช่วงคะแนน -->
                    <table class="table table-bordered" id="gradeRangeTable">
                        <thead class="thead-light">
                            <tr>
                                <th>คะแนนต่ำสุด</th>
                                <th>คะแนนสูงสุด</th>
                                <th>เกรด</th>
                                <th>ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- เพิ่มแถวช่วงคะแนนที่นี่ -->
                        </tbody>
                    </table>
                    <button class="btn btn-sm btn-info" onclick="addGradeRangeRow()">+ เพิ่มช่วงคะแนน</button>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button class="btn btn-primary" id="saveSubjectBtn" onclick="saveSubject()">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Confirm Delete -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">ยืนยันการลบรายวิชา</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    คุณแน่ใจหรือไม่ว่าต้องการลบวิชานี้?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Success -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>ลบสำเร็จ</h5>
                    <button type="button" class="btn btn-success mt-3" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Save Success -->
    <div class="modal fade" id="saveSuccessModal" tabindex="-1" role="dialog" aria-labelledby="saveSuccessModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>บันทึกสำเร็จ</h5>
                    <button type="button" class="btn btn-success mt-3" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let allSubjects = [];

    function filterSubjects(level) {
        const container = document.getElementById("subjectCardContainer");
        container.innerHTML = "";

        const filtered = level === "ทั้งหมด" ? allSubjects : allSubjects.filter(s => s.class_level === level);

        if (filtered.length === 0) {
            container.innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <p class="font-weight-bold text-dark">ไม่มีข้อมูล</p>
            </div>
        `;
            return; // ✅ หยุดตรงนี้ถ้าไม่มีข้อมูล
        }

        filtered.forEach(subject => {
            if (!subject.grades || !Array.isArray(subject.grades)) {
                subject.grades = [];
            }

            const card = document.createElement("div");
            card.className = "col-md-12 mb-4";

            card.innerHTML = `
            <div class="card shadow-sm" style="padding: 20px;">
                <div class="card-header bg-infos text-white d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${subject.subject_name}</strong> | ${subject.class_level}
                    </div>
                </div>
                <div class="card-body" style="padding-left: 0px;padding-right: 0px;padding-top: 0px;padding-bottom: 10px;">
                    <table class="table table-bordered table-sm text-center mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>คะแนนต่ำสุด</th>
                                <th>คะแนนสูงสุด</th>
                                <th>เกรด</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${subject.grades.length > 0 ? subject.grades.map(g => `
                                <tr>
                                    <td>${g.min_score}</td>
                                    <td>${g.max_score}</td>
                                    <td>${g.grade}</td>
                                </tr>`).join("") : `<tr><td colspan="3">ยังไม่มีข้อมูลช่วงเกรด</td></tr>`}
                        </tbody>
                    </table>
                </div>
                <div style="text-align: end;">
                    <button class="btn btn-sm btn-warning mr-1" onclick="editSubject('${subject.id}')">
                        <i class="fa fa-edit"></i> แก้ไข
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteSubject(${subject.id})">
                        <i class="fa fa-trash"></i> ลบ
                    </button>
                </div>
            </div>`;

            container.appendChild(card);
        });
    }

    function openAddModal() {
        document.getElementById("subjectName").value = "";
        document.getElementById("classLevel").selectedIndex = 0;
        document.querySelector("#gradeRangeTable tbody").innerHTML = "";
        document.getElementById("saveSubjectBtn").removeAttribute("data-id"); // ล้าง id (ป้องกัน update)
        addGradeRangeRow();
    }

    function addGradeRangeRow() {
        const tbody = document.querySelector("#gradeRangeTable tbody");
        const row = document.createElement("tr");
        row.innerHTML = `
      <td><input type="number" class="form-control" min="0"></td>
      <td><input type="number" class="form-control" min="0"></td>
      <td><input type="number" class="form-control" step="0.5" min="0" max="4"></td>
      <td><button class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
    `;
        tbody.appendChild(row);
    }

    function saveSubject() {
        const subjectId = document.getElementById("saveSubjectBtn").getAttribute("data-id");
        const subjectName = document.getElementById("subjectName").value.trim();
        const classLevel = document.getElementById("classLevel").value;

        if (!subjectName || !classLevel) {
            alert("กรุณากรอกชื่อวิชาและเลือกระดับชั้น");
            return;
        }

        const rows = document.querySelectorAll("#gradeRangeTable tbody tr");
        if (rows.length === 0) {
            alert("กรุณาเพิ่มช่วงคะแนนอย่างน้อย 1 แถว");
            return;
        }

        const gradeRanges = Array.from(rows).map(row => {
            return {
                min: parseFloat(row.cells[0].querySelector('input').value),
                max: parseFloat(row.cells[1].querySelector('input').value),
                grade: parseFloat(row.cells[2].querySelector('input').value)
            };
        });

        fetch("save_subject.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id: subjectId || null,
                    subject: subjectName,
                    classLevel: classLevel,
                    grades: gradeRanges
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $('#addSubjectModal').modal('hide');
                    document.getElementById("subjectName").value = "";
                    document.getElementById("classLevel").selectedIndex = 0;
                    document.querySelector("#gradeRangeTable tbody").innerHTML = "";
                    document.getElementById("saveSubjectBtn").removeAttribute("data-id"); // ✅ เคลียร์ค่า id

                    loadSubjects(); // โหลดใหม่
                    $('#saveSuccessModal').modal('show');
                } else {
                    alert("เกิดข้อผิดพลาดในการบันทึก");
                }
            })
            .catch(err => {
                console.error(err);
                alert("ข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์");
            });
    }

    function loadSubjects() {
        fetch("load_subjects.php")
            .then(res => res.json())
            .then(data => {
                allSubjects = data; // บันทึกไว้ใช้กรอง
                filterSubjects("ทั้งหมด"); // แสดงทุกวิชา
            });
    }
    // เรียกใช้ทันทีเมื่อโหลดหน้าเว็บ
    window.onload = loadSubjects;

    function editSubject(subjectId) {
        console.log('subjectId', subjectId)
        fetch(`get_subject.php?id=${subjectId}`)
            .then(res => res.json())
            .then(subject => {
                console.log('subject', subject)
                document.getElementById("subjectName").value = subject.subject_name;
                document.getElementById("classLevel").value = subject.class_level;

                const tbody = document.querySelector("#gradeRangeTable tbody");
                tbody.innerHTML = "";

                // เช็คก่อนว่า subject.grades มีค่าและเป็น Array หรือไม่
                if (subject.grades && Array.isArray(subject.grades)) {
                    subject.grades.forEach(g => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                        <td><input type="number" class="form-control" value="${g.min_score}" min="0"></td>
                        <td><input type="number" class="form-control" value="${g.max_score}" min="0"></td>
                        <td><input type="number" class="form-control" step="0.5" min="0" max="4" value="${g.grade}"></td>
                        <td><button class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">
                            <i class="fa fa-trash"></i></button></td>
                    `;
                        tbody.appendChild(row);
                    });
                } else {
                    // ถ้าไม่มีช่วงเกรด ให้แสดงว่าไม่มีข้อมูล (จะใส่แถวว่างหรือแจ้งเตือนก็ได้)
                    console.log("ไม่มีข้อมูลช่วงเกรด");
                }

                // เปิด modal และเก็บ id
                $('#addSubjectModal').modal('show');
                document.getElementById("saveSubjectBtn").setAttribute("data-id", subjectId);
            });
    }

    let subjectIdToDelete = null;

    function deleteSubject(subjectId) {
        subjectIdToDelete = subjectId;
        $('#confirmDeleteModal').modal('show');
    }

    document.getElementById("confirmDeleteBtn").addEventListener("click", () => {
        if (!subjectIdToDelete) return;

        fetch("delete_subject.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id: subjectIdToDelete
                })
            })
            .then(async res => {
                if (!res.ok) {
                    const errorText = await res.text();
                    console.error("Server error:", errorText);
                    throw new Error("Server responded with error");
                }
                return res.json();
            })
            .then(data => {
                $('#confirmDeleteModal').modal('hide'); // ซ่อน modal ยืนยันลบ

                if (data.success) {
                    $('#successModal').modal('show'); // ✅ แสดง modal ลบสำเร็จ
                    loadSubjects(); // โหลดใหม่
                } else {
                    alert("ไม่สามารถลบได้");
                }
            })
            .catch(err => {
                console.error(err);
                alert("ข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์");
                $('#confirmDeleteModal').modal('hide');
            });
    });
    </script>
</body>

</html>