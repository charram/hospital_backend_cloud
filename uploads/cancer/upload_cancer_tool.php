<?php

header('Content-Type: application/json');
require_once '../../db_connect.php';

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$hospital_id = $_POST['hospital_id'] ?? '';
$tool_key = $_POST['tool_key'] ?? '';
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? '';
$duration = $_POST['duration'] ?? '';
$related_diseases = $_POST['related_diseases'] ?? '[]';

if (
    empty($hospital_id) ||
    empty($tool_key) ||
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
INSERT INTO cancer_tools (
    hospital_id,
    tool_key,
    title,
    description,
    price,
    duration,
    related_diseases,
    image_path
)
VALUES (
    $1,$2,$3,$4,$5,$6,$7,$8
)
RETURNING id
";

$result =
    pg_query_params(
        $conn,
        $sql,
        [
            $hospital_id,
            $tool_key,
            $title,
            $description,
            $price,
            $duration,
            $related_diseases,
            $image_path
        ]
    );

if ($result) {

    $row =
        pg_fetch_assoc(
            $result
        );

    echo json_encode([
        "success" => true,
        "message" => "Tool uploaded successfully",
        "id" => $row['id']
    ]);
} else {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
}