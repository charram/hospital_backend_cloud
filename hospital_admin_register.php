<?php
error_reporting(E_ALL);
ini_set("display_errors", 0);

header("Content-Type: application/json; charset=utf-8");

require __DIR__ . "/db_connect.php";

$data = json_decode(
    file_get_contents(
        "php://input"
    ),
    true
);

$hospital_name =
    trim(
        $data[
            "hospital_name"
        ] ?? ""
    );

$province =
    trim(
        $data[
            "province"
        ] ?? ""
    );

$license_number =
    trim(
        $data[
            "license_number"
        ] ?? ""
    );

$contact_name =
    trim(
        $data[
            "contact_name"
        ] ?? ""
    );

$email =
    strtolower(
        trim(
            $data[
                "email"
            ] ?? ""
        )
    );

$phone =
    trim(
        $data[
            "phone"
        ] ?? ""
    );

$password =
    trim(
        $data[
            "password"
        ] ?? ""
    );

// =====================
// validate
// =====================
if (
    !$hospital_name ||
    !$province ||
    !$license_number ||
    !$contact_name ||
    !$email ||
    !$password
) {
    echo json_encode([
        "success" => false,
        "message" =>
            "missing fields"
    ]);
    exit;
}

// =====================
// check duplicate email
// =====================
$chk =
    pg_query_params(
        $conn,
        "
        SELECT id
        FROM hospital_admins
        WHERE email = $1
        LIMIT 1
        ",
        [$email]
    );

if (
    !$chk ||
    pg_num_rows($chk) > 0
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "email exists"
    ]);

    exit;
}

// =====================
// password hash
// =====================
$hash =
    password_hash(
        $password,
        PASSWORD_BCRYPT
    );

// =====================
// begin transaction
// =====================
pg_query(
    $conn,
    "BEGIN"
);

// =====================
// create hospital
// =====================
$h =
    pg_query_params(
        $conn,
        "
        INSERT INTO hospitals
        (
            name,
            province,
            license_number,
            status
        )
        VALUES
        (
            $1,
            $2,
            $3,
            'pending'
        )
        RETURNING id
        ",
        [
            $hospital_name,
            $province,
            $license_number
        ]
    );

if (!$h) {

    pg_query(
        $conn,
        "ROLLBACK"
    );

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error(
                $conn
            )
    ]);

    exit;
}

$hospital_id =
    pg_fetch_result(
        $h,
        0,
        0
    );

// =====================
// create admin
// =====================
$a =
    pg_query_params(
        $conn,
        "
        INSERT INTO
        hospital_admins
        (
            hospital_id,
            contact_name,
            email,
            phone,
            password,
            role
        )
        VALUES
        (
            $1,
            $2,
            $3,
            $4,
            $5,
            'hospital_admin'
        )
        ",
        [
            $hospital_id,
            $contact_name,
            $email,
            $phone,
            $hash
        ]
    );

if (!$a) {

    pg_query(
        $conn,
        "ROLLBACK"
    );

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error(
                $conn
            )
    ]);

    exit;
}

// =====================
// commit
// =====================
pg_query(
    $conn,
    "COMMIT"
);

echo json_encode([
    "success" => true,
    "hospital_id" =>
        (int)
        $hospital_id,
    "message" =>
        "registered, waiting approval"
]);