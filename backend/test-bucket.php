<?php
require 'utils/s3.php';
try {
    $command = $s3->getCommand('GetObject', array(
        'Bucket' => $bucketName,
        'Key'    => 'uploads/file.jpg',
    ));

    // Create a temporary URL valid for 20 minutes
    $presignedUrl = $s3->createPresignedUrl($command, '+20 minutes');

    echo "Temporary URL: " . $presignedUrl;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}