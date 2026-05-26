<?php

header('Content-Type: application/json');

require_once '../../db_connect.php';

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Database connection failed"
    ]);
    exit;
}

$hospital_id =
    $_POST['hospital_id']
    ?? '';

$doctor_name =
    $_POST['doctor_name']
    ?? '';

$specialty =
    $_POST['specialty']
    ?? '';

$experience =
    $_POST['experience']
    ?? '';

$education =
    $_POST['education']
    ?? '';

$language =
    $_POST['language']
    ?? '';

$phone =
    $_POST['phone']
    ?? '';

$description =
    $_POST['description']
    ?? '';

/// optional fields
$sub_specialty =
    $_POST['sub_specialty']
    ?? '';

$line =
    $_POST['line']
    ?? '';

$related_diseases =
    $_POST['related_diseases']
    ?? '[]';

if (
    empty(
        $hospital_id
    ) ||
    empty(
        $doctor_name
    )
) {
    echo json_encode([
        "success" => false,
        "message" =>
            "Missing required fields"
    ]);
    exit;
}

$image_path = "";

if (
    isset(
        $_FILES["image"]
    )
) {

    $uploadDir =
        "../../uploads/doctors/";

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
        rand(
            1000,
            9999
        ) .
        "_" .
        basename(
            $_FILES[
                "image"
            ]["name"]
        );

    $targetPath =
        $uploadDir .
        $fileName;

    if (
        move_uploaded_file(
            $_FILES[
                "image"
            ]["tmp_name"],
            $targetPath
        )
    ) {

        $image_path =
            "uploads/doctors/" .
            $fileName;
    }
}

$sql = "
INSERT INTO doctor_profiles (
    hospital_id,
    doctor_name,
    specialty,
    experience,
    education,
    language,
    phone,
    description,
    image_path
)
VALUES (
    $1,$2,$3,$4,$5,
    $6,$7,$8,$9
)
RETURNING id
";

$result =
    pg_query_params(
        $conn,
        $sql,
        [
            $hospital_id,
            $doctor_name,
            $specialty,
            $experience,
            $education,
            $language,
            $phone,
            $description,
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
        "message" =>
            "Doctor uploaded successfully",
        "id" =>
            $row["id"],
        "image_path" =>
            $image_path
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error(
                $conn
            )
    ]);
}