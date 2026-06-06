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

$hospital_id =
    $_POST['hospital_id'] ?? '';

$symptom_key =
    $_POST['symptom_key'] ?? '';

$title =
    $_POST['title'] ?? '';

$description =
    $_POST['description'] ?? '';

$symptom_score =
    $_POST['symptom_score'] ?? '';

$related_cancer =
    $_POST['related_cancer'] ?? '';

$image_path = '';

$min_price =
    $_POST['min_price'] ?? null;

$max_price =
    $_POST['max_price'] ?? null;

$avg_price =
    $_POST['avg_price'] ?? null;

$insurance_note =
    $_POST['insurance_note'] ?? '';

$is_emergency =
    ($_POST['is_emergency'] ?? 'false') === 'true';

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

    if (
        !in_array(
            $ext,
            $allowed_ext
        )
    ) {

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

    $bucket =
        "hospital-images";

    $uploadUrl =
        $supabaseUrl .
        "/storage/v1/object/" .
        $bucket .
        "/" .
        $file_name;

    $fileData =
        file_get_contents($tmp);

    $ch =
        curl_init($uploadUrl);

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
INSERT INTO cancer_symptoms
(
    hospital_id,
    symptom_key,
    title,
    description,
    symptom_score,
    related_cancer,
    image_path,
    min_price,
    max_price,
    avg_price,
    insurance_note,
    is_emergency
)
VALUES
(
    $1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12
)
";

$result =
    pg_query_params(
        $conn,
        $sql,
       [
    $hospital_id,
    $symptom_key,
    $title,
    $description,
    $symptom_score,
    $related_cancer,
    $image_path,
    $min_price,
    $max_price,
    $avg_price,
    $insurance_note,
    $is_emergency
]
    );

echo json_encode([
    "success" => $result ? true : false,
    "message" => $result
        ? "Symptom uploaded successfully"
        : pg_last_error($conn)
]);