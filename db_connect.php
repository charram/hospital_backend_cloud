<?php
$host   = "localhost";
$port   = "5432";
$dbname = "hospital_db";
$user   = "postgres";
$pass   = "123456";

$conn = pg_connect("
    host=$host
    port=$port
    dbname=$dbname
    user=$user
    password=$pass
");

if (!$conn) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// if (!$conn) {
//   // ❌ ห้าม die
//   // ❌ ห้าม echo HTML
//   // ❌ ห้าม throw
//   return null;
// }
