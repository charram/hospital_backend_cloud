<?php

header('Content-Type: application/json');

require_once '../db_connect.php';

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Database connection failed"
    ]);
    exit;
}

$hospital_id =
    $_GET['hospital_id']
    ?? '';

if (
    empty(
        $hospital_id
    )
) {
    echo json_encode([
        "success" => false,
        "message" =>
            "hospital_id required"
    ]);
    exit;
}

$sql = "
SELECT *
FROM doctor_profiles
WHERE hospital_id = $1
ORDER BY id DESC
";

$result =
    pg_query_params(
        $conn,
        $sql,
        [
            $hospital_id
        ]
    );

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error(
                $conn
            )
    ]);
    exit;
}

$data = [];

while (
    $row =
        pg_fetch_assoc(
            $result
        )
) {

    $data[] = [
        "id" =>
            $row["id"],

        "hospital_id" =>
            $row["hospital_id"],

        "doctor_name" =>
            $row["doctor_name"],

        "specialty" =>
            $row["specialty"],

        "experience" =>
            $row["experience"],

        "education" =>
            $row["education"],

        "language" =>
            $row["language"],

        "phone" =>
            $row["phone"],

        "description" =>
            $row["description"],

        "image_path" =>
            $row["image_path"],

        "image_url" =>
            !empty(
                $row["image_path"]
            )
                ? "serve_file.php?file=" .
                    urlencode(
                        $row["image_path"]
                    )
                : "",

        "created_at" =>
            $row["created_at"],
    ];
}

echo json_encode([
    "success" => true,
    "count" =>
        count(
            $data
        ),
    "data" => $data
]);