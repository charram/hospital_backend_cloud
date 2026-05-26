<?php
header("Content-Type: application/json; charset=utf-8");
require_once "db_connect.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if ($email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "missing fields"
    ]);
    exit;
}

$sql = "
SELECT id, name, email, password, role
FROM users
WHERE email = $1
LIMIT 1
";

$res = pg_query_params($conn, $sql, [$email]);

if (pg_num_rows($res) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "user not found"
    ]);
    exit;
}

$user = pg_fetch_assoc($res);

// ✅ ตรวจ password hash
if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "invalid password"
    ]);
    exit;
}

// ================================
// 🔐 สร้าง Auth Token
// ================================
$token = bin2hex(random_bytes(32));

pg_query_params(
    $conn,
    "UPDATE users SET api_token=$1 WHERE id=$2",
    [$token, $user["id"]]
);

// ไม่ส่ง password กลับ
unset($user["password"]);

echo json_encode([
    "success" => true,
    "user" => $user,
    "token" => $token
]);
