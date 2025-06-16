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
                    <a class="nav-link" href="index.php">บันทึกข้อมูลนักเรียน</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="record_score.php">บันทึกคะแนน <span
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
        <h3>บันทึกคะแนนของนักเรียน</h3>
        <div class="card-body">
            <div class="button-group">
                <div class="import-button">
                    <input type="file" id="uploadExcel" accept=".xlsx, .xls" class="d-none">
                    <button id="uploadButton" class="btn btn-success">
                        <i class="fa-regular fa-file-excel"></i><br>
                        นำเข้าคะแนน
                    </button>
                </div>
            </div>
            <br>
            <div class="over-card">
                <div class="card" style="background: #cfd8e5; width: 90%;">
                    <div class="card-body">
                        <form id="studentForm">

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

    </script>

</body>

</html>