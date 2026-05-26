<?php
header("Content-Type: application/json");
require_once("../../db_connect.php");

$session_id = $_POST["emergency_id"] ?? null;
$hospital_id = $_POST["hospital_id"] ?? null;

if (!$session_id || !$hospital_id) {
    echo json_encode(["success"=>false,"message"=>"Missing parameters"]);
    exit;
}

$query = "
UPDATE emergency_sessions
SET status = 'cancelled_by_admin',
    updated_at = NOW()
WHERE id = $1
AND hospital_id = $2
AND status IN ('waiting','assigned')
RETURNING id
";

$result = pg_query_params($conn, $query, [$session_id, $hospital_id]);

if ($result && pg_num_rows($result) > 0) {
    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["success"=>false,"message"=>"Cannot cancel"]);
}

pg_close($conn);