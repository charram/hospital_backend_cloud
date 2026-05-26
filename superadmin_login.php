<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$sql = "SELECT * FROM superadmin WHERE email = $1 LIMIT 1";
$res = pg_query_params($conn, $sql, [$email]);

if (!$res || pg_num_rows($res) == 0) {
    echo json_encode(["success" => false, "message" => "Email not found"]);
    exit;
}

$user = pg_fetch_assoc($res);

if (!password_verify($password, $user["password"])) {
    echo json_encode(["success" => false, "message" => "Wrong password"]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $user["id"],
        "email" => $user["email"],
        "name" => $user["name"],
        "role" => "superadmin"
    ]
]);
