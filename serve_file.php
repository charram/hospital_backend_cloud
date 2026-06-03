<?php
header("Access-Control-Allow-Origin: *");

$file = $_GET["file"] ?? "";
// 🔥 รองรับ encoded path ใหม่
$file = urldecode($file);

if ($file === "") {
    http_response_code(400);
    exit("Missing file name");
}

// 🔥 ถ้ามี / แปลว่าเป็น path เต็ม
if (str_contains($file, '/')) {

    $path = __DIR__ . "/" . $file;

} else {

    // 🔥 fallback (ระบบเก่า)
    $paths = [
        __DIR__ . "/uploads/diseases_images/" . $file,
        __DIR__ . "/uploads/hospital_media/" . $file,
        __DIR__ . "/uploads/hospital_card/" . $file,
        __DIR__ . "/uploads/beauty/" . $file,
        __DIR__ . "/uploads/product/" . $file,
        __DIR__ . "/uploads/" . $file,
    ];

    $path = null;
    foreach ($paths as $p) {
        if (file_exists($p)) {
            $path = $p;
            break;
        }
    }
}

// ❌ ไม่เจอไฟล์
if (!$path || !file_exists($path)) {
    http_response_code(404);
    exit("File not found");
}

// MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $path);
finfo_close($finfo);

if (ob_get_length()) ob_end_clean();

header("Content-Type: $mime");
header("Content-Length: " . filesize($path));
readfile($path);
exit;