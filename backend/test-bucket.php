<?php
require 'utils/s3.php';
try {
    $presignedUrl = r2PhotoUrl('test-upload.txt');
    echo "Temporary URL: " . $presignedUrl;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}