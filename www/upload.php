<?php

require_once 'includes/config.php';

if (!isset($_GET['hash'])) {
    throw new Exception('Upload hash required');
}
if (!isset($_GET['name'])) {
    throw new Exception('Upload name is required');
}
$file = realpath(HOA\Settings::get('uploads_path') . '/' . substr($_GET['hash'], 0, 2) . '/' . $_GET['hash']);
if (!file_exists($file)) {
    throw new Exception('Upload not found');
}
header('Content-Type: ' . mime_content_type($file));
header('Content-Disposition: attachment; filename="' . $_GET['name'] . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
