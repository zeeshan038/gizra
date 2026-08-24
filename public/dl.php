<?php
$allowed = ['gizraweb_30june_admin.zip'];
$file = $_GET['f'] ?? '';
if (!in_array($file, $allowed, true)) { http_response_code(404); exit; }
$path = __DIR__ . '/' . $file;
if (!file_exists($path)) { http_response_code(404); exit; }
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-cache');
readfile($path);
