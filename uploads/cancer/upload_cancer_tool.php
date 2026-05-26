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

if (isset($_FILES['image'])) {

    $uploadDir =
        "../../uploads/cancer_tools/";

    if (!file_exists($uploadDir)) {
        mkdir(
            $uploadDir,
            0777,
            true
        );
    }

    $fileName =
        time() .
        "_" .
        rand(1000, 9999) .
        "_" .
        basename(
            $_FILES['image']['name']
        );

    $targetPath =
        $uploadDir . $fileName;

    if (
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $targetPath
        )
    ) {
        $image_path =
            "uploads/cancer_tools/" .
            $fileName;
    }
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