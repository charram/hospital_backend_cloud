<?php

header('Content-Type: application/json');

include '../db_connect.php';

if (!$conn) {

    echo json_encode([
        "success" => false,
        "message" => "DB connection failed"
    ]);

    exit;
}

$hospital_id =
$_GET['hospital_id']
?? '';

if (empty($hospital_id)) {

    echo json_encode([
        "success" => false,
        "message" => "hospital_id missing"
    ]);

    exit;
}

$sql = "
SELECT *

FROM
cancer_specialty_details

WHERE
hospital_id = $1

ORDER BY
id DESC
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

    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);