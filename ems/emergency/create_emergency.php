<?php
ini_set('display_errors', 0);
error_reporting(0);

header("Content-Type: application/json");
require_once("../../db_connect.php");

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

/* ---------------- RECEIVE PARAMS ---------------- */

$hospital_id = $_POST["hospital_id"] ?? null; // optional
$user_id     = $_POST["user_id"] ?? null;
$user_lat    = $_POST["lat"] ?? null;
$user_lng    = $_POST["lng"] ?? null;

if ($user_id === null || $user_lat === null || $user_lng === null) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameters"
    ]);
    exit;
}

/* ---------------- SANITIZE ---------------- */

$user_id  = (int)$user_id;
$user_lat = (float)$user_lat;
$user_lng = (float)$user_lng;

if ($hospital_id !== null) {
    $hospital_id = (int)$hospital_id;
}

/* ---------------- CHECK ACTIVE SESSION ---------------- */

$checkSession = pg_query_params(
    $conn,
    "SELECT id FROM emergency_sessions 
     WHERE user_id=$1 
     AND status IN ('waiting','assigned')
     ORDER BY created_at DESC
     LIMIT 1",
    [$user_id]
);

if ($checkSession && pg_num_rows($checkSession) > 0) {

    $row = pg_fetch_assoc($checkSession);

    echo json_encode([
        "success" => true,
        "session_id" => (int)$row["id"],
        "message" => "Existing active session"
    ]);

    exit;
}

/* ---------------- AUTO ASSIGN HOSPITAL ---------------- */

if ($hospital_id === null) {

    $nearestQuery = "
    SELECT id,
    (
        6371 * acos(
            cos(radians($1)) *
            cos(radians(lat)) *
            cos(radians(lng) - radians($2)) +
            sin(radians($1)) *
            sin(radians(lat))
        )
    ) AS distance
    FROM hospitals
    ORDER BY distance
    LIMIT 1
    ";

    $nearest = pg_query_params($conn, $nearestQuery, [
        $user_lat,
        $user_lng
    ]);

    if (!$nearest || pg_num_rows($nearest) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "No hospital available"
        ]);
        exit;
    }

    $row = pg_fetch_assoc($nearest);
    $hospital_id = (int)$row["id"];
}

/* ---------------- CREATE SESSION ---------------- */

$query = "
INSERT INTO emergency_sessions (
    hospital_id,
    user_id,
    user_init_lat,
    user_init_lng,
    user_live_lat,
    user_live_lng,
    status,
    created_at
)
VALUES ($1,$2,$3,$4,$3,$4,'waiting',NOW())
RETURNING id
";

$result = pg_query_params($conn, $query, [
    $hospital_id,
    $user_id,
    $user_lat,
    $user_lng
]);

if ($result) {

    $row = pg_fetch_assoc($result);

    echo json_encode([
        "success" => true,
        "session_id" => (int)$row["id"],
        "hospital_id" => $hospital_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
}

pg_close($conn);
?>