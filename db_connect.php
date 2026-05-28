<?php

header("Content-Type: application/json; charset=utf-8");

$host   = "metro.proxy.rlwy.net";
$port   = "19442";
$dbname = "railway";
$user   = "postgres";
$pass = "vCiFHpFBjjvvEGIqNILhSUeCIyJTKmxq";
$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$pass sslmode=require"
);

if (!$conn) {

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);

    exit;
}