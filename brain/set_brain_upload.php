<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . "/../db_connect.php";

// -------------------
// DB CHECK
// -------------------

if (!$conn) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Database connection failed"
    ]);

    exit;
}

// -------------------
// RECEIVE
// -------------------

$hospital_id =
intval(
    $_POST["hospital_id"]
    ?? 0
);

$title =
trim(
    $_POST["title"]
    ?? ""
);

$category =
trim(
    $_POST["category"]
    ?? "brain"
);

$description =
$_POST["description"]
?? "";

// 💰 cost
$min_price =
$_POST["min_price"]
?? "";

$max_price =
$_POST["max_price"]
?? "";

$avg_price =
$_POST["avg_price"]
?? "";

$insurance_note =
$_POST["insurance_note"]
?? "";

// meta optional
$meta =
isset($_POST["meta"])
? $_POST["meta"]
: null;

$show_on_home =
intval(
    $_POST["show_on_home"]
    ?? 1
);

$is_hero =
intval(
    $_POST["is_hero"]
    ?? 0
);

$upload_type =
trim(
    $_POST["upload_type"]
    ?? "service"
);

// -------------------
// VALIDATE
// -------------------

if (
    $hospital_id <= 0 ||
    empty($title)
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Missing required fields"
    ]);

    exit;
}

if (
    !isset($_FILES["image"])
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Image not found"
    ]);

    exit;
}

// -------------------
// CATEGORY SAFE
// -------------------

$safeCategory =
preg_replace(
    "/[^a-zA-Z0-9_-]/",
    "",
    strtolower(
        $category
    )
);

// -------------------
// VALIDATE IMAGE
// -------------------

$allowed = [
    "jpg",
    "jpeg",
    "png",
    "webp"
];

$ext =
strtolower(
    pathinfo(
        $_FILES["image"]["name"],
        PATHINFO_EXTENSION
    )
);

if (
    !in_array(
        $ext,
        $allowed
    )
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid image type"
    ]);

    exit;
}

// -------------------
// CREATE FOLDER
// -------------------

$uploadDir =
__DIR__
. "/../uploads/"
. $safeCategory
. "/";

if (
    !is_dir(
        $uploadDir
    )
) {

    mkdir(
        $uploadDir,
        0777,
        true
    );
}

// -------------------
// GENERATE FILE NAME
// -------------------

$fileName =
time()
. "_"
. uniqid()
. "."
. $ext;

$targetPath =
$uploadDir
. $fileName;

// -------------------
// MOVE FILE
// -------------------

if (
    !move_uploaded_file(
        $_FILES["image"]["tmp_name"],
        $targetPath
    )
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Upload image failed"
    ]);

    exit;
}

// -------------------
// IMAGE PATH
// -------------------

$imagePath =
"uploads/"
. $safeCategory
. "/"
. $fileName;

// -------------------
// HERO AUTO RESET
// -------------------

if (
    $is_hero == 1
) {

    pg_query_params(
        $conn,
        "
        UPDATE
            brain_center_uploads
        SET
            is_hero = false
        WHERE
            hospital_id = $1
        AND
            category = $2
        ",
        [
            $hospital_id,
            $category
        ]
    );
}

// -------------------
// CHECK META COLUMN
// -------------------

$hasMeta =
false;

$checkMeta =
pg_query(
    $conn,
    "
    SELECT
        column_name
    FROM
        information_schema.columns
    WHERE
        table_name =
        'brain_center_uploads'
    AND
        column_name =
        'meta'
    "
);

if (
    $checkMeta &&
    pg_num_rows(
        $checkMeta
    ) > 0
) {

    $hasMeta = true;
}

// -------------------
// NORMALIZE META
// -------------------

$metaData = [];

// meta เดิมจาก Flutter
if ($meta != null) {

    $decoded =
    json_decode(
        $meta,
        true
    );

    if (
        json_last_error()
        === JSON_ERROR_NONE
    ) {

        $metaData =
            $decoded;
    }
}

// 💰 merge ราคา
$metaData[
    "min_price"
] =
$min_price;

$metaData[
    "max_price"
] =
$max_price;

$metaData[
    "avg_price"
] =
$avg_price;

$metaData[
    "insurance_note"
] =
$insurance_note;

// กัน field เดิมหาย
if (
    !isset(
        $metaData[
            "upload_type"
        ]
    )
) {

    $metaData[
        "upload_type"
    ] =
    $upload_type;
}

if (
    !isset(
        $metaData[
            "description"
        ]
    )
) {

    $metaData[
        "description"
    ] =
    $description;
}

if (
    !isset(
        $metaData[
            "symptoms"
        ]
    )
) {

    $metaData[
        "symptoms"
    ] = [];
}

if (
    !isset(
        $metaData[
            "machines"
        ]
    )
) {

    $metaData[
        "machines"
    ] = [];
}

// encode กลับ
$meta =
json_encode(
    $metaData,
    JSON_UNESCAPED_UNICODE
);

// -------------------
// INSERT
// -------------------

if ($hasMeta) {

    $q =
    pg_query_params(
        $conn,
        "
        INSERT INTO
        brain_center_uploads
        (
            hospital_id,
            category,
            upload_type,
            title,
            description,
            meta,
            image_path,
            show_on_home,
            is_hero,
            created_at
        )
        VALUES
        (
            $1,
            $2,
            $3,
            $4,
            $5,
            $6,
            $7,
            $8,
            $9,
            NOW()
        )
        ",
        [
            $hospital_id,
            $category,
            $upload_type,
            $title,
            $description,
            $meta,
            $imagePath,
            $show_on_home,
            $is_hero
        ]
    );

} else {

    $q =
    pg_query_params(
        $conn,
        "
        INSERT INTO
        brain_center_uploads
        (
            hospital_id,
            category,
            upload_type,
            title,
            description,
            image_path,
            show_on_home,
            is_hero,
            created_at
        )
        VALUES
        (
            $1,
            $2,
            $3,
            $4,
            $5,
            $6,
            $7,
            $8,
            NOW()
        )
        ",
        [
            $hospital_id,
            $category,
            $upload_type,
            $title,
            $description,
            $imagePath,
            $show_on_home,
            $is_hero
        ]
    );
}

// -------------------
// ERROR
// -------------------

if (!$q) {

    echo json_encode([
        "success" => false,
        "pg_error" =>
            pg_last_error(
                $conn
            )
    ]);

    exit;
}

// -------------------
// SUCCESS
// -------------------

echo json_encode([
    "success" => true,
    "message" =>
        "Upload success",
    "category" =>
        $category,
    "upload_type" =>
        $upload_type,
    "image_path" =>
        $imagePath
]);