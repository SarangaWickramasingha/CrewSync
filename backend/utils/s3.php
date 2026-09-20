<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/Env.php';

use Aws\S3\S3Client;

Env::load();

$s3 = new S3Client(array(
    'version'                        => 'latest',
    'region'                         => 'us-east-1',
    'suppress_php_deprecation_warning' => true,
    'credentials'                    => array(
        'key'    => Env::get('BUCKET_ACCESS_KEY_ID'),
        'secret' => Env::get('BUCKET_SECRET_ACCESS_KEY'),
    ),
));

$bucketName  = Env::get('BUCKET_BUCKET_NAME');
$bucketPrefix = trim(Env::get('BUCKET_PREFIX', 'uploads'), '/');

// ── Helper functions ──────────────────────────────────────────────────────────

/**
 * Turn a stored relative path (e.g. review_photos/xxx.jpg) into the S3 object key.
 */
function s3ObjectKey(string $relativePath): string {
    global $bucketPrefix;
    return $bucketPrefix . '/' . ltrim($relativePath, '/');
}

/**
 * Upload a local file to S3 under the given relative path.
 */
function s3UploadPhoto(string $relativePath, string $sourceFile, ?string $contentType = null): array {
    global $s3, $bucketName;

    $params = array(
        'Bucket'     => $bucketName,
        'Key'        => s3ObjectKey($relativePath),
        'SourceFile' => $sourceFile,
    );
    if ($contentType !== null) {
        $params['ContentType'] = $contentType;
    }

    return $s3->putObject($params);
}

/**
 * Generate a presigned GET URL for a stored relative path.
 */
function s3PresignedPhotoUrl(string $relativePath, int $expiresSeconds = 3600): string {
    global $s3, $bucketName;

    $command = $s3->getCommand('GetObject', array(
        'Bucket' => $bucketName,
        'Key'    => s3ObjectKey($relativePath),
    ));

    return $s3->createPresignedUrl($command, $expiresSeconds);
}

/**
 * Delete an S3 object by its stored relative path.
 */
function s3DeletePhoto(string $relativePath): void {
    global $s3, $bucketName;

    $s3->deleteObject(array(
        'Bucket' => $bucketName,
        'Key'    => s3ObjectKey($relativePath),
    ));
}