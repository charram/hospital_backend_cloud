<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "db_connect.php";

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

$description =
trim(
  $_POST["description"]
  ?? ""
);

$category =
trim(
  $_POST["category"]
  ?? ""
);

$show_home =
intval(
  $_POST["show_on_home"]
  ?? 1
);

if (
    $hospital_id === 0 ||
    $title === "" ||
    $category === ""
) {
  echo json_encode([
    "success" => false,
    "message" =>
        "Missing required fields"
  ]);
  exit;
}

if (
    !isset(
      $_FILES["image"]
    )
) {
  echo json_encode([
    "success" => false,
    "message" =>
        "Image not found"
  ]);
  exit;
}

$uploadDir =
__DIR__ .
"/uploads/diseases_images/";

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

$ext =
pathinfo(
  $_FILES["image"]["name"],
  PATHINFO_EXTENSION
);

$filename =
time()
. "_"
. uniqid()
. "." . $ext;

$targetPath =
$uploadDir .
$filename;

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

$imagePath =
"uploads/diseases_images/"
. $filename;

$q =
pg_query_params(
  $conn,
  "
  INSERT INTO
  hospital_diseases
  (
    hospital_id,
    category,
    title,
    description,
    image_path,
    show_on_home
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
  ",
  [
    $hospital_id,
    $category,
    $title,
    $description,
    $imagePath,
    $show_home
  ]
);

if (!$q) {

  echo json_encode([
    "success" => false,
    "message" =>
      pg_last_error(
        $conn
      ),
  ]);

  exit;
}

echo json_encode([
  "success" => true
]);