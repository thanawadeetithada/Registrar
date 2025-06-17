<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล

$academic_year_query = $pdo->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC");
$academic_years = $academic_year_query->fetchAll(PDO::FETCH_ASSOC);

$subjects_query = $pdo->query("SELECT * FROM subjects");
$subjects = $subjects_query->fetchAll(PDO::FETCH_ASSOC);

$students_query = $pdo->query("
    SELECT * FROM students 
    ORDER BY academic_year DESC, class_level, classroom, student_id
");
$students = $students_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกคะแนนนักเรียน</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
    .card {
        margin-bottom: 20px;
    }

    .card-header {
        background-color: #004085;
        color: white;
        font-size: 18px;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    .table td,
    .table th {
        text-align: center;
    }

    .table td.subject-name,
    .table th.subject-name {
        text-align: left;
    }

    .form-group {
        margin-bottom: 10px;
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
                    <a class="nav-link active" href="record_score.php">บันทึกคะแนน<span
                            class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="subject.php">เกรดแต่ละรายวิชา</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center">บันทึกคะแนนนักเรียน</h2>
<br>
        <?php 
        $previous_subject_name = ''; // เก็บชื่อวิชาที่แสดงไปแล้ว
        $previous_class_level = ''; // เก็บระดับชั้นที่แสดงไปแล้ว
        $previous_classroom = ''; // เก็บห้องที่แสดงไปแล้ว
        $previous_academic_year = ''; // เก็บปีการศึกษาที่แสดงไปแล้ว
        
        foreach ($academic_years as $academic_year): ?>
        <!-- แสดงปีการศึกษา -->
        <div class="card">
            <div class="card-header">
                ปีการศึกษา <?php echo $academic_year['academic_year']; ?>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>วิชา</th>
                            <th>ระดับชั้น</th>
                            <th>ห้อง</th>
                            <th>กรอกคะแนน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($subjects as $subject):
                            $students_query = $pdo->prepare("
                                SELECT * FROM students WHERE class_level = :class_level AND academic_year = :academic_year
                            ");
                            $students_query->execute([
                                'class_level' => $subject['class_level'],
                                'academic_year' => $academic_year['academic_year']
                            ]);
                            $students = $students_query->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($students as $student):
                                $is_subject_duplicate = ($previous_subject_name === $subject['subject_name'] && 
                                                        $previous_class_level === $subject['class_level'] && 
                                                        $previous_academic_year === $academic_year['academic_year']);
                                
                                if ($previous_academic_year !== $academic_year['academic_year'] || 
                                    $previous_class_level !== $subject['class_level'] || 
                                    $previous_classroom !== $student['classroom']):
                        ?>
                        <tr>
                            <td class="subject-name">
                                <?php echo $is_subject_duplicate ? '' : $subject['subject_name']; ?></td>
                            <td><?php echo $subject['class_level']; ?></td>
                            <td><?php echo $student['classroom']; ?></td>
                            <td>
                                <a href="classroom.php?academic_year=<?php echo $academic_year['academic_year']; ?>&subject_name=<?php echo urlencode($subject['subject_name']); ?>&subject_id=<?php echo $subject['id']; ?>&class_level=<?php echo $subject['class_level']; ?>&classroom=<?php echo $student['classroom']; ?>"
                                    class="btn btn-primary"
                                    onclick="console.log('subject_id:', <?php echo $subject['id']; ?>)">กรอกคะแนน</a>

                            </td>
                        </tr>
                        <?php
                            endif;
                                $previous_subject_name = $subject['subject_name'];      
                                $previous_class_level = $subject['class_level'];
                                $previous_classroom = $student['classroom'];
                                $previous_academic_year = $academic_year['academic_year'];
                            endforeach;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>

    </script>
</body>

</html>