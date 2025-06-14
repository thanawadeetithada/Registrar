<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registrar";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode([
        "error" => "เชื่อมต่อฐานข้อมูลไม่สำเร็จ",
        "details" => $e->getMessage()
    ]));
}
