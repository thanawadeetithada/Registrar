<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลนักเรียน</title>

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
                <li class="nav-item active">
                    <a class="nav-link" href="student.php">ข้อมูลนักเรียน <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="subject.php">เกรดแต่ละรายวิชา</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="card">
        <h3>ข้อมูลนักเรียน</h3>
        <div class="card-body">
            <div class="card" style="background: #cfd8e5;">
                <div class="card-body">
                  
                </div>
            </div>
        </div>
    </div>
    <!-- เพิ่ม jQuery ก่อน Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>