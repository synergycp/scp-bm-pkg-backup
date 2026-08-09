<?php

namespace Packages\Backup\App\Destinations\R2;

use Illuminate\Support\Facades\Http;
use Packages\Backup\App\Archive;

/**
 * Cloudflare R2 Handler.
 *
 * Stores backups in a Cloudflare R2 bucket over R2's S3-compatible API,
 * authenticated with AWS Signature Version 4 (implemented here directly, so
 * no AWS SDK is required). Requires an R2 API token with Object Read & Write
 * permission on the configured bucket.
 */
class R2Handler implements Archive\Dest\Handler\Handler {
  /**
   * R2's region placeholder for SigV4.
   *
   * @var string
   */
  const REGION = 'auto';

  /**
   * @var string
   */
  const SERVICE = 's3';

  /**
   * SHA-256 of an empty payload, used for requests without a body.
   *
   * @var string
   */
  const EMPTY_PAYLOAD_SHA256 =
    'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

  /**
   * The S3 single-request PUT limit (5 GB).
   *
   * @var int
   */
  const MAX_UPLOAD_BYTES = 5000000000;

  /**
   * @var int
   */
  const CONNECT_TIMEOUT = 30;

  /**
   * @var int
   */
  const API_TIMEOUT = 120;

  /**
   * @var int
   */
  const UPLOAD_TIMEOUT = 3600;

  /**
   * @var Archive\Field\ValueService
   */
  protected $value;

  /**
   * @param Archive\Field\ValueService $value
   */
  public function __construct(Archive\Field\ValueService $value) {
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function copy(Archive\Dest\Dest $dest, $tempFile, $destFile) {
    $values = $this->values($dest);

    $size = is_file($tempFile) ? filesize($tempFile) : false;
    if ($size === false) {
      throw new \Exception(
        sprintf('Backup temp file %s is missing.', $tempFile)
      );
    }
    if ($size > self::MAX_UPLOAD_BYTES) {
      throw new \Exception(
        'Backup is larger than the 5 GB Cloudflare R2 single-file upload limit.'
      );
    }

    $url = $this->url($values, $this->fileName($values, $destFile));
    $payloadHash = hash_file('sha256', $tempFile);

    $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
      ->timeout(self::UPLOAD_TIMEOUT)
      ->withHeaders($this->signedHeaders('PUT', $url, $payloadHash, $values))
      ->send('PUT', $url, [
        'body' => fopen($tempFile, 'r'),
      ]);

    if ($response->failed()) {
      throw new \Exception(
        sprintf('Cloudflare R2 upload failed: %s', $this->error($response))
      );
    }
  }

  /**
   * @inheritDoc
   */
  public function delete(Archive\Dest\Dest $dest, $destFile) {
    $values = $this->values($dest);
    $url = $this->url($values, $this->fileName($values, $destFile));

    // S3-compatible DELETE succeeds even when the object no longer exists,
    // which is exactly the idempotence we want here.
    $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
      ->timeout(self::API_TIMEOUT)
      ->withHeaders(
        $this->signedHeaders('DELETE', $url, self::EMPTY_PAYLOAD_SHA256, $values)
      )
      ->send('DELETE', $url, []);

    if ($response->failed()) {
      throw new \Exception(
        sprintf('Cloudflare R2 delete failed: %s', $this->error($response))
      );
    }
  }

  /**
   * Load and validate the destination's field values.
   *
   * @param Archive\Dest\Dest $dest
   *
   * @return object
   *
   * @throws \Exception
   */
  protected function values(Archive\Dest\Dest $dest) {
    $values = $this->value->all($dest);

    $required = [
      R2Fields::ACCOUNT_ID,
      R2Fields::ACCESS_KEY_ID,
      R2Fields::SECRET_ACCESS_KEY,
      R2Fields::BUCKET,
    ];

    foreach ($required as $name) {
      if (trim((string) $values->value($name)) === '') {
        throw new \Exception(
          sprintf('The Cloudflare R2 destination is missing its "%s" value.', $name)
        );
      }
    }

    return $values;
  }

  /**
   * The full object key inside the bucket, including the optional folder.
   *
   * @param object $values
   * @param string $destFile
   *
   * @return string
   */
  protected function fileName($values, $destFile) {
    $folder = trim((string) $values->value(R2Fields::FOLDER), "/ \t");

    return $folder === '' ? $destFile : $folder . '/' . $destFile;
  }

  /**
   * The (already percent-encoded) URL of an object.
   *
   * @param object $values
   * @param string $fileName
   *
   * @return string
   */
  protected function url($values, $fileName) {
    $path = $values->value(R2Fields::BUCKET) . '/' . $fileName;
    $encoded = implode(
      '/',
      array_map('rawurlencode', explode('/', $path))
    );

    return sprintf(
      'https://%s.r2.cloudflarestorage.com/%s',
      trim((string) $values->value(R2Fields::ACCOUNT_ID)),
      $encoded
    );
  }

  /**
   * Build the AWS Signature Version 4 headers for a request.
   *
   * @param string $method
   * @param string $url
   * @param string $payloadHash sha256 of the request body
   * @param object $values
   *
   * @return array
   */
  protected function signedHeaders($method, $url, $payloadHash, $values) {
    $host = parse_url($url, PHP_URL_HOST);
    $path = parse_url($url, PHP_URL_PATH) ?: '/';

    $amzDate = $this->timestamp();
    $date = substr($amzDate, 0, 8);
    $scope = sprintf(
      '%s/%s/%s/aws4_request',
      $date,
      self::REGION,
      self::SERVICE
    );

    $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
    $canonicalRequest = implode("\n", [
      $method,
      $path,
      '', // no query string
      "host:$host\nx-amz-content-sha256:$payloadHash\nx-amz-date:$amzDate\n",
      $signedHeaders,
      $payloadHash,
    ]);

    $stringToSign = implode("\n", [
      'AWS4-HMAC-SHA256',
      $amzDate,
      $scope,
      hash('sha256', $canonicalRequest),
    ]);

    $secret = (string) $values->value(R2Fields::SECRET_ACCESS_KEY);
    $signingKey = hash_hmac(
      'sha256',
      'aws4_request',
      hash_hmac(
        'sha256',
        self::SERVICE,
        hash_hmac(
          'sha256',
          self::REGION,
          hash_hmac('sha256', $date, 'AWS4' . $secret, true),
          true
        ),
        true
      ),
      true
    );
    $signature = hash_hmac('sha256', $stringToSign, $signingKey);

    return [
      'Authorization' => sprintf(
        'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
        $values->value(R2Fields::ACCESS_KEY_ID),
        $scope,
        $signedHeaders,
        $signature
      ),
      'x-amz-date' => $amzDate,
      'x-amz-content-sha256' => $payloadHash,
    ];
  }

  /**
   * The current UTC time in SigV4 format. Overridable for tests.
   *
   * @return string
   */
  protected function timestamp() {
    return gmdate('Ymd\THis\Z');
  }

  /**
   * A readable error from a failed S3-style (XML) response.
   *
   * @param mixed $response
   *
   * @return string
   */
  protected function error($response) {
    $body = $response->body();
    $message = preg_match('#<Message>(.*?)</Message>#s', $body, $matches)
      ? $matches[1]
      : substr($body, 0, 200);

    return sprintf('HTTP %d: %s', $response->status(), $message);
  }
}
