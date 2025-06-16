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
                    <a class="nav-link" href="record_score.php">บันทึกคะแนน</a>
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
                                <label>ระดับชั้น</label>
                                <select class="form-control" name="class_level" id="classLevelSelect" required>
                                    <option value="" disabled selected>-- เลือกระดับชั้น --</option>
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
                                <label>ห้องเลขที่</label>
                                <input type="text" class="form-control" name="classroom" required>
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

    <!-- Modal for Citizen ID Duplicate -->
    <div class="modal fade" id="citizenIdModal" tabindex="-1" role="dialog" aria-labelledby="citizenIdModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-exclamation-circle fa-3x text-danger mb-3"></i>
                    <h5>เลขบัตรประชาชนซ้ำ</h5>
                    <button type="button" class="btn btn-danger mt-3" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Student ID Duplicate -->
    <div class="modal fade" id="studentIdModal" tabindex="-1" role="dialog" aria-labelledby="studentIdModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-exclamation-circle fa-3x text-danger mb-3"></i>
                    <h5>รหัสประจำตัวนักเรียนซ้ำ</h5>
                    <button type="button" class="btn btn-danger mt-3" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#studentForm').on('submit', function(event) {
            // ตรวจสอบว่าเลือกระดับชั้นหรือไม่
            if ($('#classLevelSelect').val() === null) {
                alert('กรุณาเลือกระดับชั้น');
                return false; // หยุดการส่งฟอร์ม
            }

            // ส่งข้อมูลฟอร์ม
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'save_student.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        $('#saveSuccessModal').modal('show');
                    } else {
                        // เช็คข้อผิดพลาดจาก PHP
                        if (data.message == 'เลขบัตรประชาชนซ้ำในปีการศึกษานี้') {
                            $('#citizenIdModal').modal('show');
                        } else if (data.message ==
                            'รหัสประจำตัวนักเรียนซ้ำในปีการศึกษานี้') {
                            $('#studentIdModal').modal('show');
                        }
                    }
                },
                error: function() {
                    alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
                }
            });
        });
    });
    </script>

</body>

</html>