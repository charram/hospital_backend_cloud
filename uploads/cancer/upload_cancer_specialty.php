<?php

header('Content-Type: application/json');

include '../../db_connect.php';

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed"
    ]);
    exit;
}

$hospital_id =
$_POST['hospital_id'] ?? '';

$specialty_key =
$_POST['specialty_key'] ?? '';

$title =
$_POST['title'] ?? '';

$description =
$_POST['description'] ?? '';

$price =
$_POST['price'] ?? '';

if (
    empty($hospital_id) ||
    empty($specialty_key) ||
    empty($title)
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

$image_path = "";

if (
    isset($_FILES['image']) &&
    $_FILES['image']['error'] === 0
) {

    $targetDir =
    "../../uploads/cancer_specialty/";

    if (
        !file_exists($targetDir)
    ) {
        mkdir(
            $targetDir,
            0777,
            true
        );
    }

    $ext = pathinfo(
        $_FILES['image']['name'],
        PATHINFO_EXTENSION
    );

    $fileName =
        time() .
        "_" .
        rand(1000, 9999) .
        "." .
        $ext;

    $targetFile =
        $targetDir .
        $fileName;

    if (
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $targetFile
        )
    ) {

        $image_path =
            "uploads/cancer_specialty/" .
            $fileName;

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Image upload failed"
        ]);
        exit;
    }
}

$sql = "
INSERT INTO
cancer_specialty_details (

    hospital_id,
    specialty_key,
    title,
    description,
    image_path,
    price

)

VALUES (

    $1,
    $2,
    $3,
    $4,
    $5,
    $6

)

RETURNING id
";

$result =
pg_query_params(
    $conn,
    $sql,
    [

        $hospital_id,
        $specialty_key,
        $title,
        $description,
        $image_path,
        $price

    ]
);

if ($result) {

    $row =
    pg_fetch_assoc(
        $result
    );

    echo json_encode([
        "success" => true,
        "message" => "Upload success",
        "id" => $row['id']
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error(
            $conn
        )
    ]);
}