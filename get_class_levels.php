<?php
header('Content-Type: application/json');
require 'db.php';

try {
    $stmt = $pdo->query("SELECT DISTINCT class_level FROM subjects WHERE class_level IS NOT NULL ORDER BY class_level ASC");
    $levels = $stmt->fetchAll(PDO::FETCH_COLUMN); // ดึงเฉพาะค่าคอลัมน์เดียว

    echo json_encode($levels);
} catch (PDOException $e) {
    echo json_encode([]);
}
