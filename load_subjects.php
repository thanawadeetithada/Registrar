<?php
require_once 'db.php';

$sql = "SELECT s.id, s.subject_name, s.class_level, 
        gr.min_score, gr.max_score, gr.grade 
        FROM subjects s 
        LEFT JOIN grade_ranges gr ON s.id = gr.subject_id 
        ORDER BY s.id, gr.grade DESC";

$result = $conn->query($sql);

$subjects = [];
$current_id = null;

while ($row = $result->fetch_assoc()) {
    if ($current_id !== $row['id']) {
        $current_id = $row['id'];
        $subjects[$current_id] = [
            'subject_name' => $row['subject_name'],
            'class_level' => $row['class_level'],
            'grades' => []
        ];
    }

    $subjects[$current_id]['grades'][] = [
        'min_score' => $row['min_score'],
        'max_score' => $row['max_score'],
        'grade' => $row['grade']
    ];
}

echo json_encode(array_values($subjects));
