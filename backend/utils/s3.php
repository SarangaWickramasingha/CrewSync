<?php

require_once __DIR__ . "/../config/Env.php";

class S3Storage {
    private static $s3Client = null;
    private static $bucketName = "crewsync";
    private static $bucketPrefix = "uploads";
    private static $r2PublicUrl = "";
    private static bool $initialized = false;

    private static function init(): void {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        Env::load();
        self::$bucketPrefix = trim(Env::get("BUCKET_PREFIX", "uploads"), "/");
        self::$r2PublicUrl  = rtrim(Env::get("R2_PUBLIC_URL", ""), "/");
        self::$bucketName   = Env::get("R2_BUCKET_NAME", "crewsync");

        $autoloadPath = __DIR__ . "/../vendor/autoload.php";
        if (!file_exists($autoloadPath)) {
            return;
        }

        require_once $autoloadPath;

        $r2AccountId = Env::get("R2_ACCOUNT_ID", "");
        $r2KeyId     = Env::get("R2_ACCESS_KEY_ID", "");
        $r2SecretKey = Env::get("R2_SECRET_ACCESS_KEY", "");

        if ($r2KeyId !== "" && $r2SecretKey !== "" && class_exists("\Aws\S3\S3Client")) {
            self::$s3Client = new \Aws\S3\S3Client([
                "version"                          => "latest",
                "region"                           => "auto",
                "endpoint"                         => "https://{$r2AccountId}.r2.cloudflarestorage.com",
                "use_path_style_endpoint"          => true,
                "suppress_php_deprecation_warning" => true,
                "credentials"                      => [
                    "key"    => $r2KeyId,
                    "secret" => $r2SecretKey,
                ],
            ]);
        }
    }

    public static function isConfigured(): bool {
        self::init();
        return self::$s3Client !== null;
    }

    public static function getObjectKey(string $relativePath): string {
        self::init();
        $trimmed = ltrim($relativePath, "/");
        return self::$bucketPrefix !== ""
            ? (self::$bucketPrefix . "/" . $trimmed)
            : $trimmed;
    }

    public static function uploadFile(string $relativePath, string $sourceFile, ?string $contentType = null) {
        self::init();
        if (self::$s3Client === null) {
            throw new Exception("Cloudflare R2 storage is not configured or vendor is missing.");
        }

        $params = [
            "Bucket"     => self::$bucketName,
            "Key"        => self::getObjectKey($relativePath),
            "SourceFile" => $sourceFile,
        ];
        if ($contentType !== null) {
            $params["ContentType"] = $contentType;
        }

        return self::$s3Client->putObject($params);
    }

    public static function deleteFile(string $relativePath): void {
        self::init();
        if (self::$s3Client === null) {
            return;
        }

        self::$s3Client->deleteObject([
            "Bucket" => self::$bucketName,
            "Key"    => self::getObjectKey($relativePath),
        ]);
    }

    public static function getPhotoUrl(string $relativePath): string {
        self::init();
        $key = self::getObjectKey($relativePath);

        if (self::$r2PublicUrl !== "") {
            return self::$r2PublicUrl . "/" . ltrim($key, "/");
        }

        if (self::$s3Client !== null) {
            $command = self::$s3Client->getCommand("GetObject", [
                "Bucket" => self::$bucketName,
                "Key"    => $key,
            ]);
            $request = \Aws\serialize($command);
            $request = $request->withHeader("x-amz-content-sha256", "UNSIGNED-PAYLOAD");
            $uri = $request->getUri();
            parse_str($uri->getQuery(), $query);
            unset($query["x-amz-content-sha256"]);
            $request = $request->withUri($uri->withQuery(http_build_query($query)));
            $signer  = new \Aws\Signature\SignatureV4("s3", "auto");
            $expires = (int) Env::get("BUCKET_URL_EXPIRY_SECONDS", 3600);
            return (string) $signer->presign($request, self::$s3Client->getCredentials()->wait(), "+ {$expires} seconds")->getUri();
        }

        $scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? "https" : "http";
        $host = $_SERVER["HTTP_HOST"] ?? "localhost:8080";
        return "{$scheme}://{$host}/CrewSync-backend/backend/uploads/" . ltrim($relativePath, "/");
    }
}

// Global helper wrappers for backward compatibility if needed
function r2UploadFile(string $relativePath, string $sourceFile, ?string $contentType = null) {
    return S3Storage::uploadFile($relativePath, $sourceFile, $contentType);
}

function r2DeleteFile(string $relativePath): void {
    S3Storage::deleteFile($relativePath);
}

function r2PhotoUrl(string $relativePath): string {
    return S3Storage::getPhotoUrl($relativePath);
}

