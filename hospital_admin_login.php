<?php
header(
    "Content-Type: application/json; charset=utf-8"
);

require_once
    "db_connect.php";

/*
INPUT
{
  "email": "...",
  "password": "..."
}
*/

$data =
    json_decode(
        file_get_contents(
            "php://input"
        ),
        true
    );

$email =
    strtolower(
        trim(
            $data[
                "email"
            ] ?? ""
        )
    );

$password =
    trim(
        $data[
            "password"
        ] ?? ""
    );

// ============================
// VALIDATE
// ============================
if (
    $email === "" ||
    $password === ""
) {

    echo json_encode([
        "success" =>
            false,
        "message" =>
            "missing fields"
    ]);

    exit;
}

// ============================
// QUERY
// hospital_admin + hospitals
// ============================
$sql =
"
SELECT
    ha.id AS admin_id,
    ha.email,
    ha.password,
    ha.hospital_id,

    h.name
        AS hospital_name,

    h.status
        AS hospital_status

FROM
    hospital_admins ha

JOIN
    hospitals h
ON
    h.id =
    ha.hospital_id

WHERE
    ha.email = $1

LIMIT 1
";

$res =
    pg_query_params(
        $conn,
        $sql,
        [$email]
    );

if (
    !$res ||
    pg_num_rows(
        $res
    ) === 0
) {

    echo json_encode([
        "success" =>
            false,
        "message" =>
            "email not found"
    ]);

    exit;
}

$row =
    pg_fetch_assoc(
        $res
    );

// ============================
// CHECK PASSWORD
// ============================
if (
    !password_verify(
        $password,
        $row[
            "password"
        ]
    )
) {

    echo json_encode([
        "success" =>
            false,
        "message" =>
            "wrong password"
    ]);

    exit;
}

// ============================
// CHECK HOSPITAL STATUS
// ============================
$status =
    strtolower(
        trim(
            $row[
                "hospital_status"
            ] ?? ""
        )
    );

if (
    $status !==
    "approved"
) {

    echo json_encode([
        "success" =>
            false,
        "message" =>
            "Hospital not approved yet",
        "status" =>
            $status
    ]);

    exit;
}

// ============================
// SUCCESS
// ============================
echo json_encode([
    "success" =>
        true,

    "user" => [

        "id" =>
            (int)
            $row[
                "admin_id"
            ],

        "hospital_id" =>
            (int)
            $row[
                "hospital_id"
            ],

        "email" =>
            $row[
                "email"
            ],

        "role" =>
            "hospital_admin",

        "hospital_name" =>
            $row[
                "hospital_name"
            ]
    ]
]);