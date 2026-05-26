<?php
header("Content-Type: application/json");
require_once "../db_connect.php";

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$name = $_POST["name"] ?? "";
$name = trim($name);

if ($name === "") {
    echo json_encode([
        "success" => false,
        "message" => "Name required"
    ]);
    exit;
}

try {

   $result = pg_query_params(
    $conn,
    "INSERT INTO hospitals (name) VALUES ($1) RETURNING id",
    [$name]
);


    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Insert failed"
        ]);
        exit;
    }
$row = pg_fetch_assoc($result);

echo json_encode([
    "success" => true,
    "id" => $row["id"]
]);


} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
