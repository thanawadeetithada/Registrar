<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกข้อมูลนักเรียน</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 3rem;
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
            <div class="card" style="background: #cfd8e5;">
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Email address</label>
                            <input type="email" class="form-control" id="exampleInputEmail1"
                                aria-describedby="emailHelp" placeholder="Enter email">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputPassword1">Password</label>
                            <input type="password" class="form-control" id="exampleInputPassword1"
                                placeholder="Password">
                        </div>
                      
                        <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                        <button class="btn btn-danger">ยกเลิก</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>