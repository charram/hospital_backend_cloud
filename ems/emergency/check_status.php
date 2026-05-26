<?php
header("Content-Type: application/json");
require_once("../../db_connect.php");

$session_id = $_GET["session_id"] ?? null;

if (!$session_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing session_id"
    ]);
    exit;
}

$session_id = (int)$session_id;

$query = "
SELECT status
FROM emergency_sessions
WHERE id = $1
LIMIT 1
";

$result = pg_query_params($conn, $query, [$session_id]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Session not found"
    ]);
    exit;
}

$row = pg_fetch_assoc($result);

echo json_encode([
    "success" => true,
    "status" => $row["status"]
]);

pg_close($conn);