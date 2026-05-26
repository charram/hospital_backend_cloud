<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header("Content-Type: application/json; charset=utf-8");

require_once "db_connect.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email = strtolower(
    trim($data["email"] ?? "")
);

$name = trim(
    $data["name"] ?? ""
);

if (empty($email)) {
    echo json_encode([
        "success" => false,
        "message" => "Email required"
    ]);
    exit;
}

// หา user
$res = pg_query_params(
    $conn,
    "
    SELECT *
    FROM users
    WHERE email = $1
    LIMIT 1
    ",
    [$email]
);

// ไม่มี user → create auto
if (
    !$res ||
    pg_num_rows($res) == 0
) {

    $insert =
        pg_query_params(
            $conn,
            "
          INSERT INTO users
(
    name,
    email,
    password
)
VALUES
(
    $1,
    $2,
    ''
)
RETURNING id
            ",
            [
                $name,
                $email
            ]
        );

    if (!$insert) {
        echo json_encode([
            "success" => false,
            "message" =>
                pg_last_error(
                    $conn
                )
        ]);
        exit;
    }

    $user =
        pg_fetch_assoc(
            $insert
        );

} else {

    $user =
        pg_fetch_assoc(
            $res
        );
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" =>
            (int)
            $user["id"],
        "name" =>
            $name,
        "email" =>
            $email,
        "role" =>
            "user"
    ]
]);