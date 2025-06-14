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
        padding-top: 30px;
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
    <div class="card" style="border: 0px;">
        <h3>เกรดแต่ละรายวิชา</h3>
        <div class="card-body">
            <div class="button-group mb-2 text-right">
                <button class="btn btn-success" data-toggle="modal" data-target="#addSubjectModal">
                    <i class="fa-solid fa-plus"></i> เพิ่มรายวิชา
                </button>
            </div>

            <div class="card" style="background: #cfd8e5;">
                <div class="card-body">
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
                            required />
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
                    <button class="btn btn-primary" onclick="saveSubject()">บันทึก</button>
                </div>
            </div>
        </div>
    </div>
    <!-- SCRIPT -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                    subject: subjectName,
                    classLevel: classLevel,
                    grades: gradeRanges
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("บันทึกสำเร็จ");
                    $('#addSubjectModal').modal('hide');
                    document.getElementById("subjectName").value = "";
                    document.getElementById("classLevel").selectedIndex = 0;
                    document.querySelector("#gradeRangeTable tbody").innerHTML = "";

                    loadSubjects(); // โหลดใหม่
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
                const container = document.getElementById("subjectCardContainer");
                container.innerHTML = ""; // ล้างของเก่าก่อน

                if (data && Array.isArray(data)) {
                    data.forEach(subject => {
                        // ถ้าไม่มี grades หรือไม่ใช่ array ให้ตั้งเป็น array ว่าง
                        if (!subject.grades || !Array.isArray(subject.grades)) {
                            subject.grades = [];
                        }

                        const card = document.createElement("div");
                        card.className = "col-md-12 mb-4";

                        card.innerHTML = `
                        <div class="card shadow-sm">
                            <div class="card-header bg-infos text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${subject.subject_name}</strong> | ${subject.class_level}
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-warning mr-1" onclick="editSubject(${subject.id})">
                                        <i class="fa fa-edit"></i> แก้ไข
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteSubject(${subject.id})">
                                        <i class="fa fa-trash"></i> ลบ
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" style="padding-right: 0px;padding-left: 0px;">
                                <table class="table table-bordered table-sm text-center mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>คะแนนต่ำสุด</th>
                                            <th>คะแนนสูงสุด</th>
                                            <th>เกรด</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${subject.grades.map(g => `
                                            <tr>
                                                <td>${g.min_score}</td>
                                                <td>${g.max_score}</td>
                                                <td>${g.grade}</td>
                                            </tr>
                                        `).join("")}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;

                        container.appendChild(card);
                    });
                } else {
                    console.log("ข้อมูลวิชาไม่ถูกต้อง หรือไม่ใช่ Array");
                }
            });
    }


    // เรียกใช้ทันทีเมื่อโหลดหน้าเว็บ
    window.onload = loadSubjects;

    function editSubject(subjectId) {
        fetch(`get_subject.php?id=${subjectId}`)
            .then(res => res.json())
            .then(subject => {
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
                document.getElementById("saveSubjectBtn").setAttribute("data-id", subjectId); // ปรับตรงนี้ด้วย
            });
    }


    function deleteSubject(subjectId) {
        if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบวิชานี้?")) return;

        fetch("delete_subject.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id: subjectId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("ลบสำเร็จ");
                    loadSubjects();
                } else {
                    alert("ไม่สามารถลบได้");
                }
            })
            .catch(err => {
                console.error(err);
                alert("ข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์");
            });
    }
    </script>
</body>

</html>