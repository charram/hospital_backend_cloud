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

    $supabaseUrl =
        getenv("SUPABASE_URL");

    $supabaseKey =
        getenv("SUPABASE_SECRET");

    $tmp =
        $_FILES["image"]["tmp_name"];

    $original =
        $_FILES["image"]["name"];

    $ext = strtolower(
        pathinfo(
            $original,
            PATHINFO_EXTENSION
        )
    );

    $allowed_ext = [
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];

    if (!in_array($ext, $allowed_ext)) {

        echo json_encode([
            "success" => false,
            "message" => "invalid image type"
        ]);

        exit;
    }

    $file_name =
        uniqid() .
        "." .
        $ext;

    $fileData =
        file_get_contents($tmp);

    $bucket =
        "hospital-images";

    $uploadUrl =
        $supabaseUrl .
        "/storage/v1/object/" .
        $bucket .
        "/" .
        $file_name;

    $ch = curl_init($uploadUrl);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $fileData,
        CURLOPT_HTTPHEADER => [
            "apikey: " . $supabaseKey,
            "Authorization: Bearer " . $supabaseKey,
            "Content-Type: image/" . $ext,
            "x-upsert: true"
        ]
    ]);

    $response =
        curl_exec($ch);

    $httpCode =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

    curl_close($ch);

    if (
        $httpCode < 200 ||
        $httpCode >= 300
    ) {

        echo json_encode([
            "success" => false,
            "message" => "supabase upload failed",
            "response" => $response
        ]);

        exit;
    }

    $image_path =
        $supabaseUrl .
        "/storage/v1/object/public/" .
        $bucket .
        "/" .
        $file_name;
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