<?php
header("Content-Type: application/json; charset=utf-8");

$host = "metro.proxy.rlwy.net";
$port = "19442";
$dbname = "railway";
$user = "postgres";
$pass = "vCiFHpFBjjvvEGIqNILhSUeCIyJTKmxq";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    // compatibility
    $conn = $pdo;

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed",
        "error" => $e->getMessage()
    ]);
    exit;
}