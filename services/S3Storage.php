<?php
class S3Storage
{
    private $accessKey;
    private $secretKey;
    private $region;
    private $bucket;
    private $endpoint;
    private $baseUrl;
    private $acl;

    public function __construct()
    {
        $this->accessKey = getenv("AWS_ACCESS_KEY_ID") ?: "";
        $this->secretKey = getenv("AWS_SECRET_ACCESS_KEY") ?: "";
        $this->region = getenv("AWS_REGION") ?: "";
        $this->bucket = getenv("AWS_S3_BUCKET") ?: "";
        $this->endpoint = rtrim((string)(getenv("AWS_S3_ENDPOINT") ?: ""), "/");
        $this->baseUrl = rtrim((string)(getenv("AWS_S3_BASE_URL") ?: ""), "/");
        $this->acl = getenv("AWS_S3_ACL") ?: "";
    }

    public function isConfigured()
    {
        return $this->accessKey !== "" && $this->secretKey !== "" && $this->region !== "" && $this->bucket !== "";
    }

    public function uploadImage($localPath, $contentType, $keyPrefix, $extension)
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $safeExt = strtolower(preg_replace("/[^a-z0-9]/i", "", $extension));
        $safeExt = $safeExt !== "" ? $safeExt : "jpg";
        $key = rtrim($keyPrefix, "/") . "/" . uniqid("img_", true) . "." . $safeExt;

        $ok = $this->putObject($localPath, $contentType, $key);
        if (!$ok) {
            return null;
        }

        return $this->buildPublicUrl($key);
    }

    private function buildPublicUrl($key)
    {
        if ($this->baseUrl !== "") {
            return $this->baseUrl . "/" . ltrim($key, "/");
        }

        $host = $this->bucket . ".s3." . $this->region . ".amazonaws.com";
        if ($this->endpoint !== "") {
            $host = $this->resolveEndpointHost();
            $base = $this->endpointUsesBucketInHost()
                ? $this->endpoint
                : $this->endpoint . "/" . $this->bucket;
            return $base . "/" . ltrim($key, "/");
        }

        return "https://" . $host . "/" . ltrim($key, "/");
    }

    private function putObject($localPath, $contentType, $key)
    {
        $payload = file_get_contents($localPath);
        if ($payload === false) {
            return false;
        }

        $method = "PUT";
        $service = "s3";
        $amzDate = gmdate("Ymd\\THis\\Z");
        $dateStamp = gmdate("Ymd");
        $payloadHash = hash("sha256", $payload);

        $endpoint = $this->endpoint;
        $host = $this->bucket . ".s3." . $this->region . ".amazonaws.com";
        $canonicalUri = "/" . ltrim($key, "/");
        $url = "https://" . $host . $canonicalUri;

        if ($endpoint !== "") {
            $host = $this->resolveEndpointHost();
            if ($this->endpointUsesBucketInHost()) {
                $url = $endpoint . $canonicalUri;
            } else {
                $canonicalUri = "/" . $this->bucket . "/" . ltrim($key, "/");
                $url = $endpoint . $canonicalUri;
            }
        }

        $headers = [
            "host" => $host,
            "x-amz-content-sha256" => $payloadHash,
            "x-amz-date" => $amzDate,
        ];

        if ($this->acl !== "") {
            $headers["x-amz-acl"] = $this->acl;
        }

        ksort($headers);

        $canonicalHeaders = "";
        foreach ($headers as $k => $v) {
            $canonicalHeaders .= $k . ":" . $v . "\n";
        }

        $signedHeaders = implode(";", array_keys($headers));

        $canonicalRequest = $method . "\n" .
            $canonicalUri . "\n" .
            "\n" .
            $canonicalHeaders . "\n" .
            $signedHeaders . "\n" .
            $payloadHash;

        $algorithm = "AWS4-HMAC-SHA256";
        $credentialScope = $dateStamp . "/" . $this->region . "/" . $service . "/aws4_request";
        $stringToSign = $algorithm . "\n" .
            $amzDate . "\n" .
            $credentialScope . "\n" .
            hash("sha256", $canonicalRequest);

        $signingKey = $this->getSignatureKey($this->secretKey, $dateStamp, $this->region, $service);
        $signature = hash_hmac("sha256", $stringToSign, $signingKey);

        $authorizationHeader = $algorithm . " Credential=" . $this->accessKey . "/" . $credentialScope . ", SignedHeaders=" . $signedHeaders . ", Signature=" . $signature;

        $curlHeaders = [
            "Authorization: " . $authorizationHeader,
            "x-amz-date: " . $amzDate,
            "x-amz-content-sha256: " . $payloadHash,
            "Content-Type: " . $contentType,
            "Host: " . $host,
        ];
        if ($this->acl !== "") {
            $curlHeaders[] = "x-amz-acl: " . $this->acl;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return false;
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }

    private function endpointUsesBucketInHost()
    {
        if ($this->endpoint === "") {
            return false;
        }
        return strpos($this->endpoint, $this->bucket . ".") !== false;
    }

    private function resolveEndpointHost()
    {
        $parts = parse_url($this->endpoint);
        if (!empty($parts["host"])) {
            return $parts["host"];
        }
        return preg_replace("#^https?://#i", "", $this->endpoint);
    }

    private function getSignatureKey($key, $dateStamp, $regionName, $serviceName)
    {
        $kDate = hash_hmac("sha256", $dateStamp, "AWS4" . $key, true);
        $kRegion = hash_hmac("sha256", $regionName, $kDate, true);
        $kService = hash_hmac("sha256", $serviceName, $kRegion, true);
        return hash_hmac("sha256", "aws4_request", $kService, true);
    }
}
