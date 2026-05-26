<?php
require_once("../db_connect.php");
header("Content-Type: application/json");

$user_id = $_POST["user_id"] ?? null;
$fcm_token = $_POST["fcm_token"] ?? null;

if (!$user_id || !$fcm_token) {
  echo json_encode([
    "success" => false,
    "message" => "missing params"
  ]);
  exit;
}

// 🔥 update token
$result = pg_query_params(
  $conn,
  "UPDATE users SET fcm_token = $1 WHERE id = $2",
  [$fcm_token, $user_id]
);

if ($result) {
  echo json_encode([
    "success" => true,
    "message" => "token saved"
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "update failed"
  ]);
}