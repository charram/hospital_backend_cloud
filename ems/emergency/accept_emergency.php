<?php
header("Content-Type: application/json");
require_once("../../db_connect.php");

$emergency_id = $_POST["emergency_id"] ?? null;
$hospital_id = $_POST["hospital_id"] ?? null;

if (!$emergency_id || !$hospital_id) {
    echo json_encode(["success"=>false,"message"=>"Missing parameters"]);
    exit;
}

$query = "
UPDATE emergency_sessions
SET status = 'assigned',
    hospital_id = $2,
    updated_at = NOW()
WHERE id = $1
AND status = 'waiting'
RETURNING id
";

$result = pg_query_params($conn,$query,[$emergency_id,$hospital_id]);

if ($result && pg_num_rows($result) > 0){
    echo json_encode(["success"=>true]);
}else{
    echo json_encode(["success"=>false,"message"=>"Cannot assign"]);
}