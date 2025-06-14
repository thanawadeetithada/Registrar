<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT s.id AS subject_id, s.subject_name, s.class_level, g.min_score, g.max_score, g.grade
        FROM subjects s
        JOIN grade_ranges g ON s.id = g.subject_id
        ORDER BY s.id, g.grade DESC";

$result = $conn->query($sql);

$subjects = [];
$currentId = null;
foreach ($result as $row) {
    if ($currentId !== $row['subject_id']) {
        $currentId = $row['subject_id'];
        $subjects[$currentId] = [
            'id' => $currentId,
            'subject_name' => $row['subject_name'],
            'class_level' => $row['class_level'],
            'grades' => []
        ];
    }
    $subjects[$currentId]['grades'][] = [
        'min_score' => $row['min_score'],
        'max_score' => $row['max_score'],
        'grade' => $row['grade']
    ];
}

echo json_encode(array_values($subjects));
