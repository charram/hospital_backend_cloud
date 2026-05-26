<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../db_connect.php';

$id = $_POST["id"] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "Missing ID"]);
    exit;
}

// ดึง user_id
$q1 = "SELECT user_id FROM hospital_verification WHERE id=$1";
$res1 = pg_query_params($conn, $q1, [$id]);

if (!$res1 || pg_num_rows($res1)==0) {
    echo json_encode(["success" => false, "message" => "Record not found"]);
    exit;
}

$user = pg_fetch_assoc($res1);
$user_id = $user["user_id"];

// ลบทั้ง hospital_verification + user
pg_query_params($conn, "DELETE FROM hospital_verification WHERE id=$1", [$id]);
pg_query_params($conn, "DELETE FROM users WHERE id=$1", [$user_id]);

echo json_encode(["success"=>true, "message"=>"Hospital rejected & user removed"]);
