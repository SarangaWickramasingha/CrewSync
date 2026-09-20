<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/Env.php';

use Aws\S3\S3Client;

Env::load();

$r2AccountId   = Env::get('R2_ACCOUNT_ID', '');
$r2KeyId       = Env::get('R2_ACCESS_KEY_ID', '');
$r2SecretKey   = Env::get('R2_SECRET_ACCESS_KEY', '');

if ($r2KeyId !== '' && $r2SecretKey !== '') {
    $s3 = new S3Client(array(
        'version'                       => 'latest',
        'region'                        => 'auto',
        'endpoint'                      => "https://{$r2AccountId}.r2.cloudflarestorage.com",
        'use_path_style_endpoint'       => true,
        'suppress_php_deprecation_warning' => true,
        'credentials'                   => array(
            'key'    => $r2KeyId,
            'secret' => $r2SecretKey,
        ),
    ));
    $bucketName = Env::get('R2_BUCKET_NAME', 'crewsync');
    $storage    = 'r2';
} else {
    $s3 = new S3Client(array(
        'version'                        => 'latest',
        'region'                         => 'us-east-1',
        'suppress_php_deprecation_warning' => true,
        'credentials'                    => array(
            'key'    => Env::get('BUCKET_ACCESS_KEY_ID'),
            'secret' => Env::get('BUCKET_SECRET_ACCESS_KEY'),
        ),
    ));
    $bucketName = Env::get('BUCKET_BUCKET_NAME');
    $storage    = 'aws';
}

$bucketPrefix = trim(Env::get('BUCKET_PREFIX', 'uploads'), '/');
$r2PublicUrl  = rtrim(Env::get('R2_PUBLIC_URL', ''), '/');

function r2ObjectKey(string $relativePath): string {
    global $bucketPrefix;
    return $bucketPrefix . '/' . ltrim($relativePath, '/');
}

function r2UploadFile(string $relativePath, string $sourceFile, ?string $contentType = null): \Aws\Result {
    global $s3, $bucketName;

    $params = array(
        'Bucket'     => $bucketName,
        'Key'        => r2ObjectKey($relativePath),
        'SourceFile' => $sourceFile,
    );
    if ($contentType !== null) {
        $params['ContentType'] = $contentType;
    }

    return $s3->putObject($params);
}

function r2DeleteFile(string $relativePath): void {
    global $s3, $bucketName;

    $s3->deleteObject(array(
        'Bucket' => $bucketName,
        'Key'    => r2ObjectKey($relativePath),
    ));
}

function r2PhotoUrl(string $relativePath): string {
    global $r2PublicUrl, $s3, $bucketName;

    $key = r2ObjectKey($relativePath);
    if ($r2PublicUrl !== '') {
        return $r2PublicUrl . '/' . ltrim($key, '/');
    }

    $command = $s3->getCommand('GetObject', array(
        'Bucket' => $bucketName,
        'Key'    => $key,
    ));
    $request = \Aws\serialize($command);
    $request = $request->withHeader('x-amz-content-sha256', 'UNSIGNED-PAYLOAD');
    $uri = $request->getUri();
    parse_str($uri->getQuery(), $query);
    unset($query['x-amz-content-sha256']);
    $request = $request->withUri($uri->withQuery(http_build_query($query)));
    $signer  = new \Aws\Signature\SignatureV4('s3', 'auto');
    $expires = (int) Env::get('BUCKET_URL_EXPIRY_SECONDS', 3600);
    $expiry  = '+ ' . $expires . ' seconds';
    return (string) $signer->presign($request, $s3->getCredentials()->wait(), $expiry)->getUri();
}