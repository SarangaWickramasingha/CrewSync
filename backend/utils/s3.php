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