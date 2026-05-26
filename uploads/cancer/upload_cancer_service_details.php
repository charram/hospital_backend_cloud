<?php

header('Content-Type: application/json');

include("../../db_connect.php");

if (!$conn) {

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);

    exit;
}

$hospital_id =
    $_POST["hospital_id"] ?? '';

$service_key =
    $_POST["service_key"] ?? '';

$title =
    $_POST["title"] ?? '';

$description =
    $_POST["description"] ?? '';

$price =
    $_POST["price"] ?? '';

if (
    empty($hospital_id) ||
    empty($service_key) ||
    empty($title)
) {

    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);

    exit;
}

$image_path = '';

if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] == 0
) {

    $uploadDir =
        "../../uploads/cancer_services/";

    if (
        !file_exists(
            $uploadDir
        )
    ) {

        mkdir(
            $uploadDir,
            0777,
            true
        );
    }

    $fileName =
        time() .
        "_" .
        rand(1000,9999) .
        "_" .
        basename(
            $_FILES["image"]["name"]
        );

    $targetFile =
        $uploadDir .
        $fileName;

    if (
        move_uploaded_file(
            $_FILES["image"]["tmp_name"],
            $targetFile
        )
    ) {

        $image_path =
            "uploads/cancer_services/" .
            $fileName;
    }
}

$sql = "
INSERT INTO
cancer_service_details
(
    hospital_id,
    service_key,
    title,
    description,
    image_path,
    price
)
VALUES
(
    $1,
    $2,
    $3,
    $4,
    $5,
    $6
)
";

$res =
    pg_query_params(
        $conn,
        $sql,
        [
            $hospital_id,
            $service_key,
            $title,
            $description,
            $image_path,
            $price
        ]
    );

if ($res) {

    echo json_encode([
        "success" => true,
        "message" => "Uploaded successfully"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
}