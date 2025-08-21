<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $id            = $_POST['id'] ?? null;
  $student_id    = $_POST['student_id'] ?? null;
  $prefix        = $_POST['prefix'] ?? null;
  $student_name  = $_POST['student_name'] ?? null;
  $citizen_id    = $_POST['citizen_id'] ?? null;
  $birth_date    = $_POST['birth_date'] ?? null;
  $academic_year = $_POST['academic_year'] ?? null;

  if (!$id || !$student_id) {
    echo json_encode(['success' => false, 'message' => 'missing id or student_id']);
    exit;
  }

  $sql = "UPDATE students
          SET prefix=?, student_name=?, citizen_id=?, birth_date=?, academic_year=?
          WHERE id=? AND student_id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssis", $prefix, $student_name, $citizen_id, $birth_date, $academic_year, $id, $student_id);

  if ($stmt->execute()) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
  }
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
