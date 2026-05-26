<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$email    = strtolower(trim($data["email"] ?? ""));
$password = trim($data["password"] ?? "");

if (!$email || !$password) {
  echo json_encode([
    "success" => false,
    "message" => "missing fields"
  ]);
  exit;
}

$res = pg_query_params($conn, "
  SELECT email, password
  FROM superadmins
  WHERE email = $1
  LIMIT 1
", [$email]);

if (!$res || pg_num_rows($res) === 0) {
  echo json_encode([
    "success" => false,
    "message" => "email not found"
  ]);
  exit;
}

$row = pg_fetch_assoc($res);

if (!password_verify($password, $row["password"])) {
  echo json_encode([
    "success" => false,
    "message" => "wrong password"
  ]);
  exit;
}

echo json_encode([
  "success" => true,
  "user" => [
    "email" => $row["email"],
    "role"  => "superadmin"
  ]
], JSON_UNESCAPED_UNICODE);
