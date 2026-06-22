<?php
require_once("../db_connect.php");

header("Content-Type: application/json");

$ems_id = $_GET['ems_id'] ?? null;

if (!$ems_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing ems_id"
    ]);
    exit;
}

pg_query($conn, "BEGIN");

$sql = "
SELECT *
FROM emergency_sessions
WHERE ems_id = $1
AND status IN ('assigned','enroute')
ORDER BY id DESC
LIMIT 1
FOR UPDATE
";

$result = pg_query_params($conn, $sql, [$ems_id]);

if (!$result) {
    pg_query($conn, "ROLLBACK");

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$row = pg_fetch_assoc($result);

if (!$row) {
    pg_query($conn, "ROLLBACK");

    echo json_encode([
        "success" => false,
        "message" => "no job"
    ]);
    exit;
}

if ($row["status"] == "assigned") {

    $update = pg_query_params(
        $conn,
        "
        UPDATE emergency_sessions
        SET status = 'enroute',
            updated_at = NOW()
        WHERE id = $1
        ",
        [$row["id"]]
    );

    if (!$update) {
        pg_query($conn, "ROLLBACK");

        echo json_encode([
            "success" => false,
            "message" => "update failed"
        ]);
        exit;
    }

    $row["status"] = "enroute";
}

pg_query($conn, "COMMIT");

foreach ($row as $k => $v) {
    $row[$k] = ($v === null) ? "" : strval($v);
}

echo json_encode([
    "success" => true,
    "data" => $row
]);

pg_close($conn);