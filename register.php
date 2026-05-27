<?php
header("Content-Type: application/json; charset=utf-8");
require_once "db_connect_railway.php";

// รับ JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "invalid json"
    ]);
    exit;
}

// รับค่า
$name     = trim($data["name"] ?? "");
$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

// ตรวจสอบค่าว่าง
if ($name === "" || $email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "missing fields"
    ]);
    exit;
}

// เช็ค email ซ้ำ
$check = pg_query_params(
    $conn,
    "SELECT id FROM users WHERE email = $1 LIMIT 1",
    [$email]
);

if (pg_num_rows($check) > 0) {
    echo json_encode([
        "success" => false,
        "message" => "email exists"
    ]);
    exit;
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// สร้าง token
$token = bin2hex(random_bytes(32));

// Insert
$sql = "
INSERT INTO users (name, email, password, role, api_token, created_at)
VALUES ($1, $2, $3, 'user', $4, NOW())
RETURNING id, api_token
";

$res = pg_query_params($conn, $sql, [$name, $email, $hashed, $token]);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => "insert failed"
    ]);
    exit;
}

$row = pg_fetch_assoc($res);

echo json_encode([
    "success" => true,
    "message" => "user registered",
    "user_id" => (int)$row["id"],
    "token" => $row["api_token"]
]);
