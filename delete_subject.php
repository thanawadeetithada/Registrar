<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

$conn->begin_transaction();

// ลบช่วงเกรดก่อน
$conn->query("DELETE FROM grade_ranges WHERE subject_id = $id");

// ลบวิชา
$conn->query("DELETE FROM subjects WHERE id = $id");

$conn->commit();

echo json_encode(["success" => true]);
