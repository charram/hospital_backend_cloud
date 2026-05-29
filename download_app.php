```php
<?php
$file = __DIR__ . '/app-release.apk';

if (!file_exists($file)) {
    http_response_code(404);
    exit('APK not found');
}

// ล้าง output buffer กัน header พัง
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="OpenHospital-v1.apk"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));

flush();
readfile($file);
exit;
?>
```
