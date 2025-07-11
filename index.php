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
        border: none;
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
        <a class="navbar-brand" href="searchreport_student.php">บันทึกข้อมูลนักเรียน</a>
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
                    <a class="nav-link" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน</a>
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

    <div class="card">
        <h2 class="text-center">บันทึกข้อมูลนักเรียน</h2>
        <div class="card-body">
            <div class="button-group">
                <div class="import-button">
                    <input type="file" id="uploadExcel" accept=".xlsx, .xls" class="d-none">
                    <button id="uploadButton" class="btn btn-success">นำเข้าข้อมูลนักเรียน</button>

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
                                    <option value="ชั้นประถมศึกษา">ชั้นประถมศึกษา</option>
                                    <option value="ชั้นมัธยมศึกษา">ชั้นมัธยมศึกษา</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>ห้องเลขที่ <span>(ตัวอย่าง 1/3)</span></label>
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
                                    <option value="สามเณร">สามเณร</option>
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

                            <div class="form-group" id="subjectSwitches" style="display: none;">
                                <label>เลือกวิชาที่เรียน</label>
                                <div id="subjectSwitchContainer"></div>
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

    <!-- Modal for Success จากการ import excel -->
    <div class="modal fade" id="importSuccessModal" tabindex="-1" role="dialog"
        aria-labelledby="importSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>นำเข้าข้อมูลนักเรียนสำเร็จ</h5>
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
                    <!-- ชื่อ-นามสกุลของนักเรียนที่ซ้ำจะมาแสดงที่นี่ -->
                    <div id="studentName" style="text-align: left; padding-left: 20px;"></div>
                    <button type="button" class="btn btn-danger mt-3" data-dismiss="modal" onclick="location.reload()">ปิด</button>
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
                    <!-- แสดงชื่อ-นามสกุลของนักเรียนที่ซ้ำ -->
                    <div id="studentName" style="text-align: left; padding-left: 20px;"></div>
                    <button type="button" class="btn btn-danger mt-3" data-dismiss="modal" onclick="location.reload()">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invalidSubjectModal" tabindex="-1" role="dialog"
        aria-labelledby="invalidSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>ไม่พบรหัสวิชานี้</h5>
                    <div id="invalidSubjectList" style="text-align: center; padding-left: 20px;"></div>
                    <button type="button" class="btn btn-warning mt-3" data-dismiss="modal" onclick="location.reload()">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $('#classLevelSelect').on('change', function() {
        const level = $(this).val();
        if (!level) return;

        // เคลียร์และซ่อน
        $('#subjectSwitchContainer').empty();
        $('#subjectSwitches').hide();

        // เรียกวิชาตามระดับชั้น
        $.ajax({
            url: 'get_subjects_by_level.php',
            method: 'GET',
            data: {
                class_level: level
            },
            success: function(subjects) {
                if (subjects.length === 0) {
                    $('#subjectSwitchContainer').html(
                        '<p class="text-muted">ไม่มีวิชาสำหรับระดับชั้นนี้</p>');
                    return;
                }

                subjects.forEach(subject => {
                    const switchHTML = `
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="subject_${subject.id}" name="subjects[]" value="${subject.id}">
                    <label class="custom-control-label" for="subject_${subject.id}">${subject.subject_name} (${subject.subject_id})</label>
                </div>`;
                    $('#subjectSwitchContainer').append(switchHTML);
                });

                $('#subjectSwitches').show();
            },
            error: function() {
                $('#subjectSwitchContainer').html(
                    '<p class="text-danger">ไม่สามารถโหลดรายวิชาได้</p>');
            }
        });
    });

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
                dataType: 'json', // ✅ เพิ่มบรรทัดนี้
                success: function(data) {
                    if (data.success) {
                        $('#saveSuccessModal').modal('show');
                    } else {
                        if (data.message === 'เลขบัตรประชาชนซ้ำในปีการศึกษานี้') {
                            $('#citizenIdModal').modal('show');
                        } else if (data.message ===
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

    $('#uploadButton').on('click', function() {
        $('#uploadExcel').trigger('click'); // เปิดหน้าต่างเลือกไฟล์
    });

    $('#uploadExcel').on('change', function() {
        var fileInput = $('#uploadExcel')[0];
        if (fileInput.files.length === 0) {
            alert('กรุณาเลือกไฟล์ Excel');
            return;
        }

        var formData = new FormData();
        formData.append('file', fileInput.files[0]);

        // ส่งไฟล์ไปยัง PHP เพื่อประมวลผล
        $.ajax({
            url: 'import_excel_data_student.php', // ไฟล์ PHP ที่จะประมวลผลไฟล์ Excel
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                var data = response;
                if (data.success) {
                    $('#importSuccessModal').modal('show'); // ✅ modal สำหรับ import
                } else {
                    // ตรวจสอบ citizen_id ซ้ำ
                    if (data.duplicate_citizens.length > 0) {
                        let names = '';
                        data.duplicate_citizens.forEach(function(item) {
                            names +=
                                '<p style="padding-left: 20px;margin-bottom: 5px;">ชื่อ : ' +
                                item.student_name + '</p>';
                        });
                        $('#citizenIdModal').find('#studentName').html(names);
                        $('#citizenIdModal').modal('show');
                        return;
                    }

                    // ตรวจสอบ student_id ซ้ำ
                    if (data.duplicate_students.length > 0) {
                        let names = '';
                        data.duplicate_students.forEach(function(item) {
                            names +=
                                '<p style="padding-left: 20px;margin-bottom: 5px;">ชื่อ : ' +
                                item.student_name + '</p>';
                        });
                        $('#studentIdModal').find('#studentName').html(names);
                        $('#studentIdModal').modal('show');
                    }

                    // ✅ ✅ ✅ ตรวจสอบ subject_id ที่ไม่ถูกต้อง
                    if (data.invalid_subject_ids && data.invalid_subject_ids.length > 0) {
                        let html = '';
                        data.invalid_subject_ids.forEach(function(id) {
                            html += '<p style="margin-bottom: 5px;">รหัสวิชา: ' + id +
                                '</p>';
                        });
                        $('#invalidSubjectList').html(html);
                        $('#invalidSubjectModal').modal('show');
                    }
                }
            },

            error: function() {
                alert('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้');
            }
        });
    });
    </script>

</body>

</html>