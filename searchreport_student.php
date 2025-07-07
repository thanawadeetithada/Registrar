<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

$academicYears = [];
$classLevels = [];

$resultYear = $conn->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC");
while ($row = $resultYear->fetch_assoc()) {
    $academicYears[] = $row['academic_year'];
}

$resultLevel = $conn->query("SELECT DISTINCT class_level FROM students ORDER BY class_level ASC");
while ($row = $resultLevel->fetch_assoc()) {
    $classLevels[] = $row['class_level'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาข้อมูลนักเรียน</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
        margin: 0;
    }

    .card {
        padding: 1rem;
        border: none;
    }

    .over-card {
        display: flex;
        justify-content: center;
    }

    .center-container {
        min-height: calc(100vh - 70px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .table thead th,
    .table-bordered td,
    .table-bordered th {
        border: 1px solid #1b1e21;
    }

    .clickable-row {
        cursor: pointer;
    }

    hr {
        margin-top: 2rem;
        margin-bottom: 2rem;
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
                    <a class="nav-link" href="index.php">บันทึกข้อมูลนักเรียน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="searchreport_student.php">ค้นหาข้อมูลนักเรียน<span
                            class="sr-only">(current)</span></a>
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

    <div class="center-container">
        <div class="card" style="background: #cfd8e5; width: 90%;">
            <h2 class="text-center">ค้นหาข้อมูลนักเรียน</h2>
            <br>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="searchInput"
                        placeholder="กรุณาใส่ชื่อ-นามสกุล หรือ รหัสนักเรียน">
                    <div class="input-group-append">
                        <button class="btn btn-primary" id="searchBtn">ค้นหา</button>
                    </div>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary" id="searchAllBtn">แสดงนักเรียนทั้งหมด</button>
                </div>
                <hr>
                <div class="row mb-3 mt-3 justify-content-center">
                    <div class="col-md-3">
                        <select class="form-control" id="filterAcademicYear">
                            <option value="">-- เลือกปีการศึกษา --</option>
                            <?php foreach ($academicYears as $year): ?>
                            <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterClassLevel">
                            <option value="">-- เลือกระดับชั้น --</option>
                            <option value="ชั้นประถมศึกษา">ชั้นประถมศึกษา</option>
                            <option value="ชั้นมัธยมศึกษา">ชั้นมัธยมศึกษา</option>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-3 text-center">
                        <button class="btn btn-primary" id="filterSearchBtn">
                            </i> ค้นหา
                        </button>
                    </div>
                </div>
            </div>
            <div id="searchResult" class="mt-4"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    // ทำให้แถวในตารางสามารถคลิกเพื่อไปยังหน้า report_student.php ได้
    $(document).on('click', '.clickable-row', function() {
        const studentId = $(this).data('id');
        if (studentId) {
            window.location.href = 'report_student.php?id=' + encodeURIComponent(studentId);
        }
    });

    $(document).ready(function() {
        $('#searchBtn').click(function() {
            var searchValue = $('#searchInput').val().trim();

            if (searchValue === '') {
                $('#searchResult').html(
                    '<div class="alert alert-danger text-center">กรุณากรอกข้อมูลสำหรับค้นหา</div>');
                return;
            }
            $('#filterAcademicYear').val('');
            $('#filterClassLevel').val('');
            $.ajax({
                url: 'search_student.php', // เปลี่ยนไปใช้ search_student.php
                method: 'POST',
                data: {
                    search: searchValue
                },
                success: function(response) {
                    $('#searchResult').html(response);
                },
                error: function() {
                    $('#searchResult').html(
                        '<div class="alert alert-danger text-center">เกิดข้อผิดพลาดในการเชื่อมต่อ</div>'
                    );
                }
            });
        });

        $('#searchInput').keypress(function(event) {
            if (event.key === "Enter") {
                $('#searchBtn').click();
            }
        });
    });

    $('#searchAllBtn').click(function() {
        $('#searchInput').val('');
        $('#filterAcademicYear').val('');
        $('#filterClassLevel').val('');
        $.ajax({
            url: 'get_all_students.php',
            method: 'GET',
            success: function(response) {
                $('#searchResult').html(response);
            },
            error: function() {
                $('#searchResult').html(
                    '<div class="alert alert-danger text-center">เกิดข้อผิดพลาดในการโหลดข้อมูลนักเรียนทั้งหมด</div>'
                );
            }
        });
    });

    $('#filterSearchBtn').click(function() {
        $('#searchInput').val('');
        const year = $('#filterAcademicYear').val();
        const group = $('#filterClassLevel').val();

        $.ajax({
            url: 'get_all_students.php',
            method: 'GET',
            data: {
                academic_year: year,
                class_group: group
            },
            success: function(response) {
                $('#searchResult').html(response);
            },
            error: function() {
                $('#searchResult').html(
                    '<div class="alert alert-danger text-center">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>'
                );
            }
        });
    });
    </script>
</body>

</html>