<?php
$file = __DIR__ . '/app-release.apk';

if (!file_exists($file)) {
    die('APK not found');
}

header('Content-Type: application/vnd.android.package-archive');
header('Content-Disposition: attachment; filename="OpenHospital.apk"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-cache');

readfile($file);
exit;
?>