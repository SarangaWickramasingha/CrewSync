<?php
require 'utils/s3.php';
try {
    $file = __DIR__ . '/test-upload.txt';

    if (!file_exists($file)) {
        exit("Source file not found: $file");
    }

    $result = $s3->putObject(array(
        'Bucket'      => $bucketName,
        'Key'         => 'uploads/test-upload.txt',
        'SourceFile'  => $file,
    ));

    echo "Uploaded successfully!<br>";
    echo "Object URL: " . $result['ObjectURL'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}