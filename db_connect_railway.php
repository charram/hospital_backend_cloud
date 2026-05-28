<?php
header("Content-Type: application/json; charset=utf-8");

$host = "metro.proxy.rlwy.net";
$port = "19442";
$dbname = "railway";
$user = "postgres";
$pass = "รหัสจริงของคุณ";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}